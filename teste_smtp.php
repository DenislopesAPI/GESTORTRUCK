<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'user_invite@gestortruck.com.br';
    $mail->Password   = 'Le@070210';
    $mail->SMTPSecure = 'ssl'; // ou 'tls' para porta 587
    $mail->Port       = 465; // ou 587 se usar tls

    // Remetente
    $mail->setFrom('user_invite@gestortruck.com.br', 'Gestor Truck Teste');
    // Destinatário
    $mail->addAddress('user_invite@gestortruck.com.br', 'Gestor Truck');

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Teste SMTP Gestor Truck';
    $mail->Body    = '<h1>Funcionou!</h1><p>Este é um teste de envio via SMTP Hostinger.</p>';

    $mail->send();
    echo '✅ E-mail enviado com sucesso.';
} catch (Exception $e) {
    echo "❌ Erro no envio. <br><strong>Motivo:</strong> {$mail->ErrorInfo}";
}
?>
