<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Analytics Chatbot SI INTAN - {{ $periodLabel }}</title>
  <style>
    @page { margin: 24px 24px 28px; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; background: #fff; font-size: 10.5px; line-height: 1.45; }
    .page { width: 100%; }
    .cover { background: #0f3d68; color: #fff; border-radius: 18px; padding: 24px 26px; position: relative; overflow: hidden; }
    .cover:after { content: ""; position: absolute; right: -80px; top: -95px; width: 230px; height: 230px; border-radius: 120px; background: rgba(255,255,255,.12); }
    .brand-row { width: 100%; border-collapse: collapse; }
    .brand-cell { vertical-align: top; }
    .logo-box { width: 58px; height: 58px; border-radius: 16px; background: #fff; color: #0f3d68; text-align: center; font-size: 20px; font-weight: 900; line-height: 58px; margin-right: 12px; }
    .title { font-size: 25px; font-weight: 900; letter-spacing: -.4px; margin: 0 0 4px; }
    .subtitle { margin: 0; font-size: 11px; opacity: .9; }
    .period-badge { display: inline-block; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28); border-radius: 999px; padding: 8px 13px; font-size: 11px; font-weight: 800; text-align: right; }
    .meta { margin-top: 16px; color: rgba(255,255,255,.84); font-size: 10px; }
    .section { margin-top: 16px; page-break-inside: avoid; }
    .section.breakable { page-break-inside: auto; }
    .section-title { margin: 0 0 9px; font-size: 13px; font-weight: 900; color: #0f172a; letter-spacing: -.1px; }
    .section-title .line { display: inline-block; width: 5px; height: 14px; background: #2563eb; border-radius: 8px; vertical-align: -2px; margin-right: 7px; }
    .muted { color: #64748b; }
    .small { font-size: 9px; }
    .stats-table { width: 100%; border-collapse: separate; border-spacing: 7px; margin-left: -7px; margin-right: -7px; }
    .stat-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 11px 12px; min-height: 72px; }
    .stat-label { color: #64748b; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .35px; }
    .stat-value { font-size: 20px; font-weight: 900; margin-top: 4px; color: #0f172a; }
    .stat-note { color: #64748b; font-size: 9px; margin-top: 3px; }
    .blue { color: #2563eb; } .green { color: #059669; } .red { color: #dc2626; } .amber { color: #d97706; } .purple { color: #7c3aed; }
    .insight-box { border: 1px solid #dbeafe; background: #eff6ff; border-radius: 14px; padding: 12px 14px; color: #1e3a8a; }
    .two-col { width: 100%; border-collapse: separate; border-spacing: 10px; margin-left: -10px; margin-right: -10px; }
    .panel { border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px; background: #fff; vertical-align: top; }
    .panel-title { font-size: 11px; font-weight: 900; margin-bottom: 8px; color: #0f172a; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #f1f5f9; color: #475569; padding: 7px 8px; font-size: 8.5px; text-align: left; text-transform: uppercase; letter-spacing: .35px; border-bottom: 1px solid #e2e8f0; }
    table.data td { padding: 7px 8px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
    table.data tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .pill { display: inline-block; border-radius: 999px; padding: 2px 8px; font-size: 8.5px; font-weight: 900; background: #eef2ff; color: #4338ca; }
    .bar-bg { width: 100%; height: 7px; background: #e2e8f0; border-radius: 99px; overflow: hidden; margin-top: 3px; }
    .bar-fill { height: 7px; border-radius: 99px; background: #2563eb; }
    .bar-fill.green { background: #059669; } .bar-fill.red { background: #dc2626; } .bar-fill.amber { background: #d97706; } .bar-fill.purple { background: #7c3aed; }
    .empty { padding: 14px; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; background: #f8fafc; text-align: center; }
    .footer { margin-top: 18px; padding-top: 10px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 8.5px; text-align: center; }
  </style>
</head>
<body>
  <div class="page">
    <div class="cover">
      <table class="brand-row">
        <tr>
          <td class="brand-cell" style="width:70px;"><div class="logo-box">SI</div></td>
          <td class="brand-cell">
            <h1 class="title">Laporan Analytics Chatbot SI INTAN</h1>
            <p class="subtitle">Statistik penggunaan chatbot SI INTAN — BPMP Provinsi NTB</p>
          </td>
          <td class="brand-cell" style="text-align:right;width:170px;">
            <div class="period-badge">{{ $periodLabel }}</div>
          </td>
        </tr>
      </table>
      <div class="meta">
        Periode data: {{ $fromStr }} s.d. {{ $toStr }}<br>
        Dibuat: {{ now()->translatedFormat('d F Y H:i') }} oleh Admin
      </div>
    </div>

    <div class="section">
      <h2 class="section-title"><span class="line"></span>Ringkasan Eksekutif</h2>
      <table class="stats-table">
        <tr>
          <td class="stat-card" style="width:25%;"><div class="stat-label">Total Percakapan</div><div class="stat-value blue">{{ $fmt($analytics['total'] ?? 0) }}</div><div class="stat-note">Sepanjang waktu</div></td>
          <td class="stat-card" style="width:25%;"><div class="stat-label">Hari Ini</div><div class="stat-value green">{{ $fmt($analytics['today'] ?? 0) }}</div><div class="stat-note">Percakapan hari ini</div></td>
          <td class="stat-card" style="width:25%;"><div class="stat-label">7 Hari Terakhir</div><div class="stat-value purple">{{ $fmt($analytics['thisWeek'] ?? 0) }}</div><div class="stat-note">Minggu ini</div></td>
          <td class="stat-card" style="width:25%;"><div class="stat-label">Total User</div><div class="stat-value amber">{{ $fmt($analytics['users'] ?? 0) }}</div><div class="stat-note">User terdaftar</div></td>
        </tr>
        <tr>
          <td class="stat-card"><div class="stat-label">Total Sesi</div><div class="stat-value blue">{{ $fmt($analytics['sessions'] ?? 0) }}</div><div class="stat-note">Seluruh sesi chat</div></td>
          <td class="stat-card"><div class="stat-label">Sesi Aktif</div><div class="stat-value green">{{ $fmt($analytics['openSessions'] ?? 0) }}</div><div class="stat-note">Sedang berlangsung</div></td>
          <td class="stat-card"><div class="stat-label">Total Token</div><div class="stat-value red">{{ $fmt($analytics['totalTokens'] ?? 0) }}</div><div class="stat-note">Token terpakai</div></td>
          <td class="stat-card"><div class="stat-label">Latensi Rata-rata</div><div class="stat-value purple">{{ $fmt(round($analytics['avgResponseTime'] ?? 0)) }} ms</div><div class="stat-note">End-to-end</div></td>
        </tr>
      </table>
      <div class="insight-box">
        <b>Interpretasi cepat:</b> Pada periode {{ $periodLabel }}, chatbot SI INTAN mencatat <b>{{ $fmt($analytics['total'] ?? 0) }}</b> total percakapan dari <b>{{ $fmt($analytics['users'] ?? 0) }}</b> user terdaftar. Hari ini terdapat <b>{{ $fmt($analytics['today'] ?? 0) }}</b> percakapan dengan latensi rata-rata <b>{{ $fmt(round($analytics['avgResponseTime'] ?? 0)) }} ms</b>.
      </div>
    </div>

    <div class="section">
      <table class="two-col">
        <tr>
          <td class="panel" style="width:50%;">
            <div class="panel-title">Top 10 Pertanyaan</div>
            @if(count($topQueries))
              <table class="data">
                <thead><tr><th style="width:24px;">#</th><th>Pertanyaan</th><th class="text-right" style="width:55px;">Jumlah</th></tr></thead>
                <tbody>
                @foreach($topQueries as $index => $query)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $query->user_message ?? '-' }}</td>
                    <td class="text-right"><span class="pill">{{ $fmt($query->count ?? 0) }}x</span></td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            @else
              <div class="empty">Belum ada data pertanyaan.</div>
            @endif
          </td>
          <td class="panel" style="width:50%;">
            <div class="panel-title">Rasio Sumber Respons</div>
            @if(count($sources))
              @php $totalSources = $sources->sum(); @endphp
              @foreach($sources as $source => $count)
                @php
                  $percent = $totalSources > 0 ? round(($count / $totalSources) * 100, 1) : 0;
                  $sourceColor = $source === 'ai' ? 'green' : ($source === 'keyword' ? 'blue' : ($source === 'quota' ? 'red' : 'purple'));
                  $sourceLabel = match($source) { 'ai' => 'AI (OpenAI)', 'keyword' => 'Keyword Match', 'intent' => 'Intent Detection', 'menu' => 'Menu Navigasi', 'quota' => 'Quota Exceeded', default => ucfirst($source) };
                @endphp
                <div style="margin-bottom:9px;">
                  <table style="width:100%;border-collapse:collapse;"><tr><td><b>{{ $sourceLabel }}</b></td><td class="text-right muted">{{ $fmt($count) }} ({{ $percent }}%)</td></tr></table>
                  <div class="bar-bg"><div class="bar-fill {{ $sourceColor }}" style="width:{{ min(100, max(1, $percent)) }}%;"></div></div>
                </div>
              @endforeach
            @else
              <div class="empty">Belum ada data sumber respons.</div>
            @endif
          </td>
        </tr>
      </table>
    </div>

    <div class="section breakable">
      <h2 class="section-title"><span class="line"></span>Aktivitas Harian (30 Hari Terakhir)</h2>
      <div class="panel">
        @if(count($daily))
          @php $maxTotal = $daily->max('total') ?: 1; @endphp
          @foreach($daily as $item)
            @php $width = max(2, round(($item->total ?? 0) / $maxTotal * 100, 1)); @endphp
            <table style="width:100%;border-collapse:collapse;margin-bottom:5px;">
              <tr>
                <td style="width:80px;color:#475569;">{{ $item->date ?? '-' }}</td>
                <td><div class="bar-bg"><div class="bar-fill" style="width:{{ $width }}%;"></div></div></td>
                <td class="text-right" style="width:42px;font-weight:800;">{{ $fmt($item->total ?? 0) }}</td>
                <td class="text-right muted" style="width:70px;">{{ $fmt($item->tokens ?? 0) }} tok</td>
              </tr>
            </table>
          @endforeach
        @else
          <div class="empty">Tidak ada aktivitas harian pada periode ini.</div>
        @endif
      </div>
    </div>

    <div class="section">
      <h2 class="section-title"><span class="line"></span>Token &amp; Performa</h2>
      <table class="stats-table">
        <tr>
          <td class="stat-card" style="width:33%;"><div class="stat-label">Total Token</div><div class="stat-value blue">{{ $fmt($analytics['totalTokens'] ?? 0) }}</div><div class="stat-note">Seluruh percakapan</div></td>
          <td class="stat-card" style="width:33%;"><div class="stat-label">Rata-rata Token/Percakapan</div><div class="stat-value green">{{ ($analytics['total'] ?? 0) > 0 ? $fmt(round(($analytics['totalTokens'] ?? 0) / $analytics['total'])) : '0' }}</div><div class="stat-note">Efisiensi penggunaan</div></td>
          <td class="stat-card" style="width:33%;"><div class="stat-label">Latensi Rata-rata</div><div class="stat-value amber">{{ $fmt(round($analytics['avgResponseTime'] ?? 0)) }} ms</div><div class="stat-note">End-to-end response</div></td>
        </tr>
      </table>
    </div>

    <div class="footer">
      Laporan otomatis SI INTAN Analytics · BPMP Provinsi NTB · {{ $periodLabel }}
    </div>
  </div>
</body>
</html>