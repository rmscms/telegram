<?php

namespace RMS\Telegram;

class Button
{
    protected $params = [];

    public static function make(string $text): self
    {
        $instance = new self();
        $instance->params['text'] = $text;
        return $instance;
    }

    public function url(string $url): self
    {
        $this->params['url'] = $url;
        return $this;
    }

    public function callbackData(string $callbackData): self
    {
        $this->params['callback_data'] = $callbackData;
        return $this;
    }

    public function switchInlineQuery(string $query): self
    {
        $this->params['switch_inline_query'] = $query;
        return $this;
    }

    public function switchInlineQueryCurrentChat(string $query): self
    {
        $this->params['switch_inline_query_current_chat'] = $query;
        return $this;
    }

    public function requestContact(bool $request = true): self
    {
        $this->params['request_contact'] = $request;
        return $this;
    }

    public function requestLocation(bool $request = true): self
    {
        $this->params['request_location'] = $request;
        return $this;
    }

    public function get(): array
    {
        return $this->params;
    }
}
