<?php
require __DIR__ . '/utils.php';

$alias = clean_alias($_GET['alias'] ?? '');
$id = (int)($_GET['id'] ?? 0);
if ($alias === '' || $id <= 0) {
    json_response(['ok' => false, 'error' => 'Alias dan id wajib diisi.'], 422);
}

$targetEmail = alias_email($alias);
$stream = open_imap();
$header = imap_headerinfo($stream, $id);
if (!$header || !header_matches_alias($header, $targetEmail)) {
    imap_close($stream);
    json_response(['ok' => false, 'error' => 'Email tidak ditemukan untuk alias ini.'], 404);
}

$overview = imap_fetch_overview($stream, (string)$id, 0);
$structure = imap_fetchstructure($stream, $id);
$parts = extract_part($stream, $id, $structure, '');
$plain = trim($parts['plain']);
$html = trim($parts['html']);
$bodyText = $plain !== '' ? nl2br(htmlspecialchars($plain, ENT_QUOTES, 'UTF-8')) : nl2br(htmlspecialchars(html_to_safe_text($html), ENT_QUOTES, 'UTF-8'));
$bodyHtml = $html !== '' ? $html : '<pre style="white-space:pre-wrap;word-break:break-word;">' . htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') . '</pre>';

// imap_close($stream); // Biarkan tetap terbuka untuk reuse

json_response([
    'ok' => true,
    'email' => $targetEmail,
    'message' => [
        'id' => $id,
        'subject' => decode_mime_str($overview[0]->subject ?? '(Tanpa subjek)'),
        'from' => decode_mime_str($overview[0]->from ?? ''),
        'date' => isset($overview[0]->date) ? date('Y-m-d H:i:s', strtotime($overview[0]->date)) : '',
        'to' => get_header_targets($header),
        'plain_html' => $bodyText,
        'rendered_html' => $bodyHtml,
    ]
]);
