<?php

namespace App\Services;

class RegistrationEmailService
{
    public function __construct(private MailService $mail = new MailService())
    {
    }

    public function newRegistrationToOrganizer(array $registration): void
    {
        $to = (string) ($registration['email_contato'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = 'Nova inscricao - ' . $this->headerText($registration['championship_name'] ?? '');
        $this->mail->send($to, $subject, $this->layout([
            'Uma nova inscricao foi realizada atraves da plataforma SportConnect.',
            'Atleta: ' . ($registration['name'] ?? ''),
            'E-mail: ' . ($registration['email'] ?? ''),
            'Telefone/WhatsApp: ' . ($registration['phone'] ?? ''),
            'Campeonato: ' . ($registration['championship_name'] ?? ''),
            'Categoria: ' . (($registration['category'] ?? '') ?: ($registration['championship_category'] ?? '')),
            'Equipe: ' . ($registration['team'] ?? ''),
            'Data da inscricao: ' . ($registration['created_at'] ?? ''),
            'Valor: R$ ' . number_format((float) ($registration['payment_amount'] ?? $registration['registration_fee'] ?? 0), 2, ',', '.'),
            'Status do pagamento: ' . $this->statusLabel(($registration['payment_status'] ?? '') ?: 'nao se aplica'),
            'Acesse o painel do organizador para visualizar e gerenciar a inscricao.',
        ], url('/organizador/inscricoes'), 'Visualizar inscricao'));
    }

    public function confirmationToAthlete(array $registration): void
    {
        $to = (string) ($registration['email'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $paid = !empty($registration['requires_payment']) && (float) ($registration['registration_fee'] ?? 0) > 0;
        $lines = [
            'Ola, ' . ($registration['name'] ?? '') . '.',
            'Sua inscricao no campeonato ' . ($registration['championship_name'] ?? '') . ' foi recebida.',
        ];

        if ($paid) {
            $lines[] = 'Para continuar o processo, realize o pagamento da taxa de inscricao.';
            $lines[] = 'Valor: R$ ' . number_format((float) ($registration['registration_fee'] ?? 0), 2, ',', '.');
            $lines[] = 'Chave PIX: ' . ($registration['pix_key'] ?? '');
            $lines[] = 'Titular: ' . ($registration['pix_holder_name'] ?? '');
            $lines[] = 'Apos o pagamento, envie o comprovante atraves da plataforma SportConnect.';
            $lines[] = 'Status atual: Aguardando pagamento.';
        } else {
            $lines[] = 'Voce pode acompanhar o status da inscricao atraves da plataforma SportConnect.';
            $lines[] = 'Status atual: Aguardando aprovacao.';
        }

        $subject = 'Inscricao recebida - ' . $this->headerText($registration['championship_name'] ?? '');
        $this->mail->send($to, $subject, $this->layout($lines, url('/atleta/historico'), 'Acompanhar inscricao'));
    }

    public function receiptToOrganizer(array $registration): void
    {
        $to = (string) ($registration['email_contato'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = 'Comprovante de pagamento recebido - ' . $this->headerText($registration['championship_name'] ?? '');
        $this->mail->send($to, $subject, $this->layout([
            'Um comprovante de pagamento foi enviado e esta aguardando analise.',
            'Campeonato: ' . ($registration['championship_name'] ?? ''),
            'Atleta: ' . ($registration['name'] ?? ''),
            'Valor: R$ ' . number_format((float) ($registration['payment_amount'] ?? 0), 2, ',', '.'),
            'Status do pagamento: ' . $this->statusLabel($registration['payment_status'] ?? 'under_review'),
        ], url('/organizador/inscricoes'), 'Analisar pagamento'));
    }

    public function paymentReviewedToAthlete(array $registration, bool $approved): void
    {
        $to = (string) ($registration['email'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = $approved ? 'Pagamento aprovado - Inscricao confirmada' : 'Nao foi possivel confirmar o pagamento';
        $lines = $approved
            ? ['Seu pagamento foi aprovado.', 'Sua inscricao esta confirmada em ' . ($registration['championship_name'] ?? '') . '.']
            : ['Nao foi possivel confirmar o pagamento enviado.', 'Acesse a plataforma SportConnect para verificar o status e as observacoes do organizador.'];

        if (!empty($registration['review_notes'])) {
            $lines[] = 'Observacao: ' . $registration['review_notes'];
        }

        $this->mail->send($to, $subject, $this->layout($lines, url('/atleta/historico'), 'Ver inscricao'));
    }

    private function layout(array $lines, string $buttonUrl = '', string $buttonText = ''): string
    {
        $body = '';
        foreach ($lines as $line) {
            if ((string) $line === '') {
                continue;
            }
            $body .= '<p>' . $this->html((string) $line) . '</p>';
        }

        if ($buttonUrl !== '') {
            $body .= '<p><a href="' . $this->html($buttonUrl) . '" style="display:inline-block;padding:10px 14px;background:#1267d8;color:#ffffff;text-decoration:none;border-radius:6px;">' . $this->html($buttonText) . '</a></p>';
        }

        return '<div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.5;color:#172033;">' . $body . '</div>';
    }

    private function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function headerText(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    private function statusLabel(string $status): string
    {
        return [
            'awaiting_receipt' => 'Aguardando pagamento',
            'under_review' => 'Pagamento em analise',
            'paid' => 'Pagamento aprovado',
            'rejected' => 'Pagamento rejeitado',
            'aguardando_pagamento' => 'Aguardando pagamento',
            'pagamento_em_analise' => 'Pagamento em analise',
            'aprovado' => 'Pagamento aprovado',
            'rejeitado' => 'Pagamento rejeitado',
        ][$status] ?? $status;
    }
}
