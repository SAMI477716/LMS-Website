<?php
declare(strict_types=1);

const SESSION_TIMEOUT_SECONDS = 1800; // 30 minutes

function init_lms_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.gc_maxlifetime', (string) SESSION_TIMEOUT_SECONDS);
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT_SECONDS,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function touch_session_activity(): void
{
    $_SESSION['last_activity'] = time();
}

function enforce_session_timeout(string $redirect_path): void
{
    init_lms_session();

    $now = time();
    $last_activity = (int) ($_SESSION['last_activity'] ?? $now);
    if (($now - $last_activity) > SESSION_TIMEOUT_SECONDS) {
        session_unset();
        session_destroy();
        header('Location: ' . $redirect_path . '?session=expired');
        exit();
    }

    touch_session_activity();
}
