<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Actions\DownloadTelegramPhotoAction;
use DefStudio\Telegraph\DTO\Photo;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Container\Attributes\Bind;
use Laravel\Ai\Files\Base64Image;

#[Bind(DownloadTelegramPhotoAction::class)]
interface DownloadsTelegramPhoto
{
    public function handle(TelegraphBot $bot, Photo $photo): Base64Image;
}
