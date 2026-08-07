<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <title>@yield('title', 'BPMP Provinsi NTB - Balai Penjaminan Mutu Pendidikan')</title>
    <meta name="description" content="@yield('description', 'Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat. Bersama menjamin mutu, melayani sepenuh hati. Layanan penjaminan mutu pendidikan untuk masyarakat NTB.')">
    <meta name="keywords" content="@yield('keywords', 'BPMP, NTB, penjaminan mutu pendidikan, Nusa Tenggara Barat, pendidikan, layanan publik, PPID')">
    <meta name="author" content="BPMP Provinsi NTB">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="@yield('canonical', 'https://bpmpntb.kemendikdasmen.go.id')">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="BPMP Provinsi NTB">
    <meta property="og:title" content="@yield('og_title', 'BPMP Provinsi NTB - Balai Penjaminan Mutu Pendidikan')">
    <meta property="og:description" content="@yield('og_description', 'Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat. Layanan penjaminan mutu pendidikan untuk masyarakat NTB.')">
    <meta property="og:url" content="@yield('og_url', 'https://bpmpntb.kemendikdasmen.go.id')">
    <meta property="og:image" content="@yield('og_image', 'https://bpmpntb.kemendikdasmen.go.id/kantor_depan.jpg')">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', 'BPMP Provinsi NTB')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat')">
    <meta name="twitter:image" content="@yield('twitter_image', 'https://bpmpntb.kemendikdasmen.go.id/kantor_depan.jpg')">

    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BPMP NTB">
    <link rel="apple-touch-icon" href="/pwa/icon-192.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <link rel="preload" as="style" href="/vendor/fontawesome/css/all.min.css" onload="this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css"></noscript>
    <link rel="stylesheet" href="/fonts/quicksand/quicksand.css">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "GovernmentOrganization",
        "name": "Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat",
        "alternateName": "BPMP Provinsi NTB",
        "url": "https://bpmpntb.kemendikdasmen.go.id",
        "logo": "https://bpmpntb.kemendikdasmen.go.id/upload/settings/logo.png",
        "description": "Balai Penjaminan Mutu Pendidikan Provinsi Nusa Tenggara Barat - Kementerian Pendidikan Dasar dan Menengah",
        "address": {
            "@type": "PostalAddress",
            "addressRegion": "NTB",
            "addressCountry": "ID"
        },
        "parentOrganization": {
            "@type": "GovernmentOrganization",
            "name": "Kementerian Pendidikan Dasar dan Menengah"
        },
        "sameAs": [
            "http://www.youtube.com/@bpmp_ntb"
        ]
    }
    </script>

    <style>
        body{font-family:'Quicksand',system-ui,-apple-system,'Segoe UI',sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;margin:0;overflow-x:hidden}
        #app-loader{position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:20px;background:linear-gradient(135deg,#0A1628 0%,#0D1F35 100%);z-index:9999;transition:opacity .5s ease}
        #app-loader.fade-out{opacity:0;pointer-events:none}
        #app-loader .loader-logo{width:72px;height:72px;object-fit:contain;animation:pulse 2.5s ease-in-out infinite;filter:brightness(0) invert(1);opacity:0.9}
        #app-loader .spinner{width:32px;height:32px;border:3px solid rgba(255,255,255,0.1);border-top-color:#1976D2;border-radius:50%;animation:spin .8s linear infinite}
        #app-loader .loader-text{font-size:12px;font-weight:500;color:rgba(255,255,255,0.3);letter-spacing:0.15em;text-transform:uppercase}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes pulse{0%,100%{opacity:.8;transform:scale(1)}50%{opacity:1;transform:scale(1.05)}}
    </style>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app">
        <div id="app-loader">
            <img src="/upload/settings/logo.png" alt="BPMP NTB" class="loader-logo" onerror="this.style.display='none'">
            <div class="spinner"></div>
            <div class="loader-text">Memuat...</div>
        </div>
    </div>
</body>
</html>
