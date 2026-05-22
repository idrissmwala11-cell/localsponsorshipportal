<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sqlitePath = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';

if (!file_exists($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found at {$sqlitePath}\n");
    exit(1);
}

$env = [];
foreach (file($root . DIRECTORY_SEPARATOR . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $env[$key] = trim($value, "\"'");
}

$mysqlDsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $env['DB_HOST'] ?? '127.0.0.1',
    $env['DB_PORT'] ?? '3306',
    $env['DB_DATABASE'] ?? 'compassion_portal'
);

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mysql = new PDO($mysqlDsn, $env['DB_USERNAME'] ?? 'root', $env['DB_PASSWORD'] ?? '');
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = [
    'centers',
    'users',
    'otp_codes',
    'participants',
    'participant_sponsorships',
    'center_notifications',
    'center_notification_reads',
];

function sqliteColumns(PDO $sqlite, string $table): array
{
    $columns = [];
    $rows = $sqlite->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $columns[] = $row['name'];
    }

    return $columns;
}

function mysqlColumns(PDO $mysql, string $table): array
{
    $columns = [];
    $rows = $mysql->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $columns[] = $row['Field'];
    }

    return $columns;
}

$mysql->exec('SET FOREIGN_KEY_CHECKS=0');

foreach (array_reverse($tables) as $table) {
    try {
        $mysql->exec("TRUNCATE TABLE `{$table}`");
    } catch (Throwable $e) {
        // ignore tables that do not exist yet
    }
}

foreach ($tables as $table) {
    $sourceColumns = sqliteColumns($sqlite, $table);

    if (empty($sourceColumns)) {
        echo "Skipping {$table}: missing in SQLite\n";
        continue;
    }

    $targetColumns = mysqlColumns($mysql, $table);
    $columns = array_values(array_intersect($sourceColumns, $targetColumns));

    if (empty($columns)) {
        echo "Skipping {$table}: no matching columns\n";
        continue;
    }

    $selectSql = sprintf('SELECT %s FROM %s', implode(', ', array_map(fn ($column) => '"' . $column . '"', $columns)), $table);
    $rows = $sqlite->query($selectSql)->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "No rows in {$table}\n";
        continue;
    }

    $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
    $insertSql = sprintf(
        'INSERT INTO `%s` (%s) VALUES %s',
        $table,
        implode(', ', array_map(fn ($column) => "`{$column}`", $columns)),
        $placeholders
    );

    $statement = $mysql->prepare($insertSql);

    foreach ($rows as $row) {
        $statement->execute(array_map(fn ($column) => $row[$column], $columns));
    }

    echo "Imported {$table}: " . count($rows) . " rows\n";
}

$mysql->exec('SET FOREIGN_KEY_CHECKS=1');

echo "SQLite to MySQL import complete.\n";
