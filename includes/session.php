<?php
declare(strict_types=1);

<<<<<<< HEAD
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
=======
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Ends inactive sessions and redirects to login page.
 */
function enforce_session_timeout(string $redirect_to, int $timeout_seconds = 1800): void
{
    $last_activity = (int) ($_SESSION['last_activity'] ?? 0);
    $current_time = time();

    if ($last_activity > 0 && ($current_time - $last_activity) > $timeout_seconds) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                $current_time - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
        header('Location: ' . $redirect_to . '?session=expired');
        exit();
    }

    $_SESSION['last_activity'] = $current_time;
>>>>>>> 1f8c8ca (Updated instructor Dashboard)
}
