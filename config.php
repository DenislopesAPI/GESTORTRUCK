<?php
// Carrega variaveis de ambiente de um arquivo .env, se existir
$envFile = __DIR__ . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = array_map('trim', explode('=', $line, 2));
            putenv("{$name}={$value}");
        }
    }
}

// Configuracoes do banco de dados
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: '';
$db_user = getenv('DB_USER') ?: '';
$db_pass = getenv('DB_PASS') ?: '';

// Configuracoes SMTP
$smtp_host = getenv('SMTP_HOST') ?: 'localhost';
$smtp_user = getenv('SMTP_USER') ?: '';
$smtp_pass = getenv('SMTP_PASS') ?: '';
$smtp_port = getenv('SMTP_PORT') ?: 465;
$smtp_secure = getenv('SMTP_SECURE') ?: 'ssl';
$smtp_from_email = getenv('SMTP_FROM_EMAIL') ?: $smtp_user;
$smtp_from_name = getenv('SMTP_FROM_NAME') ?: 'Gestor Truck';
?>
