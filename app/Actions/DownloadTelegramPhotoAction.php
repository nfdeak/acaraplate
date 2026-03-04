<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\DownloadsTelegramPhoto;
use DefStudio\Telegraph\DTO\Photo;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Files\Base64Image;
use RuntimeException;

final readonly class DownloadTelegramPhotoAction implements DownloadsTelegramPhoto
{
    public function handle(TelegraphBot $bot, Photo $photo): Base64Image
    {
        $telegraph = $bot->getFileInfo($photo->id());
        $response = $telegraph->send();

        if ($response->telegraphError()) {
            throw new RuntimeException('Failed to retrieve file info for Telegram photo: '.$photo->id());
        }

        /** @var string $filePath */
        $filePath = $response->json('result.file_path');

        $fileResponse = Http::timeout(30)->get($telegraph->getFilesUrl().'/'.$filePath);

        if ($fileResponse->failed()) {
            throw new RuntimeException('Failed to download Telegram photo: '.$photo->id());
        }

        return new Base64Image(
            base64_encode($fileResponse->body()),
            $this->resolveMimeType($filePath, $fileResponse->header('Content-Type')),
        );
    }

    private function resolveMimeType(string $filePath, ?string $contentType): string
    {
        if ($contentType !== null && str_starts_with($contentType, 'image/')) {
            return $contentType;
        }

        return match (pathinfo($filePath, PATHINFO_EXTENSION)) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
