<?php
declare(strict_types=1);

function mailer_config(): array
{
    $mode = getenv('JSA_MAIL_MODE') ?: '';
    $mode = strtolower(trim((string)$mode));

    return [
        'mode' => $mode, // smtp | mail | outbox | (auto)
        'smtp_host' => getenv('JSA_SMTP_HOST') ?: '',
        'smtp_port' => (int)(getenv('JSA_SMTP_PORT') ?: 587),
        'smtp_user' => getenv('JSA_SMTP_USER') ?: '',
        'smtp_pass' => getenv('JSA_SMTP_PASS') ?: '',
        'smtp_secure' => strtolower((string)(getenv('JSA_SMTP_SECURE') ?: 'tls')), // tls | ssl | none
        'from' => getenv('JSA_MAIL_FROM') ?: (getenv('JSA_SMTP_FROM') ?: 'jm@jsaindia.com'),
        'from_name' => getenv('JSA_MAIL_FROM_NAME') ?: 'Japneet S & Associates',
        'outbox_dir' => __DIR__ . '/../data/outbox',
    ];
}

function mailer_send(string $to, string $subject, string $html, string $text = ''): array
{
    $cfg = mailer_config();

    $mode = $cfg['mode'];
    if ($mode === '') {
        if ($cfg['smtp_host'] !== '') $mode = 'smtp';
        elseif (function_exists('mail')) $mode = 'mail';
        else $mode = 'outbox';
    }

    if ($mode === 'smtp') {
        return smtp_send($cfg, $to, $subject, $html, $text);
    }

    if ($mode === 'mail') {
        return php_mail_send($cfg, $to, $subject, $html, $text);
    }

    return outbox_write($cfg, $to, $subject, $html, $text);
}

function mailer_build_message(string $fromEmail, string $fromName, string $to, string $subject, string $html, string $text = ''): string
{
    $boundary = 'b' . bin2hex(random_bytes(10));
    $fromHeader = $fromName !== '' ? sprintf('"%s" <%s>', addcslashes($fromName, '"'), $fromEmail) : $fromEmail;

    $headers = [];
    $headers[] = 'From: ' . $fromHeader;
    $headers[] = 'To: ' . $to;
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary=' . $boundary;
    $headers[] = 'Date: ' . gmdate('D, d M Y H:i:s') . ' GMT';

    $parts = [];
    $parts[] = '--' . $boundary;
    $parts[] = 'Content-Type: text/plain; charset=utf-8';
    $parts[] = 'Content-Transfer-Encoding: 8bit';
    $parts[] = '';
    $parts[] = $text !== '' ? $text : strip_tags($html);
    $parts[] = '';
    $parts[] = '--' . $boundary;
    $parts[] = 'Content-Type: text/html; charset=utf-8';
    $parts[] = 'Content-Transfer-Encoding: 8bit';
    $parts[] = '';
    $parts[] = $html;
    $parts[] = '';
    $parts[] = '--' . $boundary . '--';
    $parts[] = '';

    return implode("\r\n", array_merge($headers, ['', implode("\r\n", $parts)]));
}

function php_mail_send(array $cfg, string $to, string $subject, string $html, string $text = ''): array
{
    $from = (string)($cfg['from'] ?? 'jm@jsaindia.com');
    $fromName = (string)($cfg['from_name'] ?? 'Japneet S & Associates');
    $fromHeader = $fromName !== '' ? sprintf('"%s" <%s>', addcslashes($fromName, '"'), $from) : $from;

    $headers = [];
    $headers[] = 'From: ' . $fromHeader;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=utf-8';

    $ok = @mail($to, $subject, $html, implode("\r\n", $headers));
    return ['ok' => (bool)$ok, 'mode' => 'mail', 'error' => $ok ? null : 'mail() failed'];
}

function outbox_write(array $cfg, string $to, string $subject, string $html, string $text = ''): array
{
    $dir = (string)($cfg['outbox_dir'] ?? (__DIR__ . '/../data/outbox'));
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $from = (string)($cfg['from'] ?? 'jm@jsaindia.com');
    $fromName = (string)($cfg['from_name'] ?? 'Japneet S & Associates');

    $eml = mailer_build_message($from, $fromName, $to, $subject, $html, $text);
    $safeTo = preg_replace('/[^a-zA-Z0-9._@-]+/', '_', $to);
    $file = $dir . '/' . date('Ymd_His') . '_' . $safeTo . '_' . substr(sha1($subject), 0, 10) . '.eml';
    $ok = @file_put_contents($file, $eml) !== false;
    return ['ok' => $ok, 'mode' => 'outbox', 'path' => $file, 'error' => $ok ? null : 'Failed to write outbox file'];
}

function smtp_send(array $cfg, string $to, string $subject, string $html, string $text = ''): array
{
    $host = (string)($cfg['smtp_host'] ?? '');
    $port = (int)($cfg['smtp_port'] ?? 587);
    $user = (string)($cfg['smtp_user'] ?? '');
    $pass = (string)($cfg['smtp_pass'] ?? '');
    $secure = (string)($cfg['smtp_secure'] ?? 'tls');

    if ($host === '') {
        return ['ok' => false, 'mode' => 'smtp', 'error' => 'Missing SMTP host'];
    }

    $transport = ($secure === 'ssl') ? 'ssl' : 'tcp';
    $fp = @stream_socket_client("{$transport}://{$host}:{$port}", $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
    if (!$fp) {
        return ['ok' => false, 'mode' => 'smtp', 'error' => "Connect failed: {$errstr} ({$errno})"];
    }
    stream_set_timeout($fp, 20);

    $expect = function (array $codes) use ($fp): array {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 512);
            if ($line === false) break;
            $data .= $line;
            // multi-line ends when 4th char is space
            if (strlen($line) >= 4 && $line[3] === ' ') break;
        }
        $code = (int)substr(trim($data), 0, 3);
        $ok = in_array($code, $codes, true);
        return ['ok' => $ok, 'code' => $code, 'raw' => trim($data)];
    };

    $send = function (string $cmd, array $codes) use ($fp, $expect): array {
        fwrite($fp, $cmd . "\r\n");
        return $expect($codes);
    };

    $r = $expect([220]);
    if (!$r['ok']) {
        fclose($fp);
        return ['ok' => false, 'mode' => 'smtp', 'error' => 'Bad greeting: ' . $r['raw']];
    }

    $local = 'jsaindia.local';
    $r = $send("EHLO {$local}", [250]);
    if (!$r['ok']) {
        $r = $send("HELO {$local}", [250]);
        if (!$r['ok']) {
            fclose($fp);
            return ['ok' => false, 'mode' => 'smtp', 'error' => 'HELO/EHLO failed: ' . $r['raw']];
        }
    }

    if ($secure === 'tls') {
        $startTls = $send('STARTTLS', [220]);
        if ($startTls['ok']) {
            $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$cryptoOk) {
                fclose($fp);
                return ['ok' => false, 'mode' => 'smtp', 'error' => 'STARTTLS failed to enable crypto'];
            }
            $r = $send("EHLO {$local}", [250]);
            if (!$r['ok']) {
                fclose($fp);
                return ['ok' => false, 'mode' => 'smtp', 'error' => 'EHLO after STARTTLS failed: ' . $r['raw']];
            }
        }
    }

    if ($user !== '') {
        $r = $send('AUTH LOGIN', [334]);
        if (!$r['ok']) {
            fclose($fp);
            return ['ok' => false, 'mode' => 'smtp', 'error' => 'AUTH LOGIN failed: ' . $r['raw']];
        }
        $r = $send(base64_encode($user), [334]);
        if (!$r['ok']) {
            fclose($fp);
            return ['ok' => false, 'mode' => 'smtp', 'error' => 'SMTP username rejected: ' . $r['raw']];
        }
        $r = $send(base64_encode($pass), [235]);
        if (!$r['ok']) {
            fclose($fp);
            return ['ok' => false, 'mode' => 'smtp', 'error' => 'SMTP password rejected: ' . $r['raw']];
        }
    }

    $from = (string)($cfg['from'] ?? 'jm@jsaindia.com');
    $fromName = (string)($cfg['from_name'] ?? 'Japneet S & Associates');

    $r = $send('MAIL FROM:<' . $from . '>', [250]);
    if (!$r['ok']) {
        fclose($fp);
        return ['ok' => false, 'mode' => 'smtp', 'error' => 'MAIL FROM failed: ' . $r['raw']];
    }
    $r = $send('RCPT TO:<' . $to . '>', [250, 251]);
    if (!$r['ok']) {
        fclose($fp);
        return ['ok' => false, 'mode' => 'smtp', 'error' => 'RCPT TO failed: ' . $r['raw']];
    }
    $r = $send('DATA', [354]);
    if (!$r['ok']) {
        fclose($fp);
        return ['ok' => false, 'mode' => 'smtp', 'error' => 'DATA failed: ' . $r['raw']];
    }

    $msg = mailer_build_message($from, $fromName, $to, $subject, $html, $text);
    // Dot-stuff lines that start with a dot
    $msg = preg_replace('/\\r\\n\\./', "\r\n..", $msg);
    fwrite($fp, $msg . "\r\n.\r\n");
    $r = $expect([250]);
    $send('QUIT', [221, 250]);
    fclose($fp);
    if (!$r['ok']) {
        return ['ok' => false, 'mode' => 'smtp', 'error' => 'Message not accepted: ' . $r['raw']];
    }
    return ['ok' => true, 'mode' => 'smtp', 'error' => null];
}
