<?php

namespace App\Mail;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** SOP Bagian 3: kegiatan sudah lewat tapi bukti & laporan belum dikirim. */
class PengajuanTerlambatMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Pengajuan $pengajuan)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Belum Masuk: ' . $this->pengajuan->judul,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pengajuan-terlambat',
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
