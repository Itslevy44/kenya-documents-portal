<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TelegramService
{
    protected string $botToken;
    protected string $channelId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->botToken  = config('services.telegram.bot_token', '');
        $this->channelId = config('services.telegram.channel_id', '');
        $this->baseUrl   = 'https://api.telegram.org/bot' . $this->botToken;
    }

    /**
     * Upload a file to Telegram and return the file_id
     *
     * @param string $localPath  Absolute filesystem path to the file
     * @param string $caption    Caption for the document
     * @return string Telegram file_id
     * @throws \RuntimeException on failure
     */
    public function uploadFile(string $localPath, string $caption): string
    {
        if (!$this->botToken || !$this->channelId) {
            throw new \RuntimeException('Telegram credentials not configured.');
        }

        if (!file_exists($localPath)) {
            throw new \RuntimeException('File not found: ' . $localPath);
        }

        $response = Http::attach(
            'document',
            file_get_contents($localPath),
            basename($localPath)
        )->post($this->baseUrl . '/sendDocument', [
            'chat_id' => $this->channelId,
            'caption' => mb_substr($caption, 0, 1024), // Telegram caption limit
        ]);

        $data = $response->json();

        Log::info('Telegram upload', [
            'file' => basename($localPath),
            'ok'   => $data['ok'] ?? false,
        ]);

        if (!($data['ok'] ?? false)) {
            throw new \RuntimeException(
                'Telegram upload failed: ' . ($data['description'] ?? 'Unknown error')
            );
        }

        $fileId = $data['result']['document']['file_id'] ?? null;

        if (!$fileId) {
            throw new \RuntimeException('Telegram did not return a file_id.');
        }

        return $fileId;
    }

    /**
     * Stream a file from Telegram to the browser
     *
     * @param string $fileId  Telegram file_id
     * @return StreamedResponse
     * @throws \RuntimeException on failure
     */
    public function streamFile(string $fileId): StreamedResponse
    {
        if (!$this->botToken) {
            throw new \RuntimeException('Telegram credentials not configured.');
        }

        // Get file info from Telegram
        $infoResponse = Http::get($this->baseUrl . '/getFile', [
            'file_id' => $fileId,
        ]);

        $info = $infoResponse->json();

        if (!($info['ok'] ?? false)) {
            throw new \RuntimeException(
                'Telegram getFile failed: ' . ($info['description'] ?? 'Unknown error')
            );
        }

        $filePath   = $info['result']['file_path'] ?? null;
        $fileSize   = $info['result']['file_size'] ?? null;

        if (!$filePath) {
            throw new \RuntimeException('No file_path returned from Telegram.');
        }

        $downloadUrl = 'https://api.telegram.org/file/bot' . $this->botToken . '/' . $filePath;

        // Determine content-type from extension
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'txt'  => 'text/plain',
            'zip'  => 'application/zip',
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        $fileName = basename($filePath);

        return new StreamedResponse(function () use ($downloadUrl) {
            $stream = Http::withOptions(['stream' => true])->get($downloadUrl)->toPsrResponse()->getBody();
            while (!$stream->eof()) {
                echo $stream->read(8192);
                flush();
            }
        }, 200, [
            'Content-Type'        => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => $fileSize ?? '',
        ]);
    }
}
