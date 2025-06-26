<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'u856042760_OUfnl';
$username = 'u856042760_4EtD4';
$password = 'Le@070210'; // Substituir pela senha correta do banco

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
