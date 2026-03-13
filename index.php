<?php
session_start();

$APP_PASSWORD = 'RafliGalaprimesLove';

if (isset($_POST['login_password'])) {
    if ($_POST['login_password'] === $APP_PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: /');
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /');
    exit;
}

if (empty($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Private Access</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Inter, Arial, sans-serif;
            background:
                radial-gradient(circle at top left, #1d4ed8 0%, transparent 35%),
                radial-gradient(circle at bottom right, #16a34a 0%, transparent 30%),
                #020617;
            color: #e5e7eb;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(14px);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }
        p {
            margin: 0 0 20px;
            color: #94a3b8;
        }
        input, button {
            width: 100%;
            border: 0;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 15px;
        }
        input {
            background: #0f172a;
            color: #fff;
            border: 1px solid #334155;
            margin-bottom: 12px;
        }
        button {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #052e16;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Private Temp Mail</h1>
        <p>Masuk untuk mengakses inbox sementara.</p>
        <form method="post">
            <input type="password" name="login_password" placeholder="Password" autocomplete="current-password">
            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>
<?php
exit;
endif;

require __DIR__ . '/utils.php';
$config = app_config();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temp Mail</title>
    <style>
        :root {
            --bg: #07111f;
            --panel: rgba(15, 23, 42, 0.88);
            --panel-2: #0f172a;
            --soft: #94a3b8;
            --line: rgba(255,255,255,0.08);
            --white: #e2e8f0;
            --green: #22c55e;
            --green-dark: #166534;
            --blue: #3b82f6;
            --shadow: 0 18px 50px rgba(0,0,0,.28);
            --radius: 22px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            color: var(--white);
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.20), transparent 30%),
                radial-gradient(circle at bottom right, rgba(34,197,94,.14), transparent 30%),
                var(--bg);
        }

        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 16px;
        }

        .brand h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: -.02em;
        }

        .brand p {
            margin: 6px 0 0;
            color: var(--soft);
            font-size: 14px;
        }

        .logout {
            color: #cbd5e1;
            text-decoration: none;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.03);
            padding: 10px 14px;
            border-radius: 12px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1fr;
            margin-bottom: 20px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .hero-card {
            padding: 26px;
        }

        .domain-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #bbf7d0;
            background: rgba(34,197,94,.12);
            border: 1px solid rgba(34,197,94,.18);
            padding: 7px 12px;
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .hero-card h2 {
            margin: 0 0 10px;
            font-size: 32px;
        }

        .hero-card .sub {
            margin: 0 0 20px;
            color: var(--soft);
            max-width: 700px;
            line-height: 1.6;
        }

        .email-box {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            font-size: clamp(22px, 4vw, 34px);
            font-weight: 800;
            word-break: break-word;
            margin-bottom: 16px;
        }

        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 14px;
        }

        .input-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin-top: 12px;
        }

        .muted {
            color: var(--soft);
            font-size: 13px;
        }

        .btn, input[type="text"] {
            border-radius: 14px;
            padding: 13px 15px;
            font-size: 14px;
        }

        .btn {
            border: 0;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .btn:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(1px) scale(0.96);
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #052e16;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.04);
            color: #e5e7eb;
            border: 1px solid var(--line);
        }

        input[type="text"] {
            width: 100%;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.03);
            color: #fff;
            outline: none;
        }

        .layout {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 20px;
        }

        .sidebar,
        .viewer {
            padding: 20px;
            min-height: 70vh;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .status {
            color: var(--soft);
            font-size: 13px;
            margin-bottom: 16px;
        }

        .message-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: calc(70vh - 70px);
            overflow: auto;
            padding-right: 4px;
        }

        .message-item {
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.025);
            border-radius: 16px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .message-item:hover {
            transform: translateY(-2px);
            border-color: rgba(59,130,246,.4);
            background: rgba(59,130,246,.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .message-item:active {
            transform: translateY(1px) scale(0.97);
        }

        .message-item.active {
            border-color: rgba(34,197,94,.45);
            background: rgba(34,197,94,.10);
        }

        .message-subject {
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.35;
        }

        .message-meta {
            font-size: 12px;
            color: var(--soft);
            margin-bottom: 8px;
        }

        .message-preview {
            font-size: 13px;
            color: #cbd5e1;
            line-height: 1.45;
        }

        .empty {
            border: 1px dashed var(--line);
            border-radius: 16px;
            padding: 26px;
            text-align: center;
            color: var(--soft);
            background: rgba(255,255,255,0.02);
        }

        .viewer-head {
            padding-bottom: 14px;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--line);
        }

        .viewer-subject {
            margin: 0 0 8px;
            font-size: 24px;
            line-height: 1.3;
        }

        .viewer-meta {
            color: var(--soft);
            font-size: 13px;
            line-height: 1.6;
        }

        .viewer-body {
            background: #fff;
            color: #111827;
            border-radius: 18px;
            min-height: 420px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.08);
        }

        .viewer-frame {
            width: 100%;
            min-height: 420px;
            border: 0;
            display: block;
            background: #fff;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar,
            .viewer {
                min-height: auto;
            }

            .input-row {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @keyframes pulse-anim {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse { animation: pulse-anim 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <div class="brand">
            <h1>Temp Mail</h1>
            <p><?= htmlspecialchars($config['domain'], ENT_QUOTES, 'UTF-8') ?> • inbox otomatis refresh tiap <?= (int)$config['poll_interval_seconds'] ?> detik</p>
        </div>
        <a class="logout" href="?logout=1">Logout</a>
    </div>

    <div class="hero">
        <div class="card hero-card">
            <div class="domain-badge">Domain aktif: <?= htmlspecialchars($config['domain'], ENT_QUOTES, 'UTF-8') ?></div>
            <h2>Email sementara, cepat dan simpel</h2>
            <p class="sub">Generate alamat random, pakai alias manual kalau perlu, lalu baca email masuk langsung dari browser.</p>

            <div id="currentEmail" class="email-box">memuat...</div>

            <div class="controls">
                <button class="btn btn-primary" id="generateBtn">Generate Email</button>
                <button class="btn btn-secondary" id="copyBtn">Copy</button>
                <button class="btn btn-secondary" id="refreshBtn">Refresh</button>
            </div>

            <div class="input-row">
                <input type="text" id="customAlias" placeholder="Masukkan alias manual, contoh: login2026">
                <button class="btn btn-secondary" id="useAliasBtn">Pakai Alias</button>
            </div>

            <p class="muted" style="margin-top:12px;">Hanya karakter a-z, angka, titik, garis bawah, dan strip yang dipakai.</p>
        </div>
    </div>

    <div class="layout">
        <div class="card sidebar">
            <h3 class="section-title">Inbox</h3>
            <div id="inboxStatus" class="status">Menunggu email...</div>
            <div id="messageList" class="message-list"></div>
        </div>

        <div class="card viewer">
            <div id="viewerHeader" class="viewer-head">
                <h3 class="viewer-subject">Belum ada email dipilih</h3>
                <div class="viewer-meta">Pilih email dari daftar inbox.</div>
            </div>
            <div class="viewer-body">
                <iframe
                    id="viewerFrame"
                    class="viewer-frame"
                    sandbox="allow-same-origin"
                    srcdoc="<div style='font-family:Arial,sans-serif;padding:24px;color:#666'>Belum ada email dipilih.</div>">
                </iframe>
            </div>
        </div>
    </div>
</div>

<script>
const pollInterval = <?= (int)$config['poll_interval_seconds'] * 1000 ?>;
const appDomain = <?= json_encode($config['domain']) ?>;

let currentAlias = localStorage.getItem('tm_alias') || '';
let selectedId = null;
let timer = null;
let currentMessages = [];
let lastMessageId = null;
let initialLoad = true;

// Buat elemen audio HTML5 secara dinamis
const notifAudio = new Audio('notification.mp3'); // Ganti file ini jika ingin custom
notifAudio.volume = 1.0; // Volume maksimal (0.0 sampai 1.0)

// Unlock audio saat user interaksi pertama kali (mengatasi kebijakan Auto-Play browser)
function unlockAudio() {
    notifAudio.play().then(() => {
        notifAudio.pause();
        notifAudio.currentTime = 0;
    }).catch(e => console.warn('Audio di-lock browser', e));
}

['click', 'touchstart', 'keydown'].forEach(evt => 
    document.body.addEventListener(evt, unlockAudio, { once: true })
);

function playNotificationSound() {
    try {
        // Reset waktu ke awal dan mainkan
        notifAudio.currentTime = 0;
        let playPromise = notifAudio.play();
        if (playPromise !== undefined) {
            playPromise.catch(e => console.warn('Audio auto-play diblokir', e));
        }
    } catch (e) {
        console.warn('Gagal memutar audio', e);
    }
}

function currentEmail() {
    return currentAlias ? `${currentAlias}@${appDomain}` : '';
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>'"]/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[ch]));
}

function renderEmail() {
    document.getElementById('currentEmail').textContent = currentAlias ? currentEmail() : 'belum ada alias';
    document.getElementById('customAlias').value = currentAlias;
}

function setViewerEmpty(text = 'Belum ada email dipilih.') {
    document.getElementById('viewerHeader').innerHTML = `
        <h3 class="viewer-subject">${escapeHtml(text)}</h3>
        <div class="viewer-meta">Pilih email dari daftar inbox.</div>
    `;
    document.getElementById('viewerFrame').srcdoc =
        "<div style='font-family:Arial,sans-serif;padding:24px;color:#666'>Belum ada email dipilih.</div>";
}

function setLoadingViewer() {
    document.getElementById('viewerHeader').innerHTML = `
        <h3 class="viewer-subject pulse" style="color: var(--soft);">Memuat email...</h3>
        <div class="viewer-meta">Mohon tunggu, sedang menghubungi server</div>
    `;
    document.getElementById('viewerFrame').srcdoc = `
        <style>
            @keyframes spin { to { transform: rotate(360deg); } }
            body { font-family: 'Inter', Arial, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 85vh; margin: 0; background: #fff; color: #64748b; }
            .spinner { width: 40px; height: 40px; border: 4px solid rgba(59,130,246,0.1); border-radius: 50%; border-top-color: #3b82f6; animation: spin 0.8s ease-in-out infinite; margin-bottom: 20px; }
        </style>
        <body>
            <div class="spinner"></div>
            <div style="font-weight:500;">Mengunduh isi pesan...</div>
        </body>
    `;
}

async function generateAlias() {
    const res = await fetch('api_generate.php');
    const data = await res.json();

    currentAlias = data.alias || '';
    selectedId = null;
    localStorage.setItem('tm_alias', currentAlias);

    renderEmail();
    await refreshInbox();
}

function normalizeAlias(value) {
    return value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, '');
}

function renderInbox(messages, infoText) {
    const list = document.getElementById('messageList');
    const status = document.getElementById('inboxStatus');

    status.textContent = infoText;
    list.innerHTML = '';

    if (!messages.length) {
        list.innerHTML = `<div class="empty">Belum ada email masuk ke <strong>${escapeHtml(currentEmail())}</strong>.</div>`;
        setViewerEmpty('Inbox masih kosong');
        return;
    }

    messages.forEach((msg) => {
        const item = document.createElement('div');
        item.className = 'message-item' + (selectedId === msg.id ? ' active' : '');
        item.dataset.id = msg.id;
        item.innerHTML = `
            <div class="message-subject">${escapeHtml(msg.subject || '(Tanpa subjek)')}</div>
            <div class="message-meta">${escapeHtml(msg.from || '-')} • ${escapeHtml(msg.date || '-')}</div>
            <div class="message-preview">${escapeHtml(msg.preview || '')}</div>
        `;
        item.addEventListener('click', () => openMessage(msg.id));
        list.appendChild(item);
    });
}

function highlightSelected(id) {
    document.querySelectorAll('.message-item').forEach(el => {
        el.classList.toggle('active', el.dataset.id === String(id));
    });
}

async function refreshInbox() {
    if (!currentAlias) return;

    const status = document.getElementById('inboxStatus');
    status.textContent = `Mengecek ${currentEmail()}...`;

    const res = await fetch(`api_inbox.php?alias=${encodeURIComponent(currentAlias)}`);
    const data = await res.json();

    if (!data.ok) {
        status.textContent = data.error || 'Gagal memuat inbox.';
        return;
    }

    currentMessages = Array.isArray(data.messages) ? data.messages : [];
    
    // Deteksi email baru dan mainkan suara
    if (currentMessages.length > 0) {
        const topId = currentMessages[0].id;
        if (!initialLoad && lastMessageId && topId !== lastMessageId) {
            playNotificationSound();
        }
        lastMessageId = topId;
    }
    initialLoad = false;

    renderInbox(currentMessages, `${data.count} email • update ${data.polled_at}`);

    if (!currentMessages.length) {
        selectedId = null;
        return;
    }

    const stillExists = currentMessages.some(msg => String(msg.id) === String(selectedId));
    if (!selectedId || !stillExists) {
        selectedId = currentMessages[0].id;
    }

    highlightSelected(selectedId);
    await openMessage(selectedId, false);
}

async function openMessage(id, doHighlight = true) {
    if (!currentAlias || !id) return;

    selectedId = id;
    if (doHighlight) highlightSelected(id);
    
    // Tampilkan animasi skeleton/loading yang imersif
    setLoadingViewer();

    const res = await fetch(`api_message.php?alias=${encodeURIComponent(currentAlias)}&id=${encodeURIComponent(id)}`);
    const data = await res.json();

    if (!data.ok) {
        document.getElementById('viewerHeader').innerHTML = `
            <h3 class="viewer-subject">Gagal membuka email</h3>
            <div class="viewer-meta">${escapeHtml(data.error || 'Terjadi kesalahan.')}</div>
        `;
        return;
    }

    const msg = data.message || {};
    document.getElementById('viewerHeader').innerHTML = `
        <h3 class="viewer-subject">${escapeHtml(msg.subject || '(Tanpa subjek)')}</h3>
        <div class="viewer-meta">${escapeHtml(msg.from || '-')} • ${escapeHtml(msg.date || '-')}</div>
    `;
    document.getElementById('viewerFrame').srcdoc = msg.rendered_html || '<div style="padding:24px;font-family:Arial">Konten kosong.</div>';

    if (doHighlight) highlightSelected(id);
}

document.getElementById('generateBtn').addEventListener('click', generateAlias);

document.getElementById('copyBtn').addEventListener('click', async () => {
    if (!currentAlias) return;
    await navigator.clipboard.writeText(currentEmail());
    alert('Email dicopy: ' + currentEmail());
});

document.getElementById('useAliasBtn').addEventListener('click', async () => {
    const input = document.getElementById('customAlias');
    const alias = normalizeAlias(input.value);

    if (!alias) return;

    currentAlias = alias;
    selectedId = null;
    localStorage.setItem('tm_alias', currentAlias);

    renderEmail();
    await refreshInbox();
});

document.getElementById('refreshBtn').addEventListener('click', refreshInbox);

function startPolling() {
    if (timer) clearInterval(timer);
    timer = setInterval(refreshInbox, pollInterval);
}

(async function init() {
    if (!currentAlias) {
        await generateAlias();
    } else {
        currentAlias = normalizeAlias(currentAlias);
        renderEmail();
        await refreshInbox();
    }
    startPolling();
})();
</script>
</body>
</html>