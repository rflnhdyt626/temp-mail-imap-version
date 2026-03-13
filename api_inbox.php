<?php
require __DIR__ . '/utils.php';

$startTime = microtime(true);

$alias = clean_alias($_GET['alias'] ?? '');
if ($alias === '') {
    json_response(['ok' => false, 'error' => 'Alias wajib diisi.'], 422);
}

$targetEmail = alias_email($alias);
$stream = open_imap();

$total = imap_num_msg($stream);

$messages = [];
$maxScan = 50; // Pada IMAP, 50-100 scan sangat cepat (mili-detik) karena bulk command
$maxResults = 20;

if ($total > 0) {
    $startMsg = max(1, $total - $maxScan + 1);
    $sequence = "$startMsg:$total";
    
    // Fetch overview secara bulk (sangat stabil untuk cross-check header)
    $overviews = imap_fetch_overview($stream, $sequence, 0);
    
    if (is_array($overviews)) {
        // Balik urutan untuk mendapatkan email terbaru
        usort($overviews, function($a, $b) {
            return $b->msgno <=> $a->msgno;
        });

        foreach ($overviews as $item) {
            $to = strtolower($item->to ?? '');
            $cc = strtolower($item->cc ?? '');

            if (
                strpos($to, strtolower($targetEmail)) === false &&
                strpos($cc, strtolower($targetEmail)) === false
            ) {
                continue;
            }

            $messages[] = [
                'id' => $item->msgno,
                'subject' => decode_mime_str($item->subject ?? ''),
                'from' => decode_mime_str($item->from ?? ''),
                'to' => $item->to ?? '',
                'date' => isset($item->date) ? date('Y-m-d H:i:s', strtotime($item->date)) : '',
                'seen' => !empty($item->seen),
            ];

            if (count($messages) >= $maxResults) {
                break;
            }
        }
    }
}

$total = imap_num_msg($stream);
// imap_close($stream); // Jangan ditutup agar proxy/koneksi bisa di-reuse pada runtime yang sama (jika dipanggil via sub-request dalam FCGI)

$durationMs = round((microtime(true) - $startTime) * 1000, 2);

json_response([
    'ok' => true,
    'alias' => $alias,
    'email' => $targetEmail,
    'count' => count($messages),
    'messages' => $messages,
    'scanned_messages' => min($maxScan, $total),
    'total_messages' => $total,
    'polled_at' => date('Y-m-d H:i:s'),
    'load_time_ms' => $durationMs,
]); 