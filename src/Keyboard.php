<?php

namespace RMS\Telegram;

use Telegram\Bot\Keyboard\Keyboard as TelegramKeyboard;

class Keyboard
{
    protected $keyboard;
    protected $isInline;

    public static function inline(): self
    {
        $instance = new self();
        $instance->keyboard = TelegramKeyboard::make()->inline();
        $instance->isInline = true;
        return $instance;
    }

    public static function reply(): self
    {
        $instance = new self();
        $instance->keyboard = TelegramKeyboard::make();
        $instance->isInline = false;
        return $instance;
    }

    public function row(array $buttons): self
    {
        $row = [];
        foreach ($buttons as $button) {
            if (is_string($button)) {
                $row[] = $this->isInline
                    ? TelegramKeyboard::inlineButton(['text' => $button, 'callback_data' => $button])
                    : TelegramKeyboard::button($button);
            } elseif ($button instanceof Button) {
                $row[] = $this->isInline
                    ? TelegramKeyboard::inlineButton($button->get())
                    : TelegramKeyboard::button($button->get());
            }
        }
        $this->keyboard->row($row);
        return $this;
    }

    public function get()
    {
        return $this->keyboard;
    }
}
