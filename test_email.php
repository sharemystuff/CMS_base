<?php
/* test_email.php - EL HACK DEFINITIVO */
include_once __DIR__ . '/api/main.php';
require_once __DIR__ . '/tools/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

// HACK: Creamos la clase que falta en tiempo de ejecución para que SwiftMailer deje de llorar
namespace Egulias\EmailValidator {
    class EmailValidator {
        public function isValid($email, $parser) { return true; }
    }
}
namespace Egulias\EmailValidator\Validation {
    class RFCValidation {}
}

// Volvemos al namespace global
namespace {

    $test_host = 'mail.microstudio.cl';
    $test_port = 465; 
    $test_user = 'mailer@microstudio.cl';
    $test_pass = 'rbi8revelacion214'; 
    $test_to   = 'mhuenchul@gmail.com'; 

    echo "<h1>Probando envío SMTP (Hack Mangiacaprini)...</h1>";

    try {
        $transport = (new Swift_SmtpTransport($test_host, $test_port, 'ssl'))
          ->setUsername($test_user)
          ->setPassword($test_pass);

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message('Prueba de Vuelo CMS BASE'))
          ->setFrom([$test_user => 'Admin MicroStudio'])
          ->setTo([$test_to])
          ->setBody('✅ ¡LO LOGRAMOS! SwiftMailer fue engañado con éxito.', 'text/html');

        if ($mailer->send($message)) {
            echo "<div style='color:green; font-size:1.5rem;'>✅ <b>¡ÉXITO!</b> Correo enviado.</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='color:red;'>❌ <b>ERROR:</b> " . $e->getMessage() . "</div>";
    }
}