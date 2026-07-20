<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Artikel, Berita, Buletin, Jurnal, Kliping, Pengumuman, Galeri, Unduhan, Profil};
use App\Models\Settings;

class PdfController extends Controller
{
    protected function getModel(string $jenis)
    {
        return match ($jenis) {
            'artikel' => new Artikel,
            'berita' => new Berita,
            'buletin' => new Buletin,
            'jurnal' => new Jurnal,
            'kliping' => new Kliping,
            'pengumuman' => new Pengumuman,
            'galeri' => new Galeri,
            'unduhan' => new Unduhan,
            'profil' => new Profil,
            default => null,
        };
    }

    public function cetak(string $jenis, int $id)
    {
        $model = $this->getModel($jenis);
        if (!$model) abort(404);

        $post = $model->with('Kategori')->findOrFail($id);
        $setting = Settings::first();
        $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $tanggal = $post->tanggal ? $post->tanggal->day . ' ' . $bulan[$post->tanggal->month - 1] . ' ' . $post->tanggal->year : '-';

        $pdf = Pdf::loadView('pdf.koran', compact('post', 'jenis', 'setting', 'tanggal'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
                'dpi' => 150,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_left' => 20,
                'margin_right' => 20,
            ]);

        $filename = 'BPMP-NTB-' . ucfirst($jenis) . '-' . $id . '.pdf';
        return $pdf->stream($filename);
    }
}
