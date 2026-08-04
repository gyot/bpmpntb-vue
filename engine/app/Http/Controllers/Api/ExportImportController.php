<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log, Schema};
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportImportController extends Controller
{
    private array $typeTableMap = [
        'konten-berita' => 'berita',
        'konten-artikel' => 'artikel',
        'konten-buletin' => 'buletin',
        'konten-jurnal' => 'jurnal',
        'konten-kliping' => 'kliping',
        'konten-pengumuman' => 'pengumuman',
        'konten-galeri' => 'galeri',
        'konten-unduhan' => 'unduhan',
        'konten-profil' => 'profil',
        'konten-renstra' => 'renstra',
        'konten-lakin' => 'lakin',
        'konten-perjanjian_kinerja' => 'perjanjian_kinerja',
        'kategori-berita' => 'kategoriberita',
        'kategori-artikel' => 'kategoriartikel',
        'kategori-buletin' => 'kategoribuletin',
        'kategori-jurnal' => 'kategorijurnal',
        'kategori-kliping' => 'kategorikliping',
        'kategori-pengumuman' => 'kategoripengumuman',
        'kategori-galeri' => 'kategorigaleri',
        'kategori-unduhan' => 'kategoriunduhan',
        'kategori-profil' => 'kategoriprofil',
        'kategori-renstra' => 'kategorirenstra',
        'kategori-lakin' => 'kategorilakin',
        'kategori-perjanjian_kinerja' => 'kategoriperjanjian_kinerja',
        'sliders' => 'sliders',
        'layanans' => 'layanans',
        'external-links' => 'externallink',
        'chatbot-responses' => 'chatbot_responses',
        'chatbot-intents' => 'chatbot_intent',
        'ai-configs' => 'ai_configurations',
        'broadcast' => 'wa_broadcast_logs',
        'ppid-informations' => 'ppid_informations',
        'ppid-standards' => 'ppid_standards',
        'ppid-regulations' => 'ppid_regulations',
        'ppid-external-links' => 'ppid_external_links',
        'settings' => 'settings',
        'users' => 'users',
        'chatbot-settings' => 'chatbot_settings',
    ];

    private array $typeLabels = [
        'konten-berita' => 'Berita',
        'konten-artikel' => 'Artikel',
        'konten-buletin' => 'Buletin',
        'konten-jurnal' => 'Jurnal',
        'konten-kliping' => 'Kliping',
        'konten-pengumuman' => 'Pengumuman',
        'konten-galeri' => 'Galeri',
        'konten-unduhan' => 'Unduhan',
        'konten-profil' => 'Profil',
        'konten-renstra' => 'Renstra',
        'konten-lakin' => 'Laporan Kinerja',
        'konten-perjanjian_kinerja' => 'Perjanjian Kinerja',
        'kategori-berita' => 'Kategori Berita',
        'kategori-artikel' => 'Kategori Artikel',
        'kategori-buletin' => 'Kategori Buletin',
        'kategori-jurnal' => 'Kategori Jurnal',
        'kategori-kliping' => 'Kategori Kliping',
        'kategori-pengumuman' => 'Kategori Pengumuman',
        'kategori-galeri' => 'Kategori Galeri',
        'kategori-unduhan' => 'Kategori Unduhan',
        'kategori-profil' => 'Kategori Profil',
        'kategori-renstra' => 'Kategori Renstra',
        'kategori-lakin' => 'Kategori Laporan Kinerja',
        'kategori-perjanjian_kinerja' => 'Kategori Perjanjian Kinerja',
        'sliders' => 'Sliders',
        'layanans' => 'Layanan',
        'external-links' => 'Link Eksternal',
        'chatbot-responses' => 'Chatbot Responses',
        'chatbot-intents' => 'Chatbot Intents',
        'ai-configs' => 'AI Configurations',
        'broadcast' => 'Riwayat Broadcast',
        'ppid-informations' => 'PPID Informasi',
        'ppid-standards' => 'PPID Standar',
        'ppid-regulations' => 'PPID Regulasi',
        'ppid-external-links' => 'PPID Link Eksternal',
        'settings' => 'Pengaturan Website',
        'users' => 'Users',
        'chatbot-settings' => 'Chatbot Settings',
    ];

    private array $kontenTypes = [
        'konten-berita', 'konten-artikel', 'konten-buletin', 'konten-jurnal',
        'konten-kliping', 'konten-pengumuman', 'konten-galeri', 'konten-unduhan', 'konten-profil',
        'konten-renstra', 'konten-lakin', 'konten-perjanjian_kinerja',
    ];

    private function getJenisFromType(string $type): string
    {
        return str_replace('konten-', '', $type);
    }

    private function getDateColumn(string $table): string
    {
        $cols = Schema::getColumnListing($table);
        if (in_array('tanggal', $cols)) return 'tanggal';
        if (in_array('created_at', $cols)) return 'created_at';
        return 'id';
    }

    private function resolveTable(string $type): ?string
    {
        return $this->typeTableMap[$type] ?? null;
    }

    public function types()
    {
        $types = [];
        foreach ($this->typeTableMap as $key => $table) {
            $exists = Schema::hasTable($table);
            $count = $exists ? DB::table($table)->count() : 0;
            $hasDate = $exists && in_array($this->getDateColumn($table), ['tanggal', 'created_at']);
            $types[] = [
                'key' => $key,
                'label' => $this->typeLabels[$key] ?? $key,
                'table' => $table,
                'count' => $count,
                'exists' => $exists,
                'has_date' => $hasDate,
                'is_konten' => in_array($key, $this->kontenTypes),
            ];
        }
        return response()->json($types);
    }

    public function export(Request $request, string $type)
    {
        $table = $this->resolveTable($type);
        if (!$table || !Schema::hasTable($table)) {
            return response()->json(['error' => 'Tipe data tidak ditemukan'], 404);
        }

        $query = DB::table($table);
        if ($table === 'users') {
            $query->select('id', 'name', 'email', 'role', 'id_seksi', 'created_at', 'updated_at');
        }
        if ($table === 'settings') {
            $query->select('id', 'logo', 'title', 'description', 'footer', 'favicon', 'facebook', 'twitter', 'instagram', 'youtube', 'whatsapp', 'alamat', 'phone', 'hp', 'email', 'map');
        }
        if ($table === 'wa_broadcast_logs') {
            $query->select('id', 'message', 'total_numbers', 'total_sent', 'admin_id', 'created_at');
        }

        $from = $request->query('from');
        $to = $request->query('to');
        $dateCol = $this->getDateColumn($table);
        if ($from && $dateCol !== 'id') {
            $query->whereDate($dateCol, '>=', $from);
        }
        if ($to && $dateCol !== 'id') {
            $query->whereDate($dateCol, '<=', $to);
        }

        $data = $query->orderBy('id')->get();
        $format = $request->query('format', 'json');
        $label = $this->typeLabels[$type] ?? $type;
        $isKonten = in_array($type, $this->kontenTypes);
        $jenis = $isKonten ? $this->getJenisFromType($type) : null;
        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
        $filename = 'export_' . $type . '_' . date('Y-m-d_His');

        if ($isKonten && !$data->isEmpty()) {
            $data = $data->map(function($row) use ($jenis, $baseUrl) {
                $slug = $row->slug ?? '';
                $link = $baseUrl . '/post/' . $jenis . '/' . $row->id . '/' . $slug;
                $row = (array) $row;
                $row['link'] = $link;
                return (object) $row;
            });
        }

        if ($format === 'xlsx') {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(substr($label, 0, 31));

            if ($data->isEmpty()) {
                $sheet->setCellValue('A1', 'Tidak ada data');
            } else {
                $columns = array_keys((array) $data->first());
                foreach ($columns as $col => $name) {
                    $colLetter = Coordinate::stringFromColumnIndex($col + 1);
                    $sheet->setCellValue($colLetter . '1', $name);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }
                $sheet->getStyle('1')->getFont()->setBold(true);

                $linkColIdx = array_search('link', $columns);

                foreach ($data as $rowIdx => $row) {
                    foreach ($columns as $col => $name) {
                        $colLetter = Coordinate::stringFromColumnIndex($col + 1);
                        $val = $row->$name ?? '';
                        if (is_string($val) && strlen($val) > 5000) $val = substr($val, 0, 5000) . '...';
                        $cell = $sheet->setCellValue($colLetter . ($rowIdx + 2), $val);
                        if ($col === $linkColIdx && $val) {
                            $sheet->getCell($colLetter . ($rowIdx + 2))->getHyperlink()->setUrl($val);
                        }
                    }
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $tempFile = tempnam(sys_get_temp_dir(), 'export_');
            $writer->save($tempFile);

            return response()->download($tempFile, $filename . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        return response()->json([
            'type' => $type,
            'label' => $label,
            'table' => $table,
            'exported_at' => now()->toIso8601String(),
            'date_filter' => $from || $to ? ['from' => $from, 'to' => $to] : null,
            'total' => $data->count(),
            'data' => $data,
        ])->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
    }

    public function import(Request $request, string $type)
    {
        $table = $this->resolveTable($type);
        if (!$table || !Schema::hasTable($table)) {
            return response()->json(['error' => 'Tipe data tidak ditemukan'], 404);
        }

        $request->validate([
            'file' => 'required|file|mimes:json,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();

        try {
            if ($ext === 'json') {
                $content = file_get_contents($file->getRealPath());
                $parsed = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'File JSON tidak valid: ' . json_last_error_msg()], 422);
                }
                if (isset($parsed['data']) && is_array($parsed['data'])) {
                    $rows = $parsed['data'];
                } elseif (isset($parsed[0])) {
                    $rows = $parsed;
                } else {
                    return response()->json(['error' => 'Format JSON tidak dikenali. Gunakan file export dari sistem ini.'], 422);
                }
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = [];
                $headers = [];
                foreach ($sheet->getRowIterator(1) as $rowIdx => $row) {
                    $cells = $row->getCells();
                    if ($rowIdx === 1) {
                        foreach ($cells as $cell) $headers[] = $cell->getValue();
                        continue;
                    }
                    $rowData = [];
                    foreach ($cells as $col => $cell) {
                        $key = $headers[$col] ?? "col_$col";
                        $rowData[$key] = $cell->getValue();
                    }
                    $rows[] = $rowData;
                }
            }

            if (empty($rows)) {
                return response()->json(['error' => 'File kosong atau tidak ada data'], 422);
            }

            $skipCols = ['id', 'created_at', 'updated_at'];
            if ($type === 'users') $skipCols[] = 'password';

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();
            try {
                foreach ($rows as $i => $row) {
                    $clean = [];
                    foreach ($row as $key => $val) {
                        if (in_array($key, $skipCols)) continue;
                        $clean[$key] = $val;
                    }

                    if (empty($clean)) {
                        $skipped++;
                        continue;
                    }

                    try {
                        if ($type === 'settings') {
                            $existing = DB::table($table)->first();
                            if ($existing) {
                                DB::table($table)->where('id', $existing->id)->update($clean);
                            } else {
                                DB::table($table)->insert($clean);
                            }
                        } else {
                            $clean['created_at'] = $clean['created_at'] ?? now();
                            $clean['updated_at'] = $clean['updated_at'] ?? now();
                            DB::table($table)->insert($clean);
                        }
                        $imported++;
                    } catch (\Throwable $e) {
                        $errors[] = "Baris " . ($i + 1) . ": " . $e->getMessage();
                        $skipped++;
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['error' => 'Import gagal: ' . $e->getMessage()], 500);
            }

            return response()->json([
                'status' => 'ok',
                'message' => "Import selesai: {$imported} data berhasil, {$skipped} dilewati",
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => array_slice($errors, 0, 10),
            ]);
        } catch (\Throwable $e) {
            Log::error('Import error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membaca file: ' . $e->getMessage()], 422);
        }
    }

    public function downloadTemplate(string $type)
    {
        $table = $this->resolveTable($type);
        if (!$table || !Schema::hasTable($table)) {
            return response()->json(['error' => 'Tipe data tidak ditemukan'], 404);
        }

        $columns = Schema::getColumnListing($table);
        $label = $this->typeLabels[$type] ?? $type;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($label, 0, 31));

        foreach ($columns as $col => $name) {
            $colLetter = Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($colLetter . '1', $name);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet->getStyle('1')->getFont()->setBold(true);

        $sample = DB::table($table)->first();
        if ($sample) {
            foreach ($columns as $col => $name) {
                $colLetter = Coordinate::stringFromColumnIndex($col + 1);
                $val = $sample->$name ?? '';
                if (is_string($val) && strlen($val) > 200) $val = substr($val, 0, 200) . '...';
                $sheet->setCellValue($colLetter . '2', $val);
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempFile = tempnam(sys_get_temp_dir(), 'tmpl_');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_' . $type . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
