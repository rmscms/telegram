<?php

namespace RMS\Telegram;

use Illuminate\Support\Facades\Storage;

class MediaGroup
{
    protected $media = [];

    public static function make(): self
    {
        return new self();
    }

    public function addPhoto(string $path, ?string $caption = null): self
    {
        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath)) {
            throw new \Exception("Photo file does not exist: {$fullPath}");
        }
        $this->media[] = [
            'type' => 'photo',
            'media' => $fullPath,
            'caption' => $caption,
        ];
        return $this;
    }

    public function addVideo(string $path, ?string $caption = null): self
    {
        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath)) {
            throw new \Exception("Video file does not exist: {$fullPath}");
        }
        $this->media[] = [
            'type' => 'video',
            'media' => $fullPath,
            'caption' => $caption,
        ];
        return $this;
    }

    public function get(): array
    {
        return $this->media;
    }
}
