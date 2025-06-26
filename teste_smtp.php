<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_user;
    $mail->Password   = $smtp_pass;
    $mail->SMTPSecure = $smtp_secure; // ou 'tls' para porta 587
    $mail->Port       = $smtp_port; // ou 587 se usar tls

    // Remetente
    $mail->setFrom($smtp_from_email, 'Gestor Truck Teste');
    // Destinatário
    $mail->addAddress($smtp_from_email, 'Gestor Truck');

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
