<?php
//Chargement de PHPMailer (installé via Composer) et des variables d'environnement

require_once __DIR__ . '/../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

\Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../config')->safeLoad();

function EnvoieMail($mail, $mailToSend, $Content)
{
    try {
        // Identifiants lus depuis config/.env
        $monEmail = $_ENV['MAIL_USERNAME'];
        $monMotDePasseApp = $_ENV['MAIL_PASSWORD'];

        //Server settings
        $mail->SMTPDebug = 0;                                       // 0 en production, 2 pour le debug
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $monEmail;
        $mail->Password   = $monMotDePasseApp;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Recipients
        // IMPORTANT : Le setFrom DOIT être votre adresse Gmail, sinon blocage pour usurpation
        $mail->setFrom($monEmail, 'SAE');
        $mail->addAddress($mailToSend, 'User');
        $mail->addReplyTo($monEmail, 'SAE');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Validation envoie message automatique';
        $mail->Body    = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Validation de Compte</title>
            <style>
                body { background-color: #000; color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 0; }
                .container { background-color: rgba(0, 0, 0, 0.7); margin: 0 auto; padding: 40px; max-width: 600px; text-align: center; border-radius: 10px; border: 1px solid #333; }
                h1 { font-size: 24px; color: #fff; }
                p { font-size: 18px; color: #ccc; }
                .validation-code { background-color: rgba(0, 0, 0, 0.9); padding: 20px; font-size: 32px; color: #00bfff; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px; border: 2px solid #00bfff; }
                .footer { margin-top: 30px; font-size: 14px; color: #888; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1> Mail envoyé avec succès :</h1>
                <p> Abonnez-vous au : </p>
                <div class="validation-code">' . htmlspecialchars($Content) . '</div>
                <div class="footer">Abonnez-vous à la chaîne du Code Redempteur</div>
            </div>
        </body>
        </html>';

        $mail->AltBody = 'Votre message : ' . $Content;

        $mail->send();
        return true; // Retourne "vrai" si le mail est parti

    } catch (Exception $e) {
        return $mail->ErrorInfo; // Retourne l'erreur exacte en cas d'échec
    }
}