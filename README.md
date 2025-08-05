# RMS Telegram Package for Laravel

A powerful and fluent Telegram integration package for Laravel applications, built on top of the `irazasyed/telegram-bot-sdk`. This package provides a chainable interface to send messages, photos, documents, media groups, and manage Telegram messages with ease.

## Installation

1. **Install the Package**

   Add the package to your Laravel project via Composer:

   ```bash
   composer require rmscms/telegram
   ```

2. **Publish Configuration**

   Publish the configuration file to your project:

   ```bash
   php artisan vendor:publish --tag=telegram-config
   ```

   This creates a `config/telegram.php` file in your Laravel project.

3. **Configure Environment**

   Add the following to your `.env` file:

   ```env
   TELEGRAM_BOT_TOKEN=your-bot-token-here
   TELEGRAM_CHANNEL_ID=@YourChannel
   ```

## Configuration

The `config/telegram.php` file contains:

```php
return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
    'channel_id' => env('TELEGRAM_CHANNEL_ID', ''),
    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),
    'http_client_handler' => null,
    'base_bot_url' => env('TELEGRAM_BASE_BOT_URL', 'https://api.telegram.org/bot'),
    'commands' => [],
    'command_groups' => ['default' => []],
    'shared_commands' => [
        'start' => \Telegram\Bot\Commands\StartCommand::class,
        'help' => \Telegram\Bot\Commands\HelpCommand::class,
    ],
];
```

## Usage

The package uses a fluent, chainable interface for interacting with Telegram. Below are detailed examples for each feature.

### 1. Sending a Text Message

```php
use RMS\Telegram\Telegram;

Route::get('/send-text', function () {
    app('telegram')
        ->to(config('telegram.channel_id'))
        ->message('Hello, this is a <b>bold</b> message!')
        ->withHtml()
        ->withoutNotification()
        ->send();

    return 'Text message sent!';
});
```

### 2. Sending a Photo

Photos are loaded from Laravel's `Storage` disk (e.g., `public`). Ensure the file exists in `storage/app/public`.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/send-photo', function () {
    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Visit Site')->url('https://example.com'),
            Button::make('Action')->callbackData('action_data'),
        ]);

    app('telegram')
        ->to(config('telegram.channel_id'))
        ->photo('images/photo.jpg')
        ->message('This is a <b>photo</b> caption!')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->withoutNotification()
        ->send();

    return 'Photo sent!';
});
```

### 3. Sending a Document

Documents are also loaded from `Storage`. Ensure the file exists in `storage/app/public`.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/send-document', function () {
    $keyboard = Keyboard::reply()
        ->row([
            Button::make('Contact')->requestContact(),
            Button::make('Location')->requestLocation(),
            'Option 3',
        ]);

    app('telegram')
        ->to(config('telegram.channel_id'))
        ->document('documents/file.pdf')
        ->message('This is a <b>document</b> caption!')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->send();

    return 'Document sent!';
});
```

### 4. Sending a Media Group

Send multiple photos or videos in one message using `MediaGroup`.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\MediaGroup;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/send-media-group', function () {
    $mediaGroup = MediaGroup::make()
        ->addPhoto('images/photo1.jpg', 'Photo 1 caption')
        ->addPhoto('images/photo2.jpg', 'Photo 2 caption')
        ->addVideo('videos/video1.mp4', 'Video caption');

    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Click')->url('https://example.com'),
        ]);

    app('telegram')
        ->to(config('telegram.channel_id'))
        ->mediaGroup($mediaGroup)
        ->withHtml()
        ->withKeyboard($keyboard)
        ->withoutNotification()
        ->send();

    return 'Media group sent!';
});
```

### 5. Updating a Message or Caption

Update a text message or media caption using `message_id`.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/update-message', function () {
    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Updated')->url('https://example.com'),
        ]);

    app('telegram')
        ->to(config('telegram.channel_id'))
        ->withMessageId(123)
        ->message('This is an <b>updated</b> message!')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->update();

    return 'Message updated!';
});

Route::get('/update-caption', function () {
    app('telegram')
        ->to(config('telegram.channel_id'))
        ->withMessageId(123)
        ->photo('images/photo.jpg')
        ->message('This is an <b>updated</b> caption!')
        ->withHtml()
        ->update();

    return 'Caption updated!';
});
```

### 6. Deleting a Message

Delete a message using `message_id`.

```php
use RMS\Telegram\Telegram;

Route::get('/delete-message', function () {
    app('telegram')
        ->to(config('telegram.channel_id'))
        ->withMessageId(123)
        ->delete();

    return 'Message deleted!';
});
```

### 7. Pinning a Message

Pin a message in a channel using `message_id`.

```php
use RMS\Telegram\Telegram;

Route::get('/pin-message', function () {
    app('telegram')
        ->to(config('telegram.channel_id'))
        ->withMessageId(123)
        ->withoutNotification()
        ->pin();

    return 'Message pinned!';
});
```

### 8. Using Parse Modes

The package supports `HTML`, `MarkdownV2`, and `Markdown` (legacy) parse modes. Default is `HTML`.

```php
use RMS\Telegram\Telegram;

Route::get('/send-markdown', function () {
    app('telegram')
        ->to(config('telegram.channel_id'))
        ->message('This is a *bold* message!')
        ->withMarkdown()
        ->send();

    return 'Markdown message sent!';
});
```

### 9. Creating Custom Keyboards

Use the `Keyboard` and `Button` classes to create inline or reply keyboards.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/send-with-keyboard', function () {
    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Visit')->url('https://example.com'),
            Button::make('Action')->callbackData('action_data'),
        ])
        ->row([
            Button::make('Inline Query')->switchInlineQuery('query'),
        ]);

    app('telegram')
        ->to(config('telegram.channel_id'))
        ->message('Choose an option:')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->send();

    return 'Message with keyboard sent!';
});

Route::get('/send-reply-keyboard', function () {
    $keyboard = Keyboard::reply()
        ->row([
            Button::make('Contact')->requestContact(),
            Button::make('Location')->requestLocation(),
        ])
        ->row(['Option 3']);

    app('telegram')
        ->to(config('telegram.channel_id'))
        ->message('Select an option:')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->send();

    return 'Reply keyboard sent!';
});
```

## Storage Configuration

The package uses Laravel's `Storage` for file handling. Ensure your `config/filesystems.php` has a `public` disk:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

Place files in `storage/app/public` (e.g., `images/photo.jpg` or `documents/file.pdf`).

## Contributing

Contributions are welcome! Please submit issues or pull requests to the [GitHub repository](https://github.com/rmscms/telegram).

## License

This package is open-sourced under the [MIT License](LICENSE).