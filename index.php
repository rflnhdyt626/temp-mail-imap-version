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
        $_SESSION['user_email'] = null; // Clear user session if login as admin
        $_SESSION['user_alias'] = null; 
        header('Location: ./');
        exit;
    } else {
        $error = "Password Admin salah.";
    }
}

// HANDLE LOGIN USER (EMAIL SPECIFIC)
if (isset($_POST['user_email']) && isset($_POST['user_password'])) {
    $emailInput = strtolower(trim($_POST['user_email']));
    $password = $_POST['user_password'];
    $accessList = get_access_list();

    if (isset($accessList[$emailInput]) && $accessList[$emailInput] === $password) {
        $_SESSION['user_email'] = $emailInput;
        $parts = explode('@', $emailInput);
        $_SESSION['user_alias'] = $parts[0];
        $_SESSION['logged_in'] = false; // Not an admin
        header('Location: ./');
        exit;
    } else {
        $error = "Email atau Password salah.";
    }
}

// HANDLE REGISTER ALIAS (ADMIN ONLY)
if (isset($_POST['register_email']) && !empty($_SESSION['logged_in'])) {
    $email = strtolower(trim($_POST['register_email']));
    $pass = $_POST['register_password'] ?: '123456';
    $list = get_access_list();
    $list[$email] = $pass;
    save_access_list($list);
    header('Location: ./?msg=registered');
    exit;
}

// HANDLE DOMAIN MANAGEMENT (ADMIN ONLY)
if (isset($_POST['add_domain']) && !empty($_SESSION['logged_in'])) {
    $newDomain = strtolower(trim($_POST['add_domain']));
    if ($newDomain) {
        $domains = get_domains();
        if (!in_array($newDomain, $domains)) {
            $domains[] = $newDomain;
            save_domains($domains);
        }
    }
    header('Location: ./');
    exit;
}

if (isset($_GET['delete_domain']) && !empty($_SESSION['logged_in'])) {
    $delDomain = $_GET['delete_domain'];
    $domains = get_domains();
    $domains = array_values(array_filter($domains, fn($d) => $d !== $delDomain));
    save_domains($domains);
    header('Location: ./');
    exit;
}

// REDIRECT IF NOT AUTHORIZED
$is_admin = !empty($_SESSION['logged_in']);
$user_email = $_SESSION['user_email'] ?? null;
$user_alias = $_SESSION['user_alias'] ?? null;
$domains = get_domains();
$defaultDomain = $domains[0] ?? $config['domain'];

if (!$is_admin && !$user_email):
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
                <input type="text" name="user_email" placeholder="Email (contoh: kerja123@<?= htmlspecialchars($defaultDomain) ?>)" required>
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
            <p><?= htmlspecialchars($is_admin ? count($domains) . ' Domains' : $user_email) ?> • <?= $is_admin ? 'Semua Akses' : 'Akses Terbatas' ?></p>
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

            <div class="input-row" style="grid-template-columns: 1fr 180px auto;">
                <input type="text" id="customAlias" placeholder="Masukkan alias manual...">
                <select id="domainSelect" class="btn btn-secondary" style="background: rgba(255,255,255,0.05); text-align: left; padding-left: 10px;">
                    <?php foreach ($domains as $d): ?>
                        <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-secondary" id="useAliasBtn">Gunakan</button>
            </div>

            <div id="registerPanel" style="margin-top:20px; padding-top:20px; border-top:1px solid var(--line);">
                <p style="font-weight:700; margin-bottom:10px;">Daftarkan Email ini untuk User</p>
                <form method="post" style="display:grid; grid-template-columns: 1fr 1fr auto; gap:10px;">
                    <input type="text" name="register_email" id="regEmail" readonly style="background:rgba(255,255,255,0.05);">
                    <input type="text" name="register_password" placeholder="Set Password User" required>
                    <button type="submit" class="btn btn-primary">Daftarkan & Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card" style="padding:24px;">
        <h3 class="section-title">Kelola Domain</h3>
        <p class="muted">Tambah atau hapus domain yang bisa digunakan untuk email sementara.</p>
        
        <form method="post" style="display:flex; gap:10px; margin: 15px 0;">
            <input type="text" name="add_domain" placeholder="tambahdomain.com" style="max-width:300px;">
            <button type="submit" class="btn btn-primary">Tambah Domain</button>
        </form>

        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <?php foreach ($domains as $d): ?>
                <div style="background:rgba(255,255,255,0.05); padding:8px 12px; border-radius:10px; border:1px solid var(--line); display:flex; align-items:center; gap:10px;">
                    <span><?= htmlspecialchars($d) ?></span>
                    <a href="?delete_domain=<?= urlencode($d) ?>" onclick="return confirm('Hapus domain ini?')" style="color:#f87171; text-decoration:none; font-weight:bold;">&times;</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card" style="padding:24px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 class="section-title" style="margin:0;">Email Terdaftar</h3>
            <p class="muted" style="margin:5px 0 0;">Lihat dan kelola semua email yang memiliki akses user.</p>
        </div>
        <a href="registered.php" class="btn btn-secondary" style="text-decoration:none;">Buka Daftar Email →</a>
    </div>
    <?php else: // USER MODE ?>
    <div class="hero">
        <div class="card hero-card">
            <div class="domain-badge">User Access</div>
            <h2>Email Aktif: <?= htmlspecialchars($user_alias) ?></h2>
            <div id="currentEmail" class="email-box"><?= htmlspecialchars($user_email) ?></div>
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
const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;

let currentAlias = isAdmin ? (localStorage.getItem('tm_alias') || '') : <?= json_encode($user_alias) ?>;
let currentDomain = isAdmin ? (localStorage.getItem('tm_domain') || <?= json_encode($defaultDomain) ?>) : <?= json_encode(explode('@', $user_email)[1] ?? $defaultDomain) ?>;
let selectedId = null;
let timer = null;
let currentMessages = [];
let lastMessageId = null;
let initialLoad = true;

// Konfigurasi Audio Web API (Lebih stabil untuk background tab)
let audioCtx = null;
let audioBuffer = null;
let isAudioUnlocked = false;

// Load file suara ke dalam Buffer satu kali saja di awal
async function loadNotificationBuffer() {
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const response = await fetch('notification.mp3');
        const arrayBuffer = await response.arrayBuffer();
        audioBuffer = await audioCtx.decodeAudioData(arrayBuffer);
        console.log('[Audio] File notification.mp3 berhasil dimuat ke buffer.');
    } catch (e) {
        console.error('[Audio] Gagal memuat file mp3:', e);
    }
}

function updateAudioUI(unlocked) {
    const badge = document.getElementById('audioStatusBadge');
    const icon = document.getElementById('audioStatusIcon');
    const text = document.getElementById('audioStatusText');
    if (!badge) return;

    if (unlocked) {
        badge.style.background = "rgba(34,197,94,0.15)";
        badge.style.color = "#4ade80";
        badge.style.borderColor = "rgba(34,197,94,0.3)";
        icon.textContent = "🔊";
        text.innerHTML = 'Notifikasi Aktif <button onclick="playNotificationSound()" style="background:none;border:none;color:inherit;text-decoration:underline;cursor:pointer;padding:0;margin-left:5px;font-size:11px;">(Tes)</button>';
    }
}

async function forceUnlockAudio() {
    if (isAudioUnlocked) return;
    
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') await audioCtx.resume();
    
    if (!audioBuffer) await loadNotificationBuffer();

    // Mainkan suara tes segera
    playNotificationSound();
    
    isAudioUnlocked = true;
    updateAudioUI(true);
    console.log('[Audio] Berhasil di-Unlock lewat interaksi user.');

    ['touchstart', 'click', 'mousedown'].forEach(evt => document.body.removeEventListener(evt, forceUnlockAudio));
}

['touchstart', 'click', 'mousedown', 'keydown'].forEach(evt => 
    document.body.addEventListener(evt, forceUnlockAudio)
);

function playNotificationSound() {
    if (!audioCtx || !audioBuffer) {
        console.warn('[Audio] Buffer belum siap atau AudioContext belum di-unlock.');
        return;
    }

    try {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        
        const source = audioCtx.createBufferSource();
        source.buffer = audioBuffer;
        
        const gainNode = audioCtx.createGain();
        gainNode.gain.value = 1.5; // Naikkan volume 150%
        
        source.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        source.start(0);
        console.log('[Audio] Perintah putar suara berhasil dikirim ke AudioContext.');
    } catch (e) {
        console.error('[Audio] Error saat memutar suara:', e);
    }
}

function currentEmail() {
    return currentAlias ? `${currentAlias}@${currentDomain}` : '';
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
    const box = document.getElementById('currentEmail');
    if(box) box.textContent = currentAlias ? currentEmail() : 'belum ada alias';
    
    const inp = document.getElementById('customAlias');
    if(inp) inp.value = currentAlias;

    const dom = document.getElementById('domainSelect');
    if(dom) dom.value = currentDomain;

    const reg = document.getElementById('regEmail');
    if(reg) reg.value = currentEmail();
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

function normalizeAlias(value) {
    return value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, '');
}

async function generateAlias() {
    if (!isAdmin) return;
    const res = await fetch(`api_generate.php?domain=${encodeURIComponent(currentDomain)}`);
    const data = await res.json();

    currentAlias = data.alias || '';
    selectedId = null;
    localStorage.setItem('tm_alias', currentAlias);
    localStorage.setItem('tm_domain', currentDomain);

    renderEmail();
    await refreshInbox();
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
    console.log(`[Polling] Mengecek inbox untuk: ${currentEmail()}`);
    status.textContent = `Mengecek ${currentEmail()}...`;

    try {
        const res = await fetch(`api_inbox.php?email=${encodeURIComponent(currentEmail())}`);
        const data = await res.json();

        if (!data.ok) {
            console.error('[Polling] Server error:', data.error);
            status.textContent = data.error || 'Gagal memuat inbox.';
            return;
        }

        currentMessages = Array.isArray(data.messages) ? data.messages : [];
        console.log(`[Polling] Berhasil. Dapat ${currentMessages.length} email.`);
        
        // Deteksi email baru dan mainkan suara
        if (currentMessages.length > 0) {
            const topId = currentMessages[0].id;
            console.log(`[Check] ID Email Terbaru: ${topId} | Terakhir: ${lastMessageId}`);

            // LOGIKA DIPERBAIKI: Tetap bunyi jika sebelumnya inbox kosong (lastMessageId null)
            const isDifferent = lastMessageId !== null && String(topId) !== String(lastMessageId);
            const isFirstArrival = lastMessageId === null && !initialLoad;

            if (isDifferent || isFirstArrival) {
                console.log('%c[NOTIF] ADA EMAIL BARU! Memicu suara...', 'color: #22c55e; font-weight: bold; font-size: 14px;');
                playNotificationSound();
            } else if (initialLoad) {
                console.log('[Init] Pemuatan pertama, mencatat ID tanpa bunyi.');
            } else {
                console.log('[Check] Tidak ada email baru.');
            }
            lastMessageId = topId;
        } else {
            console.log('[Index] Inbox kosong.');
            lastMessageId = null; // Reset agar saat ada email masuk nanti, ia terdeteksi sebagai "baru"
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
            console.log(`[UI] Memilih email otomatis ke ID: ${selectedId}`);
        }

        highlightSelected(selectedId);
        await openMessage(selectedId, false);
    } catch (err) {
        console.error('[Polling] Fetch failed:', err);
    }
}

async function openMessage(id, doHighlight = true) {
    if (!currentAlias || !id) return;

    selectedId = id;
    if (doHighlight) highlightSelected(id);
    
    // Tampilkan animasi skeleton/loading yang imersif
    setLoadingViewer();

    const res = await fetch(`api_message.php?email=${encodeURIComponent(currentEmail())}&id=${encodeURIComponent(id)}`);
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

if (isAdmin) {
    document.getElementById('generateBtn').addEventListener('click', generateAlias);
    document.getElementById('useAliasBtn').addEventListener('click', async () => {
        const input = document.getElementById('customAlias');
        const dom = document.getElementById('domainSelect');
        const alias = normalizeAlias(input.value);
        if (!alias) return;
        currentAlias = alias;
        currentDomain = dom.value;
        selectedId = null;
        localStorage.setItem('tm_alias', currentAlias);
        localStorage.setItem('tm_domain', currentDomain);
        renderEmail();
        await refreshInbox();
    });
    document.getElementById('domainSelect').addEventListener('change', (e) => {
        currentDomain = e.target.value;
        localStorage.setItem('tm_domain', currentDomain);
        renderEmail();
    });
}

document.getElementById('copyBtn').addEventListener('click', async () => {
    if (!currentAlias) return;
    await navigator.clipboard.writeText(currentEmail());
    alert('Email dicopy: ' + currentEmail());
});

document.getElementById('refreshBtn').addEventListener('click', refreshInbox);

function startPolling() {
    if (timer) clearInterval(timer);
    timer = setInterval(refreshInbox, pollInterval);
}

(async function init() {
    renderEmail();
    await refreshInbox();
    startPolling();
})();
</script>
</body>
</html>