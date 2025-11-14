<?php
$envPath = __DIR__ . '/../.env';

if (!file_exists($envPath)) {
    die('接続失敗: .env ファイルが見つかりません');
}

// .envファイルを手動でパース
$env = [];
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // コメント行をスキップ
    if (strpos(trim($line), '#') === 0) {
        continue;
    }
    // KEY=VALUE形式をパース
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // クォートを削除
        $value = trim($value, '"\'');
        $env[$key] = $value;
        $_ENV[$key] = $value;
        putenv(sprintf('%s=%s', $key, $value));
    }
}

$servername = $env['DB_HOST'] ?? 'localhost';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';
$dbname = $env['DB_DATABASE'] ?? '';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $servername, $dbname),
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET NAMES utf8mb4');
} catch (PDOException $e) {
    die('接続失敗: ' . $e->getMessage());
}
?>
