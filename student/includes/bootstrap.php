<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/session.php';
enforce_session_timeout('../Login Page 2.0/index.html');

include __DIR__ . '/../../includes/db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../Login Page 2.0/index.html');
    exit();
}

/**
 * Execute query and return mysqli_result|false without throwing runtime notices.
 */
function run_query(mysqli $conn, string $sql)
{
    $result = $conn->query($sql);
    if ($result === false) {
        error_log('Student dashboard query failed: ' . $conn->error . ' | SQL: ' . $sql);
    }

    return $result;
}

/**
 * Fetch first row from a query result, with fallback values.
 *
 * @param array<string, mixed> $fallback
 * @return array<string, mixed>
 */
function first_row_or(array $fallback, $query_result): array
{
    if ($query_result instanceof mysqli_result) {
        $row = $query_result->fetch_assoc();
        if (is_array($row)) {
            return $row;
        }
    }

    return $fallback;
}
