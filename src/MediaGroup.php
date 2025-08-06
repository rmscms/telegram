<?php

namespace RMS\Telegram;

use Illuminate\Support\Facades\Storage;
use Telegram\Bot\FileUpload\InputFile;

class MediaGroup
{
    protected $media = [];

    public static function make(): self
    {
        return new self();
    }

    public function addPhoto(string $path, ?string $caption = null): self
    {
        $this->media[] = [
            'type' => 'photo',
            'media' => InputFile::create(Storage::disk('public')->path($path), basename($path)),
            'caption' => $caption,
        ];
        return $this;
    }

    public function addVideo(string $path, ?string $caption = null): self
    {
        $this->media[] = [
            'type' => 'video',
            'media' => InputFile::create(Storage::disk('public')->path($path), basename($path)),
            'caption' => $caption,
        ];
        return $this;
    }

    public function get(): array
    {
        return $this->media;
    }
}
