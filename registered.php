<?php
session_start();
require __DIR__ . '/utils.php';

$config = app_config();
$is_admin = !empty($_SESSION['logged_in']);

if (!$is_admin) {
    header('Location: ./');
    exit;
}

// HANDLE DELETE ACCESS
if (isset($_GET['delete_access'])) {
    $alias = clean_alias($_GET['delete_access']);
    $list = get_access_list();
    unset($list[$alias]);
    save_access_list($list);
    header('Location: registered.php?msg=deleted');
    exit;
}

$accessList = get_access_list();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Terdaftar - Admin</title>
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
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .btn-back {
            color: var(--soft);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--white);
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
            padding: 30px;
        }

        .mgmt-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mgmt-table th, .mgmt-table td {
            text-align: left;
            padding: 16px;
            border-bottom: 1px solid var(--line);
        }

        .mgmt-table th {
            color: var(--soft);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger:hover {
            background: #ef4444;
            color: white;
        }

        .muted {
            color: var(--soft);
            font-size: 14px;
        }

        code {
            background: rgba(255,255,255,0.05);
            padding: 4px 8px;
            border-radius: 6px;
            font-family: monospace;
            color: #cbd5e1;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-success {
            background: rgba(34,197,94,0.1);
            color: var(--green);
            border: 1px solid rgba(34,197,94,0.2);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <a href="./" class="btn-back">← Kembali ke Dashboard</a>
            <h1>Email Terdaftar</h1>
        </div>
        <div class="muted"><?= count($accessList) ?> Email</div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">Berhasil menghapus akses email.</div>
    <?php endif; ?>

    <div class="card">
        <p class="muted" style="margin-top: 0; margin-bottom: 24px;">Berikut adalah daftar email yang telah didaftarkan dan dapat diakses oleh user non-admin.</p>
        
        <div style="overflow-x:auto;">
            <table class="mgmt-table">
                <thead>
                    <tr>
                        <th>Alias</th>
                        <th>Password</th>
                        <th>Alamat Lengkap</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accessList)): ?>
                        <tr>
                            <td colspan="4" class="muted" style="text-align:center; padding:40px;">Belum ada email yang didaftarkan.</td>
                        </tr>
                    <?php else: 
                        foreach($accessList as $a => $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a) ?></strong></td>
                            <td><code><?= htmlspecialchars($p) ?></code></td>
                            <td><small style="color:var(--blue)"><?= htmlspecialchars($a) ?>@<?= htmlspecialchars($config['domain']) ?></small></td>
                            <td style="text-align: right;">
                                <a href="?delete_access=<?= urlencode($a) ?>" class="btn-danger" onclick="return confirm('Hapus akses untuk email ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
