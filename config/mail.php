<?php

/**
 * Función para enviar email de bienvenida
 * Requiere: mail() habilitado en el servidor o librería PHPMailer/SwiftMailer
 *
 * Para producción, instala PHPMailer o SwiftMailer:
 * composer require phpmailer/phpmailer
 */

function sendWelcomeEmail($email, $fullname, $password) {
    // Implementar aquí la lógica de envío de email
    // Por ahora retorna false para no bloquear el registro
    
    // Opción 1: Usar mail() nativo (requiere que el servidor lo permita)
    // $subject = 'Bienvenido a 40 Figuritas';
    // $message = "Hola $fullname,\n\nTu usuario ha sido creado.\nEmail: $email\nContraseña: $password";
    // $headers = "From: " . MAIL_FROM;
    // return mail($email, $subject, $message, $headers);
    
    // Opción 2: Usar SMTP (requiere PHPMailer)
    // Ver: https://github.com/PHPMailer/PHPMailer
    
    return true;
}

?>
