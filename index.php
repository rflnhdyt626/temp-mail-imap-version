<?php
session_start();
require __DIR__ . '/utils.php';

$config = app_config();
$APP_PASSWORD = $config['admin_password'] ?? 'admin'; // Diambil dari config.php

// HANDLE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ./');
    exit;
}

// HANDLE LOGIN ADMIN
if (isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $APP_PASSWORD) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_alias'] = null; // Clear user session if login as admin
        header('Location: ./');
        exit;
    } else {
        $error = "Password Admin salah.";
    }
}

// HANDLE LOGIN USER (EMAIL SPECIFIC)
if (isset($_POST['user_alias']) && isset($_POST['user_password'])) {
    $alias = clean_alias($_POST['user_alias']);
    $password = $_POST['user_password'];
    $accessList = get_access_list();

    if (isset($accessList[$alias]) && $accessList[$alias] === $password) {
        $_SESSION['user_alias'] = $alias;
        $_SESSION['logged_in'] = false; // Not an admin
        header('Location: ./');
        exit;
    } else {
        $error = "Alias atau Password Email salah.";
    }
}

// HANDLE REGISTER ALIAS (ADMIN ONLY)
if (isset($_POST['register_alias']) && !empty($_SESSION['logged_in'])) {
    $alias = clean_alias($_POST['register_alias']);
    $pass = $_POST['register_password'] ?: '123456';
    $list = get_access_list();
    $list[$alias] = $pass;
    save_access_list($list);
    header('Location: ./?msg=registered');
    exit;
}

// HANDLE DELETE ACCESS (ADMIN ONLY)
if (isset($_GET['delete_access']) && !empty($_SESSION['logged_in'])) {
    $alias = clean_alias($_GET['delete_access']);
    $list = get_access_list();
    unset($list[$alias]);
    save_access_list($list);
    header('Location: ./?msg=deleted');
    exit;
}

// REDIRECT IF NOT AUTHORIZED
$is_admin = !empty($_SESSION['logged_in']);
$user_alias = $_SESSION['user_alias'] ?? null;

if (!$is_admin && !$user_alias):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Temp Mail</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center;
            font-family: Inter, Arial, sans-serif;
            background: radial-gradient(circle at top left, #1d4ed8 0%, transparent 35%),
                        radial-gradient(circle at bottom right, #16a34a 0%, transparent 30%), #020617;
            color: #e5e7eb;
        }
        .login-card {
            width: 100%; max-width: 420px;
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(14px);
            border-radius: 24px; padding: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        h1 { margin: 0 0 10px; font-size: 26px; }
        p { margin: 0 0 24px; color: #94a3b8; font-size: 14px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .tab { cursor: pointer; color: #94a3b8; font-weight: 600; font-size: 14px; padding: 5px 10px; }
        .tab.active { color: #22c55e; border-bottom: 2px solid #22c55e; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        input, button { width: 100%; border: 0; border-radius: 12px; padding: 12px 16px; font-size: 14px; margin-bottom: 12px; }
        input { background: #0f172a; color: #fff; border: 1px solid #334155; }
        button { background: linear-gradient(135deg, #22c55e, #16a34a); color: #052e16; font-weight: 700; cursor: pointer; }
        .error { color: #f87171; font-size: 13px; margin-bottom: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Temp Mail Access</h1>
        <p>Silakan masuk untuk akses inbox.</p>
        
        <?php if(isset($error)): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showTab('user')">User Email</div>
            <div class="tab" onclick="showTab('admin')">Admin Panel</div>
        </div>

        <div id="user-form" class="form-section active">
            <form method="post">
                <input type="text" name="user_alias" placeholder="Alias Email (contoh: kerja123)" required>
                <input type="password" name="user_password" placeholder="Password Email" required>
                <button type="submit">Buka Inbox</button>
            </form>
        </div>

        <div id="admin-form" class="form-section">
            <form method="post">
                <input type="password" name="admin_password" placeholder="Password Master Admin" required>
                <button type="submit">Login Admin</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(type) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(f => f.classList.remove('active'));
            if(type === 'user') {
                document.querySelector('.tab:nth-child(1)').classList.add('active');
                document.getElementById('user-form').classList.add('active');
            } else {
                document.querySelector('.tab:nth-child(2)').classList.add('active');
                document.getElementById('admin-form').classList.add('active');
            }
        }
    </script>
</body>
</html>
<?php
exit;
endif;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temp Mail - <?= $is_admin ? 'Admin' : $user_alias ?></title>
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
            margin-bottom: 20px;
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

        .btn, input[type="text"], input[type="password"] {
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
        
        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        input[type="text"], input[type="password"] {
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

        /* Access Management Table */
        .mgmt-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .mgmt-table th, .mgmt-table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--line);
        }
        .mgmt-table th { color: var(--soft); font-size: 13px; font-weight: normal; }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .input-row { grid-template-columns: 1fr; }
        }

        @keyframes pulse-anim { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .pulse { animation: pulse-anim 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <div class="brand">
            <h1>Temp Mail <small style="font-size:12px; color:var(--green); background:rgba(34,197,94,0.1); padding:4px 8px; border-radius:6px;"><?= $is_admin ? 'ADMIN' : 'USER' ?></small></h1>
            <p><?= htmlspecialchars($config['domain'], ENT_QUOTES, 'UTF-8') ?> • <?= $is_admin ? 'Semua Akses' : 'Akses Terbatas: ' . $user_alias ?></p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
             <div id="audioStatusBadge" style="font-size:12px; padding:6px 12px; border-radius:10px; background:rgba(239,68,68,0.2); color:#f87171; border:1px solid rgba(239,68,68,0.3); display:flex; align-items:center; gap:6px;">
                 <span id="audioStatusIcon">🔇</span> <span id="audioStatusText">Suara Mati</span>
             </div>
             <a class="logout" href="?logout=1">Logout</a>
        </div>
    </div>

    <?php if ($is_admin): ?>
    <div class="hero">
        <div class="card hero-card">
            <div class="domain-badge">Admin Dashboard</div>
            <h2>Kelola Email Sementara</h2>
            
            <div id="currentEmail" class="email-box">memuat...</div>

            <div class="controls">
                <button class="btn btn-primary" id="generateBtn">Generate Random</button>
                <button class="btn btn-secondary" id="copyBtn">Copy</button>
                <button class="btn btn-secondary" id="refreshBtn">Refresh</button>
            </div>

            <div class="input-row">
                <input type="text" id="customAlias" placeholder="Masukkan alias manual...">
                <button class="btn btn-secondary" id="useAliasBtn">Gunakan</button>
            </div>

            <div id="registerPanel" style="margin-top:20px; padding-top:20px; border-top:1px solid var(--line);">
                <p style="font-weight:700; margin-bottom:10px;">Daftarkan Alias ini untuk User</p>
                <form method="post" style="display:grid; grid-template-columns: 1fr 1fr auto; gap:10px;">
                    <input type="text" name="register_alias" id="regAlias" readonly style="background:rgba(255,255,255,0.05);">
                    <input type="text" name="register_password" placeholder="Set Password User" required>
                    <button type="submit" class="btn btn-primary">Daftarkan & Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card" style="padding:24px;">
        <h3 class="section-title">Email Terdaftar (Akses User)</h3>
        <p class="muted">Hanya alias di bawah ini yang bisa dibuka oleh non-admin menggunakan password.</p>
        <div style="overflow-x:auto;">
            <table class="mgmt-table">
                <thead>
                    <tr>
                        <th>Alias</th>
                        <th>Password</th>
                        <th>Link Akses</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $accessList = get_access_list();
                    if (empty($accessList)): ?>
                        <tr><td colspan="4" class="muted" style="text-align:center; padding:20px;">Belum ada email yang didaftarkan.</td></tr>
                    <?php else: 
                        foreach($accessList as $a => $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a) ?></strong></td>
                            <td><code><?= htmlspecialchars($p) ?></code></td>
                            <td><small style="color:var(--blue)">alias: <?= $a ?></small></td>
                            <td>
                                <a href="?delete_access=<?= urlencode($a) ?>" class="btn btn-danger" style="padding:6px 12px; font-size:12px; text-decoration:none;" onclick="return confirm('Hapus akses untuk email ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: // USER MODE ?>
    <div class="hero">
        <div class="card hero-card">
            <div class="domain-badge">User Access</div>
            <h2>Email Aktif: <?= htmlspecialchars($user_alias) ?></h2>
            <div id="currentEmail" class="email-box"><?= htmlspecialchars($user_alias) ?>@<?= $config['domain'] ?></div>
            <div class="controls">
                <button class="btn btn-primary" id="copyBtn">Copy Address</button>
                <button class="btn btn-secondary" id="refreshBtn">Refresh Inbox</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                <iframe id="viewerFrame" class="viewer-frame" sandbox="allow-same-origin" srcdoc="<div style='font-family:Arial,sans-serif;padding:24px;color:#666'>Belum ada email dipilih.</div>"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
const pollInterval = <?= (int)$config['poll_interval_seconds'] * 1000 ?>;
const appDomain = <?= json_encode($config['domain']) ?>;
const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;

let currentAlias = isAdmin ? (localStorage.getItem('tm_alias') || '') : <?= json_encode($user_alias) ?>;
let selectedId = null;
let timer = null;
let currentMessages = [];
let lastMessageId = null;
let initialLoad = true;

// Audio logic
let audioCtx = null, audioBuffer = null, isAudioUnlocked = false;
async function loadNotificationBuffer() {
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const response = await fetch('notification.mp3');
        const arrayBuffer = await response.arrayBuffer();
        audioBuffer = await audioCtx.decodeAudioData(arrayBuffer);
    } catch (e) { console.error('Audio load failed', e); }
}

function updateAudioUI(unlocked) {
    const badge = document.getElementById('audioStatusBadge'), icon = document.getElementById('audioStatusIcon'), text = document.getElementById('audioStatusText');
    if (unlocked) {
        badge.style.background = "rgba(34,197,94,0.15)"; badge.style.color = "#4ade80"; badge.style.borderColor = "rgba(34,197,94,0.3)";
        icon.textContent = "🔊"; text.textContent = "Notifikasi Aktif";
    }
}

async function forceUnlockAudio() {
    if (isAudioUnlocked) return;
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') await audioCtx.resume();
    if (!audioBuffer) await loadNotificationBuffer();
    isAudioUnlocked = true; updateAudioUI(true);
}
['touchstart', 'click', 'keydown'].forEach(evt => document.body.addEventListener(evt, forceUnlockAudio));

function playNotificationSound() {
    if (!audioCtx || !audioBuffer) return;
    try {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        const sc = audioCtx.createBufferSource(); sc.buffer = audioBuffer;
        const gn = audioCtx.createGain(); gn.gain.value = 1.5;
        sc.connect(gn); gn.connect(audioCtx.destination); sc.start(0);
    } catch (e) {}
}

function currentEmail() { return currentAlias ? `${currentAlias}@${appDomain}` : ''; }
function escapeHtml(v) { return String(v||'').replace(/[&<>'"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c])); }

function renderEmail() {
    const box = document.getElementById('currentEmail');
    if(box) box.textContent = currentAlias ? currentEmail() : 'belum ada alias';
    const inp = document.getElementById('customAlias');
    if(inp) inp.value = currentAlias;
    const reg = document.getElementById('regAlias');
    if(reg) reg.value = currentAlias;
}

function setViewerEmpty(text) {
    document.getElementById('viewerHeader').innerHTML = `<h3 class="viewer-subject">${escapeHtml(text)}</h3><div class="viewer-meta">Pilih email dari daftar inbox.</div>`;
    document.getElementById('viewerFrame').srcdoc = "<div style='font-family:Arial,sans-serif;padding:24px;color:#666'>Belum ada email dipilih.</div>";
}

function setLoadingViewer() {
    document.getElementById('viewerHeader').innerHTML = `<h3 class="viewer-subject pulse" style="color: var(--soft);">Memuat email...</h3><div class="viewer-meta">Sedang mengunduh konten</div>`;
    document.getElementById('viewerFrame').srcdoc = `<center style="margin-top:100px; font-family:sans-serif; color:#64748b;">Memuat...</center>`;
}

async function refreshInbox() {
    if (!currentAlias) return;
    const status = document.getElementById('inboxStatus');
    status.textContent = `Mengecek ${currentAlias}...`;
    try {
        const res = await fetch(`api_inbox.php?alias=${encodeURIComponent(currentAlias)}`);
        const data = await res.json();
        if (!data.ok) { status.textContent = data.error; return; }
        currentMessages = data.messages || [];
        if (currentMessages.length > 0) {
            const topId = currentMessages[0].id;
            if (lastMessageId !== null && String(topId) !== String(lastMessageId)) playNotificationSound();
            lastMessageId = topId;
        } else { lastMessageId = null; }
        initialLoad = false;
        renderInbox(currentMessages, `${data.count} email • updated ${new Date().toLocaleTimeString()}`);
        if (currentMessages.length > 0) {
            const stillIdx = currentMessages.findIndex(m => String(m.id) === String(selectedId));
            if (selectedId === null || stillIdx === -1) { selectedId = currentMessages[0].id; openMessage(selectedId, false); }
            highlightSelected(selectedId);
        }
    } catch (err) { console.error(err); }
}

function renderInbox(msgs, info) {
    const list = document.getElementById('messageList'), stat = document.getElementById('inboxStatus');
    stat.textContent = info; list.innerHTML = '';
    if (!msgs.length) {
        list.innerHTML = `<div class="empty">Inbox kosong untuk <strong>${currentAlias}</strong>.</div>`;
        setViewerEmpty('Belum ada pesan'); return;
    }
    msgs.forEach(m => {
        const div = document.createElement('div');
        div.className = 'message-item' + (selectedId === m.id ? ' active' : '');
        div.dataset.id = m.id;
        div.innerHTML = `<div class="message-subject">${escapeHtml(m.subject || '(No Subject)')}</div><div class="message-meta">${escapeHtml(m.from)}</div>`;
        div.onclick = () => openMessage(m.id);
        list.appendChild(div);
    });
}

function highlightSelected(id) {
    document.querySelectorAll('.message-item').forEach(el => el.classList.toggle('active', el.dataset.id === String(id)));
}

async function openMessage(id, highlight = true) {
    if (!currentAlias || !id) return;
    selectedId = id; if (highlight) highlightSelected(id);
    setLoadingViewer();
    const res = await fetch(`api_message.php?alias=${encodeURIComponent(currentAlias)}&id=${id}`);
    const data = await res.json();
    if (!data.ok) { setViewerEmpty(data.error); return; }
    const m = data.message;
    document.getElementById('viewerHeader').innerHTML = `<h3 class="viewer-subject">${escapeHtml(m.subject)}</h3><div class="viewer-meta">Dari: ${escapeHtml(m.from)} • ${m.date}</div>`;
    document.getElementById('viewerFrame').srcdoc = m.rendered_html;
}

if (isAdmin) {
    document.getElementById('generateBtn').onclick = async () => {
        const r = await fetch('api_generate.php'); const d = await r.json();
        currentAlias = d.alias; localStorage.setItem('tm_alias', currentAlias);
        renderEmail(); refreshInbox();
    };
    document.getElementById('useAliasBtn').onclick = () => {
        const v = document.getElementById('customAlias').value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, '');
        if (v) { currentAlias = v; localStorage.setItem('tm_alias', v); renderEmail(); refreshInbox(); }
    };
}

document.getElementById('copyBtn').onclick = () => {
    navigator.clipboard.writeText(currentEmail()); alert('Alamat email dicopy!');
};

document.getElementById('refreshBtn').onclick = refreshInbox;

renderEmail();
refreshInbox();
setInterval(refreshInbox, pollInterval);
</script>
</body>
</html>