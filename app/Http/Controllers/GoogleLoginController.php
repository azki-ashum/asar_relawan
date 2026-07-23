<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BlockedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleLoginController extends Controller
{

    public function redirectToGoogle()
    {
        $driver = Socialite::driver('google');
        if (app()->environment('local')) {
            $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        }
        return $driver->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            \Log::info('Google callback hit');
            \Log::info('Google callback request', request()->all());
            // Gunakan stateless() sementara untuk debugging redirect/state issues
            $driver = Socialite::driver('google')->stateless();
            if (app()->environment('local')) {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            $googleUser = $driver->user();

            // Socialite\Two\User doesn't implement toArray(), use getters/raw data
            \Log::info('Google User data', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
                'raw' => is_array($googleUser->user) ? $googleUser->user : null,
            ]);

            $email = $googleUser->getEmail();
            $emailDomain = $email ? substr(strrchr($email, "@"), 1) : null;
            \Log::info('Google email domain resolved', ['email' => $email, 'domain' => $emailDomain]);
            if ($emailDomain !== 'asarhumanity.org') {
                \Log::warning('Google login domain rejected', ['domain' => $emailDomain]);
                return redirect()->route('login')->with('error', 'Login hanya diizinkan untuk email @asarhumanity.org.');
            }

            // Check if email is blocked
            if (BlockedUser::where('email', $email)->exists()) {
                \Log::warning('Blocked email attempted login', ['email' => $email]);
                return redirect()->route('login')->with('error', 'Akun Anda diblokir. Silakan hubungi admin.');
            }

            $user = User::where('google_sub', $googleUser->getId())->first();

            if ($user) {
                \Log::info('Existing user found by google_sub', ['user_id' => $user->id]);
                Auth::login($user);
                \Log::info('Auth login successful (existing user)', ['user_id' => $user->id]);
                return redirect()->intended('relawan/dashboard');
            } else {
                $existingUser = User::where('email', $googleUser->getEmail())->first();

                if ($existingUser) {
                    $existingUser->update([
                        'google_sub' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                    Auth::login($existingUser);
                    \Log::info('Auth login successful (linked existing user)', ['user_id' => $existingUser->id]);
                } else {
                    // Buat user baru dengan role 'user'
                    $newUser = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_sub' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                        'role' => 'user', // Definisikan role secara eksplisit
                        'password' => Hash::make(uniqid()),
                    ]);
                    Auth::login($newUser);
                    \Log::info('Auth login successful (new user created)', ['user_id' => $newUser->id]);
                }

                return redirect()->intended('relawan/dashboard');
            }

        } catch (Exception $e) {
            \Log::error('Google login exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect('/login')->with('error', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }
}
