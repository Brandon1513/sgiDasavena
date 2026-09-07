<?php

namespace App\Mail\Transport;

use App\Services\Graph\GraphAccessTokenProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Transporte de correo que envía mensajes a través de Microsoft Graph
 * (POST /users/{mailFrom}/sendMail) usando una app de Entra ID con
 * permisos de tipo Application (Mail.Send).
 */
class GraphApiTransport extends AbstractTransport
{
    public function __construct(
        private readonly Client $client,
        private readonly GraphAccessTokenProvider $tokenProvider,
        private readonly string $mailFrom,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('GraphApiTransport solo soporta mensajes Email de Symfony Mime.');
        }

        try {
            $response = $this->client->post(
                "https://graph.microsoft.com/v1.0/users/{$this->mailFrom}/sendMail",
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->tokenProvider->getToken(),
                    ],
                    'json' => [
                        'message' => $this->buildGraphMessage($email),
                        'saveToSentItems' => false,
                    ],
                    'http_errors' => false,
                ]
            );
        } catch (GuzzleException $e) {
            throw new TransportException(
                'Error de red al enviar el correo mediante Microsoft Graph.', 0, $this->sanitize($e)
            );
        }

        $status = $response->getStatusCode();

        if ($status >= 400) {
            throw new TransportException($this->describeError($status, (string) $response->getBody()));
        }
    }

    public function __toString(): string
    {
        return 'graph';
    }

    private function buildGraphMessage(Email $email): array
    {
        $message = [
            'subject' => (string) $email->getSubject(),
            'body' => $this->buildBody($email),
            'toRecipients' => $this->mapAddresses($email->getTo()),
        ];

        if ($cc = $email->getCc()) {
            $message['ccRecipients'] = $this->mapAddresses($cc);
        }

        if ($bcc = $email->getBcc()) {
            $message['bccRecipients'] = $this->mapAddresses($bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $message['replyTo'] = $this->mapAddresses($replyTo);
        }

        if ($attachments = $this->buildAttachments($email)) {
            $message['attachments'] = $attachments;
        }

        return $message;
    }

    private function buildBody(Email $email): array
    {
        if ($html = $email->getHtmlBody()) {
            return ['contentType' => 'HTML', 'content' => $html];
        }

        return ['contentType' => 'Text', 'content' => (string) $email->getTextBody()];
    }

    /**
     * @param  Address[]  $addresses
     */
    private function mapAddresses(array $addresses): array
    {
        return array_map(fn (Address $address) => [
            'emailAddress' => array_filter([
                'address' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ]),
        ], $addresses);
    }

    private function buildAttachments(Email $email): array
    {
        return array_map(fn (DataPart $attachment) => [
            '@odata.type' => '#microsoft.graph.fileAttachment',
            'name' => $attachment->getFilename() ?? 'attachment',
            'contentType' => $attachment->getContentType(),
            'contentBytes' => base64_encode($attachment->getBody()),
        ], $email->getAttachments());
    }

    /**
     * Construye un mensaje de error legible a partir del body de error de Graph,
     * sin incluir el request original (headers/token) en la excepción.
     */
    private function describeError(int $status, string $body): string
    {
        $decoded = json_decode($body, true);
        $code = $decoded['error']['code'] ?? null;
        $detail = $decoded['error']['message'] ?? null;

        $reason = $code ? "{$code}: {$detail}" : "HTTP {$status}";

        return "Microsoft Graph rechazó el envío del correo ({$reason}).";
    }

    /**
     * Evita propagar la excepción de Guzzle tal cual como "previous": puede incluir
     * el request completo (con el header Authorization) en su representación en texto.
     */
    private function sanitize(GuzzleException $e): \Throwable
    {
        $status = $e instanceof RequestException && $e->hasResponse()
            ? $e->getResponse()->getStatusCode()
            : null;

        return new \RuntimeException($status ? "HTTP {$status}" : $e::class);
    }
}
