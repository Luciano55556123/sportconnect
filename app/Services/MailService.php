<?php

namespace App\Services;

class MailService
{
    public function send(string $to, string $subject, string $html, string $text = ''): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->log('Destinatario invalido para e-mail.');
            return false;
        }

        try {
            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendWithPhpMailer($to, $subject, $html, $text);
            }

            return $this->sendWithNativeMail($to, $subject, $html);
        } catch (\Throwable $exception) {
            $this->log($exception->getMessage());
            return false;
        }
    }

    private function sendWithPhpMailer(string $to, string $subject, string $html, string $text): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) getenv('MAIL_HOST');
        $mail->Port = (int) (getenv('MAIL_PORT') ?: 587);
        $mail->SMTPAuth = getenv('MAIL_USERNAME') !== '';
        $mail->Username = (string) getenv('MAIL_USERNAME');
        $mail->Password = (string) getenv('MAIL_PASSWORD');

        $encryption = strtolower((string) getenv('MAIL_ENCRYPTION'));
        if (in_array($encryption, ['tls', 'ssl'], true)) {
            $mail->SMTPSecure = $encryption;
        }

        $fromAddress = (string) (getenv('MAIL_FROM_ADDRESS') ?: getenv('MAIL_USERNAME'));
        $fromName = (string) (getenv('MAIL_FROM_NAME') ?: 'SportConnect');
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $this->log('Remetente invalido para e-mail.');
            return false;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromAddress, $this->sanitizeHeader($fromName));
        $mail->addAddress($to);
        $mail->Subject = $this->sanitizeHeader($subject);
        $mail->isHTML(true);
        $mail->Body = $html;
        $mail->AltBody = $text !== '' ? $text : strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
        $mail->send();

        return true;
    }

    private function sendWithNativeMail(string $to, string $subject, string $html): bool
    {
        $fromAddress = (string) (getenv('MAIL_FROM_ADDRESS') ?: '');
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $this->log('PHPMailer nao instalado e MAIL_FROM_ADDRESS invalido.');
            return false;
        }

        $fromName = $this->sanitizeHeader((string) (getenv('MAIL_FROM_NAME') ?: 'SportConnect'));
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromAddress . '>',
        ];

        return mail($to, $this->sanitizeHeader($subject), $html, implode("\r\n", $headers));
    }

    private function sanitizeHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    private function log(string $message): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        error_log('[' . date('Y-m-d H:i:s') . '] MailService: ' . $message . PHP_EOL, 3, $dir . '/mail.log');
    }
}
