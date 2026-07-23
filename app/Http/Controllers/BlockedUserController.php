<?php

namespace App\Http\Controllers;

use App\Models\BlockedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlockedUserController extends Controller
{
    protected function authorizeAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $query = BlockedUser::query()->orderByDesc('created_at');
        if ($search = $request->get('q')) {
            $query->where('email', 'like', "%$search%");
        }
        $blockedUsers = $query->paginate(20)->withQueryString();

        // Ambil map email->name untuk email yang masih ada di table users (batch untuk efisiensi)
        $emailList = $blockedUsers->pluck('email')->unique()->all();
        $userNames = User::whereIn('email', $emailList)->pluck('name', 'email');

        return view('admin.blocked_users.index', compact('blockedUsers', 'userNames'));
    }

    /**
     * AJAX search endpoint for Select2: returns list of users (email based) limited to 50
     */
    public function search(Request $request)
    {
        $this->authorizeAdmin();
        $q = trim($request->get('q', ''));
        $users = User::query()
            ->when($q, function($qq) use ($q) {
                $qq->where(function($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(function($u){
                return [
                    'id' => $u->email,
                    'text' => $u->name . ' (' . $u->email . ')'
                ];
            });
        return response()->json(['results' => $users]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'email' => 'required|email|unique:blocked_users,email',
            'reason' => 'nullable|string|max:255',
        ]);
        $data['blocked_by'] = auth()->id();

        DB::transaction(function () use ($data) {
            $blocked = BlockedUser::create($data);
            // If user already exists, optionally remove active sessions to force logout
            $user = User::where('email', $blocked->email)->first();
            if ($user) {
                // delete sessions referencing this user
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        });

        return redirect()->route('admin.blocked_users.index')->with('success', 'Email berhasil diblokir.');
    }

    public function destroy(BlockedUser $blockedUser)
    {
        $this->authorizeAdmin();
        $blockedUser->delete();
        return redirect()->route('admin.blocked_users.index')->with('success', 'Email berhasil dihapus dari daftar blokir.');
    }
}
