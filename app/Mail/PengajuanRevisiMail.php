<?php

namespace App\Mail;

use App\Models\Pengajuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Admin mengembalikan pengajuan ke pengaju untuk diperbaiki.
 * Dipakai dua tahap: revisi data pengajuan (Bagian 1) & revisi bukti/laporan (Bagian 3).
 */
class PengajuanRevisiMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param 'pengajuan'|'laporan' $tahap */
    public function __construct(public Pengajuan $pengajuan, public string $tahap = 'pengajuan')
    {
    }

    public function envelope(): Envelope
    {
        $prefix = $this->tahap === 'laporan' ? 'Revisi Laporan' : 'Perlu Revisi';

        return new Envelope(
            subject: $prefix . ': ' . $this->pengajuan->judul,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pengajuan-revisi',
            with: [
                'pengajuan' => $this->pengajuan->loadMissing(['user']),
                'tahap'     => $this->tahap,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
