<?php

namespace App\Services;

use RuntimeException;

class SupabaseStorageService
{
    private string $url;
    private string $key;
    private string $bucket;

    public function __construct()
    {
        $this->url = rtrim((string) getenv('SUPABASE_URL'), '/');
        $this->key = (string) getenv('SUPABASE_SERVICE_ROLE_KEY');
        $this->bucket = (string) (getenv('SUPABASE_STORAGE_BUCKET') ?: 'championship-images');
    }

    public function uploadPublicObject(string $sourcePath, string $objectPath, string $contentType): string
    {
        if ($this->url === '' || $this->key === '') {
            throw new RuntimeException('Supabase Storage nao configurado.');
        }

        if (!is_file($sourcePath)) {
            throw new RuntimeException('Arquivo de upload nao encontrado.');
        }

        $contents = file_get_contents($sourcePath);
        if ($contents === false) {
            throw new RuntimeException('Nao foi possivel ler o arquivo de upload.');
        }

        $endpoint = $this->url . '/storage/v1/object/' . rawurlencode($this->bucket) . '/' . $this->encodeObjectPath($objectPath);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                'Authorization: Bearer ' . $this->key,
                'apikey: ' . $this->key,
                'Content-Type: ' . $contentType,
                'x-upsert: false',
                ]),
                'content' => $contents,
                'ignore_errors' => true,
            ],
        ]);

        $response = file_get_contents($endpoint, false, $context);
        $status = $this->responseStatus($http_response_header ?? []);
        if ($response === false || $status < 200 || $status >= 300) {
            throw new RuntimeException('Falha ao enviar imagem ao Supabase Storage. HTTP ' . $status);
        }

        return $this->publicUrl($objectPath);
    }

    private function publicUrl(string $objectPath): string
    {
        return $this->url . '/storage/v1/object/public/' . rawurlencode($this->bucket) . '/' . $this->encodeObjectPath($objectPath);
    }

    private function encodeObjectPath(string $objectPath): string
    {
        return implode('/', array_map('rawurlencode', explode('/', ltrim($objectPath, '/'))));
    }

    private function responseStatus(array $headers): int
    {
        $statusLine = $headers[0] ?? '';
        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}