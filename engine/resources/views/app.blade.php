<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e40af">
    <title>{{ config('app.name', 'BPMP NTB') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e40af">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BPMP NTB">
    <link rel="apple-touch-icon" href="/pwa/icon-192.svg">
    <link rel="stylesheet" href="/fonts/quicksand/quicksand.css">
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
    <script src="/vendor/chartjs/chart.umd.js"></script>
    <link rel="stylesheet" href="/vendor/quill/quill.snow.css">
    <script src="/vendor/quill/quill.js"></script>
    <style>
        body{font-family:'Quicksand',system-ui,sans-serif;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}
        #app-loader{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:#fff;z-index:9999;}
        #app-loader .spinner{width:40px;height:40px;border:3px solid #e5e7eb;border-top-color:#2563eb;border-radius:50%;animation:spin .7s linear infinite;}
        @keyframes spin{to{transform:rotate(360deg)}}
        .ql-toolbar.ql-snow{border:1px solid #e5e7eb!important;border-radius:16px 16px 0 0!important;background:#f9fafb;}
        .ql-container.ql-snow{border:1px solid #e5e7eb!important;border-top:none!important;border-radius:0 0 16px 16px!important;font-size:15px;font-family:'Quicksand',sans-serif;}
        .ql-editor{min-height:250px;line-height:1.8;}
        .ql-editor img{max-width:100%;height:auto;border-radius:12px;cursor:pointer;transition:box-shadow .2s;}
        .ql-editor img:hover{box-shadow:0 0 0 3px rgba(37,99,235,.3);}
    </style>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app"><div id="app-loader"><div class="spinner"></div></div></div>
</body>
</html>
