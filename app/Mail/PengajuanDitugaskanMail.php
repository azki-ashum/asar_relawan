<?php

namespace App\Mail;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** SOP Bagian 2 -> 3: seluruh kebutuhan sudah terisi relawan, pengaju siap deployment. */
class PengajuanDitugaskanMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Pengajuan $pengajuan)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relawan Siap Ditugaskan: ' . $this->pengajuan->judul,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pengajuan-ditugaskan',
            with: [
                'pengajuan' => $this->pengajuan->loadMissing(['user', 'kebutuhan.relawan']),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
