<?php

namespace RMS\Telegram;

use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Api;

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
        $this->telegram = new Api(config('telegram.bot_token'));
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
        $this->photo = Storage::path($path);
        return $this;
    }

    public function document(string $path): self
    {
        $this->document = Storage::path($path);
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

    public function send(): void
    {
        if (!$this->chatId || (!$this->message && !$this->photo && !$this->document && !$this->mediaGroup)) {
            return;
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
            $this->telegram->sendMediaGroup($params);
        } elseif ($this->document) {
            $params['document'] = $this->document;
            $params['caption'] = $this->message ?? null;
            $this->telegram->sendDocument($params);
        } elseif ($this->photo) {
            $params['photo'] = $this->photo;
            $params['caption'] = $this->message ?? null;
            $this->telegram->sendPhoto($params);
        } elseif ($this->message) {
            $params['text'] = $this->message;
            $this->telegram->sendMessage($params);
        }
    }

    public function update(): void
    {
        if (!$this->chatId || !$this->messageId || !$this->message) {
            return;
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
            $this->telegram->editMessageCaption($params);
        } else {
            $params['text'] = $this->message;
            $this->telegram->editMessageText($params);
        }
    }

    public function delete(): void
    {
        if ($this->chatId && $this->messageId) {
            $this->telegram->deleteMessage([
                'chat_id' => $this->chatId,
                'message_id' => $this->messageId,
            ]);
        }
    }

    public function pin(): void
    {
        if ($this->chatId && $this->messageId) {
            $params = [
                'chat_id' => $this->chatId,
                'message_id' => $this->messageId,
            ];

            if ($this->disableNotification) {
                $params['disable_notification'] = true;
            }

            $this->telegram->pinChatMessage($params);
        }
    }
}
