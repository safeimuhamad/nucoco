<?php

require_once dirname(__DIR__, 2) . '/handlers/phpmailer/src/PHPMailer.php';
require_once dirname(__DIR__, 2) . '/handlers/phpmailer/src/SMTP.php';
require_once dirname(__DIR__, 2) . '/handlers/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists('nucoco_send_mail')) {
    function nucoco_send_mail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $config = db_select_one("SELECT site_name, company_name, email, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_secure FROM web_config LIMIT 1") ?: [];

        $fromEmail = trim($config['smtp_user'] ?? '') ?: (trim($config['email'] ?? '') ?: 'info@nucoco.id');
        $fromName = trim($config['company_name'] ?? '') ?: (trim($config['site_name'] ?? '') ?: 'Nucoco');
        $smtpHost = trim($config['smtp_host'] ?? '') ?: 'mail.nucoco.id';
        $smtpPort = (int) (trim((string) ($config['smtp_port'] ?? '')) ?: 465);
        $smtpUser = trim($config['smtp_user'] ?? '') ?: 'info@nucoco.id';
        $smtpPass = trim($config['smtp_pass'] ?? '') ?: 'M7f@s4r21';
        $smtpSecure = trim($config['smtp_secure'] ?? '') ?: PHPMailer::ENCRYPTION_SMTPS;

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = $smtpPort;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody !== '' ? $textBody : strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        return $mail->send();
    }
}
