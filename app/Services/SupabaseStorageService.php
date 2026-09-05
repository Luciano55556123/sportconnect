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
        $curl = curl_init();
        if ($curl === false) {
            throw new RuntimeException('Falha ao iniciar cURL para upload no Supabase Storage.');
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $contents,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->key,
                'apikey: ' . $this->key,
                'Content-Type: ' . $contentType,
                'x-upsert: false',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
        ]);

        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException(
                'Falha ao enviar imagem ao Supabase Storage. cURL errno ' . $errno
                . ': ' . $error
                . '. URL: ' . $endpoint
                . '. HTTP ' . $status
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException(
                'Falha ao enviar imagem ao Supabase Storage. HTTP ' . $status
                . '. URL: ' . $endpoint
                . '. Resposta: ' . $this->summarizeResponse($response)
            );
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

    private function summarizeResponse(string $response): string
    {
        $response = trim(preg_replace('/\s+/', ' ', $response) ?? '');
        if ($response === '') {
            return '[sem corpo]';
        }

        return mb_substr($response, 0, 500);
    }
}
