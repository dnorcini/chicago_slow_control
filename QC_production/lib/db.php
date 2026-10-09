<?php
// lib/db.php
// The database connection: one PDO connection, prepared statements only.
//   q($sql, $args)    run a statement, returns the PDOStatement
//   one($sql, $args)  the first row as an array, or null
//   all($sql, $args)  every row
// Example:  $die = one('SELECT * FROM die WHERE id = ?', [$id]);
//
// Credentials come from env vars DB_HOST / DB_USER / DB_PASSWORD / DB_NAME (set
// by docker-compose), or from db_config.php next to the pages (git-ignored;
// copy db_config.example.php to create it).

function db_settings(): array {
    $file = __DIR__ . '/../db_config.php';
    $cfg = is_file($file) ? require $file : [];
    $s = [
        'host'     => getenv('DB_HOST')     ?: ($cfg['host'] ?? 'localhost'),
        'user'     => getenv('DB_USER')     ?: ($cfg['user'] ?? ''),
        'password' => getenv('DB_PASSWORD') ?: ($cfg['password'] ?? ''),
        'database' => getenv('DB_NAME')     ?: ($cfg['database'] ?? 'ccdqc'),
    ];
    if ($s['user'] === '')
        throw new RuntimeException('No database credentials: copy db_config.example.php to db_config.php and fill it in.');
    return $s;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $s = db_settings();
        $pdo = new PDO("mysql:host={$s['host']};dbname={$s['database']};charset=utf8mb4", $s['user'], $s['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements; numbers come back as numbers
        ]);
    }
    return $pdo;
}

function q(string $sql, array $args = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st;
}

function one(string $sql, array $args = []): ?array {
    $row = q($sql, $args)->fetch();
    return $row === false ? null : $row;
}

function all(string $sql, array $args = []): array {
    return q($sql, $args)->fetchAll();
}
