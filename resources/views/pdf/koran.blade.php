<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $post->title }} - {{ $setting->title ?? 'BPMP NTB' }}</title>
    <style>
        @page { margin: 15mm 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #1a1a1a;
        }

        .header {
            border-bottom: 4px double #111;
            padding-bottom: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header .newspaper-name {
            font-family: 'Georgia', serif;
            font-size: 28pt;
            font-weight: bold;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #111;
            line-height: 1.1;
        }
        .header .tagline {
            font-size: 9pt;
            color: #666;
            font-style: italic;
            margin-top: 4px;
            letter-spacing: 1px;
        }
        .header .edition {
            font-size: 8pt;
            color: #999;
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
        }

        .headline {
            font-family: 'Georgia', serif;
            font-size: 24pt;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
            margin: 20px 0 8px;
            color: #111;
        }

        .sub-headline {
            font-size: 12pt;
            text-align: center;
            color: #555;
            font-style: italic;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .byline {
            text-align: center;
            font-size: 9pt;
            color: #777;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #ddd;
        }
        .byline strong {
            color: #333;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-image {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            margin-bottom: 6px;
        }
        .image-caption {
            font-size: 8pt;
            color: #999;
            text-align: center;
            font-style: italic;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        .content {
            text-align: justify;
            font-size: 10.5pt;
            line-height: 1.7;
            color: #222;
            column-count: 2;
            column-gap: 24px;
            column-rule: 1px solid #e5e5e5;
        }
        .content p {
            margin-bottom: 10px;
            text-indent: 20px;
        }
        .content p:first-child {
            text-indent: 0;
        }
        .content p:first-child::first-letter {
            font-size: 36pt;
            font-weight: bold;
            float: left;
            line-height: 0.8;
            padding-right: 6px;
            padding-top: 4px;
            color: #111;
            font-family: 'Georgia', serif;
        }
        .content img {
            max-width: 100%;
            height: auto;
            margin: 8px 0;
            display: block;
        }
        .content h1, .content h2, .content h3, .content h4 {
            column-span: all;
            margin: 16px 0 8px;
            font-family: 'Georgia', serif;
        }
        .content blockquote {
            border-left: 3px solid #333;
            padding-left: 12px;
            margin: 12px 0;
            font-style: italic;
            color: #555;
        }

        .tags-section {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #999;
        }
        .tags-section span {
            display: inline-block;
            background: #f0f0f0;
            padding: 2px 8px;
            margin: 2px 4px 2px 0;
            border-radius: 2px;
            font-size: 7.5pt;
            color: #555;
        }

        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 4px double #111;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }
        .footer .org-name {
            font-weight: bold;
            font-size: 9pt;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .separator {
            text-align: center;
            margin: 16px 0;
            color: #ccc;
            font-size: 14pt;
            letter-spacing: 8px;
        }

        .watermark {
            position: fixed;
            bottom: 8mm;
            right: 15mm;
            font-size: 7pt;
            color: #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="newspaper-name">{{ $setting->title ?? 'BPMP Provinsi NTB' }}</div>
        <div class="tagline">{{ $setting->description ?? 'Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat' }}</div>
        <div class="edition">
            <span>{{ ucfirst($jenis) }} | {{ $post->Kategori->title ?? 'Umum' }}</span>
            <span>{{ $tanggal }}</span>
        </div>
    </div>

    <h1 class="headline">{{ $post->title }}</h1>

    @if($post->writer)
    <div class="byline">
        Oleh: <strong>{{ $post->writer }}</strong> &mdash; {{ $tanggal }}
    </div>
    @endif

    @if($post->images)
    <div>
        <img src="{{ public_path('upload/' . $jenis . '/' . $post->images) }}" class="main-image" alt="{{ $post->title }}">
        @if($post->tags)
        <div class="image-caption">{{ Str::limit($post->tags, 120) }}</div>
        @endif
    </div>
    @endif

    <div class="separator">&bull; &bull; &bull;</div>

    <div class="content">
        {!! $post->content !!}
    </div>

    @if($post->tags)
    <div class="tags-section">
        <strong>TAGS:</strong>
        @foreach(explode(',', $post->tags) as $tag)
            <span>{{ trim($tag) }}</span>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <div class="org-name">{{ $setting->title ?? 'BPMP Provinsi NTB' }}</div>
        <div>{{ $setting->alamat ?? '' }}</div>
        <div>Telp: {{ $setting->phone ?? '' }} | Email: {{ $setting->email ?? '' }}</div>
        <div style="margin-top:6px;color:#bbb;">Dicetak pada {{ date('d/m/Y H:i') }}</div>
    </div>

    <div class="watermark">{{ $setting->title ?? 'BPMP NTB' }} &mdash; {{ url('/') }}</div>
</body>
</html>
