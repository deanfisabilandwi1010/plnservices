<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    /** Message topics shown on the contact form, with the unit that handles them. */
    public const CATEGORIES = [
        'Informasi Umum' => 'Sekretariat Perusahaan',
        'Kemitraan & Bisnis' => 'Divisi Pengembangan Bisnis',
        'Pengadaan & Vendor' => 'Divisi Pengadaan',
        'Karier & Rekrutmen' => 'Divisi Human Capital',
        'Media & Publikasi' => 'Komunikasi Korporat',
        'Pengaduan Layanan' => 'Unit Layanan Pelanggan',
    ];

    protected $table = 'feedbacks';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    /** Human-friendly ticket number, e.g. PLNS-2026-00012. */
    public function reference(): string
    {
        return sprintf('PLNS-%s-%05d', $this->created_at?->format('Y') ?? now()->format('Y'), $this->id);
    }
}
