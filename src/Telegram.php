<?php

namespace RMS\Telegram;

use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;

class Telegram
{
    protected $telegram;
    protected $chatId;
    protected $message;
    protected $photo;
    protected $document;
    protected $mediaGroup;
    protected $parseMode = 'HTML';
    protected $replyMarkup;
    protected $messageId;
    protected $disableNotification = false;

    public function __construct()
    {
        $defaultBot = config('telegram.default', 'mybot');
        $this->telegram = new Api(config("telegram.bots.{$defaultBot}.token"));
    }

    public function setApi($api): self
    {
        $this->telegram = $api;
        return $this;
    }

    public function to(string $chatId): self
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function photo(string $path): self
    {
        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath)) {
            throw new \Exception("Photo file does not exist: {$fullPath}");
        }
        $this->photo = InputFile::create($fullPath, basename($path));
        return $this;
    }

    public function document(string $path): self
    {
        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath)) {
            throw new \Exception("Document file does not exist: {$fullPath}");
        }
        $this->document = InputFile::create($fullPath, basename($path));
        return $this;
    }

    public function mediaGroup(MediaGroup $mediaGroup): self
    {
        $this->mediaGroup = $mediaGroup->get();
        return $this;
    }

    public function withHtml(): self
    {
        $this->parseMode = 'HTML';
        return $this;
    }

    public function withMarkdown(): self
    {
        $this->parseMode = 'MarkdownV2';
        return $this;
    }

    public function withMarkdownLegacy(): self
    {
        $this->parseMode = 'Markdown';
        return $this;
    }

    public function withKeyboard(Keyboard $keyboard): self
    {
        $this->replyMarkup = $keyboard->get();
        return $this;
    }

    public function withMessageId(int $messageId): self
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function withoutNotification(): self
    {
        $this->disableNotification = true;
        return $this;
    }

    public function send(): mixed
    {
        $defaultBot = config('telegram.default', 'mybot');
        if (!$this->chatId) {
            $this->chatId = config("telegram.bots.{$defaultBot}.channel_id");
        }

        if (!$this->chatId) {
            return null;
        }

        $params = ['chat_id' => $this->chatId];

        if ($this->parseMode) {
            $params['parse_mode'] = $this->parseMode;
        }

        if ($this->replyMarkup) {
            $params['reply_markup'] = $this->replyMarkup;
        }

        if ($this->disableNotification) {
            $params['disable_notification'] = true;
        }

        if ($this->mediaGroup) {
            $params['media'] = json_encode($this->mediaGroup);
            $response = $this->telegram->sendMediaGroup($params);
            $this->reset();
            return $response;
        } elseif ($this->document) {
            $params['document'] = $this->document;
            if ($this->message) {
                $params['caption'] = $this->message;
            }
            $response = $this->telegram->sendDocument($params);
            $this->reset();
            return $response;
        } elseif ($this->photo) {
            $params['photo'] = $this->photo;
            if ($this->message) {
                $params['caption'] = $this->message;
            }
            $response = $this->telegram->sendPhoto($params);
            $this->reset();
            return $response;
        } elseif ($this->message) {
            $params['text'] = $this->message;
            $response = $this->telegram->sendMessage($params);
            $this->reset();
            return $response;
        }

        $this->reset();
        return null;
    }

    public function update(): mixed
    {
        if (!$this->chatId || !$this->messageId) {
            return null;
        }

        $params = [
            'chat_id' => $this->chatId,
            'message_id' => $this->messageId,
        ];

        if ($this->parseMode) {
            $params['parse_mode'] = $this->parseMode;
        }

        if ($this->replyMarkup) {
            $params['reply_markup'] = $this->replyMarkup;
        }

        if ($this->photo || $this->document) {
            $params['caption'] = $this->message ?? null;
            $response = $this->telegram->editMessageCaption($params);
        } else {
            $params['text'] = $this->message ?? null;
            $response = $this->telegram->editMessageText($params);
        }

        $this->reset();
        return $response;
    }

    public function delete(): mixed
    {
        if ($this->chatId && $this->messageId) {
            $response = $this->telegram->deleteMessage([
                'chat_id' => $this->chatId,
                'message_id' => $this->messageId,
            ]);
            $this->reset();
            return $response;
        }
        return null;
    }

    public function pin(): mixed
    {
        if ($this->chatId && $this->messageId) {
            $params = [
                'chat_id' => $this->chatId,
                'message_id' => $this->messageId,
            ];

            if ($this->disableNotification) {
                $params['disable_notification'] = true;
            }

            $response = $this->telegram->pinChatMessage($params);
            $this->reset();
            return $response;
        }
        return null;
    }

    protected function reset(): void
    {
        $this->message = null;
        $this->photo = null;
        $this->document = null;
        $this->mediaGroup = null;
        $this->replyMarkup = null;
        $this->parseMode = 'HTML';
        $this->disableNotification = false;
        $this->messageId = null;
    }
}
