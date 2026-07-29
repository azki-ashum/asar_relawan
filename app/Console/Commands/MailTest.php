<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Cek konfigurasi provider email tanpa harus membuat pengajuan dummy.
 * Sengaja dikirim langsung (bukan lewat queue) supaya error SMTP langsung terlihat.
 */
class MailTest extends Command
{
    protected $signature = 'mail:test {email : Alamat tujuan email uji}';
    protected $description = 'Kirim email uji untuk memverifikasi konfigurasi provider (SMTP)';

    public function handle()
    {
        $to     = $this->argument('email');
        $mailer = config('mail.default');

        $this->line('Mailer   : ' . $mailer);
        if ($mailer === 'smtp') {
            $this->line('Host     : ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
            $this->line('Username : ' . (config('mail.mailers.smtp.username') ?: '(kosong)'));
            $this->line('Password : ' . (config('mail.mailers.smtp.password') ? '(terisi)' : '(KOSONG)'));
        }
        $this->line('From     : ' . config('mail.from.address') . ' <' . config('mail.from.name') . '>');
        $this->line('App URL  : ' . config('app.url'));
        $this->newLine();

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER=log — email hanya ditulis ke storage/logs/laravel.log, tidak benar-benar terkirim.');
        }
        if ($mailer === 'smtp' && !config('mail.mailers.smtp.password')) {
            $this->error('MAIL_PASSWORD kosong. Isi App Password Google Workspace di .env lebih dulu.');
            return self::FAILURE;
        }

        try {
            Mail::raw(
                "Email uji dari " . config('app.name') . ".\n\n"
                    . "Jika email ini sampai, konfigurasi provider sudah benar dan notifikasi pengajuan siap dipakai.\n"
                    . "Dikirim: " . now()->format('d M Y H:i:s'),
                fn($m) => $m->to($to)->subject('[' . config('app.name') . '] Email Uji Konfigurasi')
            );
        } catch (\Throwable $e) {
            $this->error('Gagal mengirim: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Email uji terkirim ke ' . $to . '.');
        if ($mailer === 'log') {
            $this->line('Cek isinya di storage/logs/laravel.log.');
        }

        return self::SUCCESS;
    }
}
