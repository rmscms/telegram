<?php

namespace RMS\Telegram\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;
use RMS\Telegram\MediaGroup;
use Mockery;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

class TelegramTest extends TestCase
{
    protected $telegramApi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->telegramApi = Mockery::mock(Api::class);
        $this->app->singleton('telegram', function () {
            $telegram = new Telegram();
            $telegram->setApi($this->telegramApi);
            return $telegram;
        });
        config(['telegram.bot_token' => 'test-token', 'telegram.channel_id' => '@TestChannel']);
    }

    protected function getPackageProviders($app)
    {
        return ['RMS\Telegram\TelegramServiceProvider'];
    }

    public function testSendTextMessage()
    {
        $this->telegramApi->shouldReceive('sendMessage')
            ->once()
            ->withArgs(function ($args) {
                return $args['chat_id'] === '@TestChannel'
                    && $args['text'] === 'Test message'
                    && $args['parse_mode'] === 'HTML'
                    && $args['disable_notification'] === true;
            })
            ->andReturn(new Message(['message_id' => 123]));

        app('telegram')
            ->to('@TestChannel')
            ->message('Test message')
            ->withHtml()
            ->withoutNotification()
            ->send();

        $this->assertTrue(true);
    }

    public function testSendPhoto()
    {
        Storage::disk('public')->put('images/photo.jpg', 'fake content');
        $photoPath = str_replace('\\', '/', Storage::disk('public')->path('images/photo.jpg'));

        $this->telegramApi->shouldReceive('sendPhoto')
            ->once()
            ->withArgs(function ($args) use ($photoPath) {
                return $args['chat_id'] === '@TestChannel'
                    && str_replace('\\', '/', $args['photo']) === $photoPath
                    && $args['caption'] === 'Test caption'
                    && $args['parse_mode'] === 'HTML';
            })
            ->andReturn(new Message(['message_id' => 123]));

        app('telegram')
            ->to('@TestChannel')
            ->photo('images/photo.jpg')
            ->message('Test caption')
            ->withHtml()
            ->send();

        $this->assertTrue(true);
    }

    public function testSendDocument()
    {
        Storage::disk('public')->put('documents/file.pdf', 'fake content');
        $documentPath = str_replace('\\', '/', Storage::disk('public')->path('documents/file.pdf'));

        $this->telegramApi->shouldReceive('sendDocument')
            ->once()
            ->withArgs(function ($args) use ($documentPath) {
                return $args['chat_id'] === '@TestChannel'
                    && str_replace('\\', '/', $args['document']) === $documentPath
                    && $args['caption'] === 'Test document'
                    && $args['parse_mode'] === 'HTML';
            })
            ->andReturn(new Message(['message_id' => 123]));

        app('telegram')
            ->to('@TestChannel')
            ->document('documents/file.pdf')
            ->message('Test document')
            ->withHtml()
            ->send();

        $this->assertTrue(true);
    }

    public function testSendMediaGroup()
    {
        Storage::disk('public')->put('images/photo1.jpg', 'fake content');
        Storage::disk('public')->put('videos/video1.mp4', 'fake content');
        $photoPath = str_replace('\\', '/', Storage::disk('public')->path('images/photo1.jpg'));
        $videoPath = str_replace('\\', '/', Storage::disk('public')->path('videos/video1.mp4'));

        $this->telegramApi->shouldReceive('sendMediaGroup')
            ->once()
            ->withArgs(function ($args) use ($photoPath, $videoPath) {
                $media = json_decode($args['media'], true);
                return $args['chat_id'] === '@TestChannel'
                    && $args['parse_mode'] === 'HTML'
                    && $media[0]['type'] === 'photo'
                    && str_replace('\\', '/', $media[0]['media']) === $photoPath
                    && $media[0]['caption'] === 'Photo 1'
                    && $media[1]['type'] === 'video'
                    && str_replace('\\', '/', $media[1]['media']) === $videoPath
                    && $media[1]['caption'] === 'Video 1';
            })
            ->andReturn([new Message(['message_id' => 123])]);

        $mediaGroup = MediaGroup::make()
            ->addPhoto('images/photo1.jpg', 'Photo 1')
            ->addVideo('videos/video1.mp4', 'Video 1');

        app('telegram')
            ->to('@TestChannel')
            ->mediaGroup($mediaGroup)
            ->withHtml()
            ->send();

        $this->assertTrue(true);
    }

    public function testUpdateMessage()
    {
        $this->telegramApi->shouldReceive('editMessageText')
            ->once()
            ->withArgs(function ($args) {
                return $args['chat_id'] === '@TestChannel'
                    && $args['message_id'] === 123
                    && $args['text'] === 'Updated message'
                    && $args['parse_mode'] === 'HTML';
            })
            ->andReturn(new Message(['message_id' => 123]));

        app('telegram')
            ->to('@TestChannel')
            ->withMessageId(123)
            ->message('Updated message')
            ->withHtml()
            ->update();

        $this->assertTrue(true);
    }

    public function testUpdateCaption()
    {
        $this->telegramApi->shouldReceive('editMessageCaption')
            ->once()
            ->withArgs(function ($args) {
                return $args['chat_id'] === '@TestChannel'
                    && $args['message_id'] === 123
                    && $args['caption'] === 'Updated caption'
                    && $args['parse_mode'] === 'HTML';
            })
            ->andReturn(new Message(['message_id' => 123]));

        app('telegram')
            ->to('@TestChannel')
            ->withMessageId(123)
            ->photo('images/photo.jpg')
            ->message('Updated caption')
            ->withHtml()
            ->update();

        $this->assertTrue(true);
    }

    public function testDeleteMessage()
    {
        $this->telegramApi->shouldReceive('deleteMessage')
            ->once()
            ->withArgs(function ($args) {
                return $args['chat_id'] === '@TestChannel'
                    && $args['message_id'] === 123;
            })
            ->andReturn(true);

        app('telegram')
            ->to('@TestChannel')
            ->withMessageId(123)
            ->delete();

        $this->assertTrue(true);
    }

    public function testPinMessage()
    {
        $this->telegramApi->shouldReceive('pinChatMessage')
            ->once()
            ->withArgs(function ($args) {
                return $args['chat_id'] === '@TestChannel'
                    && $args['message_id'] === 123
                    && $args['disable_notification'] === true;
            })
            ->andReturn(true);

        app('telegram')
            ->to('@TestChannel')
            ->withMessageId(123)
            ->withoutNotification()
            ->pin();

        $this->assertTrue(true);
    }

    public function testSendWithKeyboard()
    {
        $keyboard = Keyboard::inline()
            ->row([
                Button::make('Click')->url('https://example.com'),
            ]);

        $this->telegramApi->shouldReceive('sendMessage')
            ->once()
            ->withArgs(function ($args) {
                return $args['chat_id'] === '@TestChannel'
                    && $args['text'] === 'Test with keyboard'
                    && $args['parse_mode'] === 'HTML'
                    && $args['reply_markup'] instanceof \Telegram\Bot\Keyboard\Keyboard;
            })
            ->andReturn(new Message(['message_id' => 123]));

        app('telegram')
            ->to('@TestChannel')
            ->message('Test with keyboard')
            ->withKeyboard($keyboard)
            ->withHtml()
            ->send();

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
        Mockery::close();
        Storage::fake('public')->deleteDirectory('images');
        Storage::fake('public')->deleteDirectory('videos');
        Storage::fake('public')->deleteDirectory('documents');
        parent::tearDown();
    }
}
