<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Transcript Live Chat - Sesi #{{ $sessionId }}</title>
  <style>
    @page { margin: 24px; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; font-size: 10.5px; line-height: 1.45; }
    .header { background: #0f3d68; color: #fff; border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; }
    .header h1 { font-size: 18px; font-weight: 900; margin: 0 0 4px; }
    .header p { margin: 0; font-size: 10px; opacity: .85; }
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .meta-table td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #f1f5f9; }
    .meta-table .label { color: #64748b; font-weight: 600; width: 100px; }
    .meta-table .value { color: #0f172a; font-weight: 500; }
    .messages { margin-top: 10px; }
    .msg-row { margin-bottom: 8px; }
    .msg-bubble { padding: 8px 12px; border-radius: 12px; font-size: 10px; line-height: 1.5; max-width: 80%; }
    .msg-bubble.user { background: #f1f5f9; border: 1px solid #e2e8f0; margin-right: auto; }
    .msg-bubble.admin { background: #dbeafe; border: 1px solid #bfdbfe; margin-left: auto; }
    .msg-bubble.system { background: #fef3c7; border: 1px solid #fde68a; margin: 6px auto; text-align: center; max-width: 90%; color: #92400e; }
    .msg-sender { font-weight: 700; font-size: 9px; display: block; margin-bottom: 2px; }
    .msg-sender.admin-label { color: #1d4ed8; }
    .msg-sender.user-label { color: #475569; }
    .msg-time { font-size: 8px; color: #94a3b8; display: block; margin-top: 3px; }
    .msg-time.right { text-align: right; }
    .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 8px; text-align: center; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Transcript Live Chat SI INTAN</h1>
    <p>Sesi #{{ $sessionId }} — BPMP Provinsi NTB</p>
  </div>

  <table class="meta-table">
    <tr><td class="label">User</td><td class="value">{{ $user->nama ?? '-' }}</td></tr>
    <tr><td class="label">Instansi</td><td class="value">{{ $user->instansi ?? '-' }}</td></tr>
    <tr><td class="label">Kontak</td><td class="value">{{ $user->kontak ?? '-' }}</td></tr>
    <tr><td class="label">Sesi Dimulai</td><td class="value">{{ $session->created_at ?? '-' }}</td></tr>
    <tr><td class="label">Status</td><td class="value">{{ ucfirst($session->status ?? '-') }}</td></tr>
    <tr><td class="label">Total Pesan</td><td class="value">{{ $messages->count() }}</td></tr>
  </table>

  <div class="messages">
    @foreach($messages as $m)
      @php
        $isAdmin = $m->sender_type === 'admin';
        $isSystem = $m->sender_type === 'system';
        $sender = $isAdmin ? 'Admin' : ($isSystem ? 'System' : ($user->nama ?? 'User'));
      @endphp
      <div class="msg-row">
        <div class="msg-bubble {{ $m->sender_type }}">
          @if(!$isSystem)
            <span class="msg-sender {{ $isAdmin ? 'admin-label' : 'user-label' }}">{{ $sender }}</span>
          @endif
          <div>{{ $m->message }}</div>
          <span class="msg-time {{ $isAdmin ? 'right' : '' }}">{{ $m->created_at }}</span>
        </div>
      </div>
    @endforeach
  </div>

  <div class="footer">
    Transcript Live Chat SI INTAN · Sesi #{{ $sessionId }} · Dicetak {{ now()->translatedFormat('d F Y H:i') }}
  </div>
</body>
</html>