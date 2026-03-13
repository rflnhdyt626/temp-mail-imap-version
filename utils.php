<?php

function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $configPath = __DIR__ . '/config.php';
        if (!file_exists($configPath)) {
            http_response_code(500);
            exit('config.php belum dibuat. Copy config.sample.php menjadi config.php lalu isi kredensial IMAP.');
        }
        $config = require $configPath;
        date_default_timezone_set($config['timezone'] ?? 'Asia/Jakarta');
    }
    return $config;
}

function imap_connection_string(array $config): string
{
    $imap = $config['imap'];
    return '{' . $imap['host'] . ':' . $imap['port'] . $imap['flags'] . '}' . $imap['mailbox'];
}

function open_imap()
{
    static $streamCache = null;

    if ($streamCache !== null) {
        // Cek jika koneksi lama masih hidup
        if (imap_ping($streamCache)) {
            return $streamCache;
        } else {
            // Tutup jika sudah mati sebelum buka baru
            @imap_close($streamCache);
        }
    }

    $config = app_config();
    if (!function_exists('imap_open')) {
        http_response_code(500);
        exit('Ekstensi PHP IMAP belum aktif. Aktifkan dulu di Select PHP Version / PHP Extensions.');
    }
    
    // Gunakan OP_HALFOPEN untuk koneksi yang lebih ringan saat belum memilih mailbox (opsional)
    // Atau limit jumlah percobaan reconnect
    $streamCache = @imap_open(
        imap_connection_string($config),
        $config['imap']['username'],
        $config['imap']['password'],
        0, 
        1, 
        ['DISABLE_AUTHENTICATOR' => 'GSSAPI'] // Mencegah fallback auth lambat
    );

    if (!$streamCache) {
        http_response_code(500);
        $error = imap_last_error() ?: 'Gagal membuka koneksi IMAP.';
        exit('IMAP error: ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
    }

    return $streamCache;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function random_local_part(int $length = 10): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

function clean_alias(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9._-]/', '', $value);
    return $value ?: random_local_part();
}

function alias_email(string $alias): string
{
    $config = app_config();
    return clean_alias($alias) . '@' . $config['domain'];
}

function get_header_targets(object $header): array
{
    $targets = [];
    foreach (['toaddress', 'ccaddress', 'reply_toaddress'] as $field) {
        if (!empty($header->$field)) {
            preg_match_all('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $header->$field, $matches);
            foreach ($matches[0] as $email) {
                $targets[] = strtolower($email);
            }
        }
    }

    foreach (['to', 'cc'] as $field) {
        if (!empty($header->$field) && is_array($header->$field)) {
            foreach ($header->$field as $entry) {
                if (!empty($entry->mailbox) && !empty($entry->host)) {
                    $targets[] = strtolower($entry->mailbox . '@' . $entry->host);
                }
            }
        }
    }

    return array_values(array_unique($targets));
}

function header_matches_alias(object $header, string $targetEmail): bool
{
    $targets = get_header_targets($header);
    return in_array(strtolower($targetEmail), $targets, true);
}

function decode_mime_str(?string $text): string
{
    $text = (string) $text;
    if ($text === '') {
        return '';
    }

    $elements = imap_mime_header_decode($text);
    $decoded = '';
    foreach ($elements as $element) {
        $decoded .= $element->text;
    }
    return $decoded;
}

function extract_part($stream, int $msgNo, object $structure, string $partNumber = '1'): array
{
    $result = ['plain' => '', 'html' => ''];

    if (!isset($structure->parts) || !is_array($structure->parts) || count($structure->parts) === 0) {
        $body = imap_body($stream, $msgNo, FT_PEEK) ?: '';
        if (($structure->encoding ?? 0) == 3) {
            $body = base64_decode($body, true) ?: $body;
        } elseif (($structure->encoding ?? 0) == 4) {
            $body = quoted_printable_decode($body);
        }

        if (($structure->subtype ?? '') === 'HTML') {
            $result['html'] = $body;
        } else {
            $result['plain'] = $body;
        }
        return $result;
    }

    foreach ($structure->parts as $index => $part) {
        $currentPartNumber = $partNumber === '' ? (string)($index + 1) : $partNumber . '.' . ($index + 1);
        if ((int)($part->type ?? 0) === 0) {
            $data = imap_fetchbody($stream, $msgNo, $currentPartNumber, FT_PEEK) ?: '';
            if (($part->encoding ?? 0) == 3) {
                $data = base64_decode($data, true) ?: $data;
            } elseif (($part->encoding ?? 0) == 4) {
                $data = quoted_printable_decode($data);
            }

            if (strtoupper($part->subtype ?? 'PLAIN') === 'HTML') {
                $result['html'] .= $data;
            } else {
                $result['plain'] .= $data;
            }
        } elseif (!empty($part->parts)) {
            $nested = extract_part($stream, $msgNo, $part, $currentPartNumber);
            $result['plain'] .= $nested['plain'];
            $result['html'] .= $nested['html'];
        }
    }

    return $result;
}

function html_to_safe_text(string $html): string
{
    $html = preg_replace('#<style[^>]*>.*?</style>#si', '', $html);
    $html = preg_replace('#<script[^>]*>.*?</script>#si', '', $html);
    $html = strip_tags($html);
    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = preg_replace('/\s+/u', ' ', $html);
    return trim($html);
}

function message_summary($stream, int $msgNo): array
{
    $overview = imap_fetch_overview($stream, (string)$msgNo, 0);
    $header = imap_headerinfo($stream, $msgNo);
    $structure = imap_fetchstructure($stream, $msgNo);
    $parts = extract_part($stream, $msgNo, $structure, '');
    $plain = trim($parts['plain']);
    $html = trim($parts['html']);
    $fallbackText = $plain !== '' ? $plain : html_to_safe_text($html);
    $config = app_config();

    return [
        'id' => $msgNo,
        'subject' => decode_mime_str($overview[0]->subject ?? '(Tanpa subjek)'),
        'from' => decode_mime_str($overview[0]->from ?? ''),
        'date' => isset($overview[0]->date) ? date('Y-m-d H:i:s', strtotime($overview[0]->date)) : '',
        'preview' => mb_substr($fallbackText, 0, (int)$config['message_preview_length']) . (mb_strlen($fallbackText) > (int)$config['message_preview_length'] ? '…' : ''),
        'to' => get_header_targets($header),
        'has_html' => $html !== '',
    ];
}
