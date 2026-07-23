<?php

namespace App\Mail;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PengajuanBaruMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Pengajuan $pengajuan)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Relawan Baru: ' . $this->pengajuan->judul,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pengajuan-baru',
            with: [
                'pengajuan' => $this->pengajuan->loadMissing(['user', 'bidang']),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
