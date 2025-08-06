<?php

namespace RMS\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;
use RMS\Telegram\MediaGroup;

class TestTelegram extends Command
{
    protected $signature = 'telegram:test';
    protected $description = 'Run operational tests for rmscms/telegram package';

    public function handle()
    {
        // ساخت فایل‌های تستی
        $this->call('telegram:test-files');

        $results = [];
        $messageId = null;
        $photoMessageId = null;

        // تست ۱: ارسال پیام متنی
        try {
            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->message('Hello, this is a <b>bold</b> test message!')
                ->withHtml()
                ->withoutNotification()
                ->send();
            if (is_null($response)) {
                throw new \Exception('Send response is null');
            }
            $messageId = $response->getMessageId();
            \Log::info('Send Text response: ' . json_encode($response->toArray()));
            $results[] = ['Test' => 'Send Text', 'Status' => 'Success', 'Message' => 'Text message sent, ID: ' . $messageId];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Text', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۲: ارسال عکس
        try {
            $keyboard = Keyboard::inline()
                ->row([
                    Button::make('Visit Site')->url('https://example.com'),
                    Button::make('Action')->callbackData('action_data'),
                ]);

            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->photo('images/test.jpg')
                ->message('This is a <b>photo</b> caption!')
                ->withHtml()
                ->withKeyboard($keyboard)
                ->withoutNotification()
                ->send();
            if (is_null($response)) {
                throw new \Exception('Send photo response is null');
            }
            $photoMessageId = $response->getMessageId();
            \Log::info('Send Photo response: ' . json_encode($response->toArray()));
            $results[] = ['Test' => 'Send Photo', 'Status' => 'Success', 'Message' => 'Photo sent, ID: ' . $photoMessageId];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Photo', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۳: ارسال سند
        try {
            $keyboard = Keyboard::inline()
                ->row([
                    Button::make('Visit Site')->url('https://example.com'),
                ]);

            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->document('documents/test.pdf')
                ->message('This is a <b>document</b> caption!')
                ->withHtml()
                ->withKeyboard($keyboard)
                ->withoutNotification()
                ->send();
            if (is_null($response)) {
                throw new \Exception('Send document response is null');
            }
            \Log::info('Send Document response: ' . json_encode($response->toArray()));
            $results[] = ['Test' => 'Send Document', 'Status' => 'Success', 'Message' => 'Document sent'];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Document', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۴: ارسال گروه رسانه
        try {
            $mediaGroup = MediaGroup::make()
                ->addPhoto('images/test.jpg', 'Test photo caption')
                ->addVideo('videos/test.mp4', 'Test video caption');

            $keyboard = Keyboard::inline()
                ->row([
                    Button::make('Click')->url('https://example.com'),
                ]);

            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->mediaGroup($mediaGroup)
                ->withHtml()
                ->withKeyboard($keyboard)
                ->withoutNotification()
                ->send();
            if (empty($response)) {
                throw new \Exception('Send media group response is empty');
            }
            $mediaIds = [];
            foreach ($response as $msg) {
                if (is_object($msg)) {
                    $mediaIds[] = $msg->getMessageId();
                } else {
                    $mediaIds[] = $msg['message_id'];
                }
            }
            $results[] = ['Test' => 'Send Media Group', 'Status' => 'Success', 'Message' => 'Media group sent, IDs: ' . implode(', ', $mediaIds)];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Media Group', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۵: آپدیت پیام متنی
        try {
            if ($messageId) {
                $keyboard = Keyboard::inline()
                    ->row([
                        Button::make('Updated')->url('https://example.com'),
                    ]);

                $response = app('rms.telegram')
                    ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                    ->withMessageId($messageId)
                    ->message('This is an <b>updated</b> text message!')
                    ->withHtml()
                    ->withKeyboard($keyboard)
                    ->update();
                if (is_null($response)) {
                    throw new \Exception('Update message response is null');
                }
                \Log::info('Update Message response: ' . json_encode($response->toArray()));
                $results[] = ['Test' => 'Update Message', 'Status' => 'Success', 'Message' => 'Message updated'];
            } else {
                throw new \Exception('No message_id available');
            }
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Update Message', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۶: آپدیت کپشن
        try {
            if ($photoMessageId) {
                $keyboard = Keyboard::inline()
                    ->row([
                        Button::make('Updated')->url('https://example.com'),
                    ]);

                $response = app('rms.telegram')
                    ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                    ->withMessageId($photoMessageId)
                    ->photo('images/test.jpg')
                    ->message('This is an <b>updated</b> caption!')
                    ->withHtml()
                    ->withKeyboard($keyboard)
                    ->update();
                if (is_null($response)) {
                    throw new \Exception('Update caption response is null');
                }
                \Log::info('Update Caption response: ' . json_encode($response->toArray()));
                $results[] = ['Test' => 'Update Caption', 'Status' => 'Success', 'Message' => 'Caption updated'];
            } else {
                throw new \Exception('No photo_message_id available');
            }
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Update Caption', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۷: پین کردن پیام
        try {
            if ($messageId) {
                $response = app('rms.telegram')
                    ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                    ->withMessageId($messageId)
                    ->withoutNotification()
                    ->pin();
                if (!$response) {
                    throw new \Exception('Pin message response is false');
                }
                \Log::info('Pin Message response: ' . json_encode($response));
                $results[] = ['Test' => 'Pin Message', 'Status' => 'Success', 'Message' => 'Message pinned'];
            } else {
                throw new \Exception('No message_id available');
            }
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Pin Message', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۸: حذف پیام
        try {
            if ($messageId) {
                $response = app('rms.telegram')
                    ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                    ->withMessageId($messageId)
                    ->delete();
                if (!$response) {
                    throw new \Exception('Delete message response is false');
                }
                \Log::info('Delete Message response: ' . json_encode($response));
                $results[] = ['Test' => 'Delete Message', 'Status' => 'Success', 'Message' => 'Message deleted'];
            } else {
                throw new \Exception('No message_id available');
            }
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Delete Message', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۹: ارسال با Markdown
        try {
            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->message('This is a *bold* message\!')
                ->withMarkdown()
                ->send();
            if (is_null($response)) {
                throw new \Exception('Send markdown response is null');
            }
            \Log::info('Send Markdown response: ' . json_encode($response->toArray()));
            $results[] = ['Test' => 'Send Markdown', 'Status' => 'Success', 'Message' => 'Markdown message sent'];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Markdown', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۱۰: ارسال با کیبورد اینلاین
        try {
            $keyboard = Keyboard::inline()
                ->row([
                    Button::make('Visit')->url('https://example.com'),
                    Button::make('Action')->callbackData('action_data'),
                ]);

            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->message('Choose an option:')
                ->withHtml()
                ->withKeyboard($keyboard)
                ->send();
            if (is_null($response)) {
                throw new \Exception('Send inline keyboard response is null');
            }
            \Log::info('Send Inline Keyboard response: ' . json_encode($response->toArray()));
            $results[] = ['Test' => 'Send Inline Keyboard', 'Status' => 'Success', 'Message' => 'Message with inline keyboard sent'];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Inline Keyboard', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // تست ۱۱: ارسال با کیبورد اینلاین (چون reply تو کانال کار نمی‌کنه)
        try {
            $keyboard = Keyboard::inline()
                ->row([
                    Button::make('Option 1')->url('https://example.com'),
                    Button::make('Option 2')->callbackData('option_2'),
                ]);

            $response = app('rms.telegram')
                ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
                ->message('Select an option:')
                ->withHtml()
                ->withKeyboard($keyboard)
                ->send();
            if (is_null($response)) {
                throw new \Exception('Send inline keyboard response is null');
            }
            \Log::info('Send Reply Keyboard response: ' . json_encode($response->toArray()));
            $results[] = ['Test' => 'Send Inline Keyboard (Reply)', 'Status' => 'Success', 'Message' => 'Message with inline keyboard sent'];
        } catch (\Exception $e) {
            $results[] = ['Test' => 'Send Inline Keyboard (Reply)', 'Status' => 'Failed', 'Message' => $e->getMessage()];
        }

        // حذف فایل‌های تستی
        $this->call('telegram:test-files', ['--clean' => true]);

        // نمایش جدول نتایج
        $this->table(['Test', 'Status', 'Message'], $results);

        // لاگ نتایج
        \Log::info('Telegram Tests Results', $results);
    }
}
