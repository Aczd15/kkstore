<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function currentPath(): string
{
    return $_GET['page'] ?? 'home';
}

function createCaptchaChallenge(string $context): array
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789@#';
    $code = '';
    for ($i = 0; $i < 7; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }

    $a = random_int(11, 45);
    $b = random_int(2, 9);
    $ops = ['+', '-', '*'];
    $op = $ops[random_int(0, 2)];
    $math = match ($op) {
        '+' => $a + $b,
        '-' => $a - $b,
        '*' => $a * $b,
    };

    $_SESSION['captcha'][$context] = [
        'code' => $code,
        'math' => (string)$math,
        'question' => "$a $op $b",
    ];

    return $_SESSION['captcha'][$context];
}

function captchaData(string $context): array
{
    if (!isset($_SESSION['captcha'][$context])) {
        return createCaptchaChallenge($context);
    }

    return $_SESSION['captcha'][$context];
}

function verifyCaptcha(string $context, string $code, string $math): bool
{
    $expected = $_SESSION['captcha'][$context] ?? null;
    unset($_SESSION['captcha'][$context]);

    if (!$expected) {
        return false;
    }

    return mb_strtoupper(trim($code)) === $expected['code'] && trim($math) === $expected['math'];
}

function saveUploadedImage(array $file, string $folder): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }

    if (($file['size'] ?? 0) > 4 * 1024 * 1024) {
        return null;
    }

    $ext = $allowed[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $relative = '/uploads/' . $folder . '/' . $filename;
    $absolute = __DIR__ . '/..' . $relative;

    if (!move_uploaded_file($file['tmp_name'], $absolute)) {
        return null;
    }

    return $relative;
}
