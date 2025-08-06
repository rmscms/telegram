# RMS Telegram Package for Laravel

A robust and fluent Telegram integration package for Laravel applications, built on top of `irazasyed/telegram-bot-sdk`. This package provides a chainable interface to interact with Telegram's API, enabling seamless sending of messages, photos, documents, media groups, and managing messages in Telegram channels.

## Features

- **Fluent Interface**: Chainable methods for sending messages, photos, documents, and media groups.
- **Custom Keyboards**: Support for inline and reply keyboards with flexible button configurations.
- **Parse Modes**: Supports `HTML`, `MarkdownV2`, and legacy `Markdown` for message formatting.
- **File Handling**: Integrates with Laravel's `Storage` for seamless file uploads.
- **Comprehensive Testing**: Built-in Artisan command to test all package features.
- **Extensible**: Easy to extend for additional Telegram API functionalities.

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

   Ensure your bot is an admin in the specified channel.

## Configuration

The `config/telegram.php` file contains:

```php
return [
    'bots' => [
        'mybot' => [
            'token' => env('TELEGRAM_BOT_TOKEN', ''),
            'channel_id' => env('TELEGRAM_CHANNEL_ID', ''),
            'certificate_path' => env('TELEGRAM_CERTIFICATE_PATH', ''),
            'webhook_url' => env('TELEGRAM_WEBHOOK_URL', ''),
            'allowed_updates' => null,
            'commands' => [],
        ],
    ],
    'default' => 'mybot',
    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),
    'http_client_handler' => null,
    'base_bot_url' => env('TELEGRAM_BASE_BOT_URL', 'https://api.telegram.org/bot'),
    'resolve_command_dependencies' => true,
    'commands' => [
        \Telegram\Bot\Commands\HelpCommand::class,
    ],
    'command_groups' => [],
    'shared_commands' => [],
];
```

## Usage

The package uses a fluent, chainable interface for interacting with Telegram. Below are examples for each feature.

### 1. Sending a Text Message

```php
use RMS\Telegram\Telegram;

Route::get('/send-text', function () {
    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
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

    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
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

Documents are loaded from `storage/app/public`.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/send-document', function () {
    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Visit Site')->url('https://example.com'),
        ]);

    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
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
        ->addVideo('videos/video1.mp4', 'Video caption');

    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Click')->url('https://example.com'),
        ]);

    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
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

    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
        ->withMessageId(123)
        ->message('This is an <b>updated</b> message!')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->update();

    return 'Message updated!';
});

Route::get('/update-caption', function () {
    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
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
    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
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
    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
        ->withMessageId(123)
        ->withoutNotification()
        ->pin();

    return 'Message pinned!';
});
```

### 8. Using Parse Modes

Supports `HTML`, `MarkdownV2`, and `Markdown` (legacy) parse modes. Default is `HTML`.

```php
use RMS\Telegram\Telegram;

Route::get('/send-markdown', function () {
    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
        ->message('This is a *bold* message\!')
        ->withMarkdown()
        ->send();

    return 'Markdown message sent!';
});
```

### 9. Creating Custom Keyboards

Use `Keyboard` and `Button` classes to create inline or reply keyboards.

```php
use RMS\Telegram\Telegram;
use RMS\Telegram\Keyboard;
use RMS\Telegram\Button;

Route::get('/send-with-keyboard', function () {
    $keyboard = Keyboard::inline()
        ->row([
            Button::make('Visit')->url('https://example.com'),
            Button::make('Action')->callbackData('action_data'),
        ]);

    app('rms.telegram')
        ->to(config('telegram.bots.' . config('telegram.default') . '.channel_id'))
        ->message('Choose an option:')
        ->withHtml()
        ->withKeyboard($keyboard)
        ->send();

    return 'Message with keyboard sent!';
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

Place files in `storage/app/public` (e.g., `images/photo.jpg`, `documents/file.pdf`, `videos/video.mp4`).

## Testing

The package includes a powerful Artisan command to validate all features:

```bash
php artisan telegram:test
```

### Test Command Details

The `telegram:test` command executes a comprehensive suite of tests to verify the package's functionality:

- **Send Text**: Sends a text message with HTML formatting.
- **Send Photo**: Sends a photo with a caption and inline keyboard.
- **Send Document**: Sends a document with a caption and inline keyboard.
- **Send Media Group**: Sends a group of media (photo and video) with captions and an inline keyboard.
- **Update Message**: Updates the text of a previously sent message.
- **Update Caption**: Updates the caption of a previously sent photo.
- **Pin Message**: Pins a message in the channel.
- **Delete Message**: Deletes a message from the channel.
- **Send Markdown**: Sends a message with MarkdownV2 formatting.
- **Send Inline Keyboard**: Sends a message with an inline keyboard.
- **Send Inline Keyboard (Reply)**: Sends a message with another inline keyboard (emulating reply keyboard behavior in channels).

The command automatically:
- Creates test files (`test.jpg`, `test.pdf`, `test.mp4`) in `storage/app/public` if they don't exist.
- Runs all tests and displays results in a table.
- Cleans up test files after execution (with `--clean` option).

**Note**: For the `Send Media Group` test, ensure a valid MP4 file is placed in `storage/app/public/videos/test.mp4` (e.g., download from `https://sample-videos.com`). The command preserves existing files to avoid overwriting.

To run unit tests:

```bash
cd vendor/rmscms/telegram
composer install --dev
./vendor/bin/phpunit
```

## Contributing

Contributions are welcome! Please submit issues or pull requests to the [GitHub repository](https://github.com/rmscms/telegram).

## License

This package is open-sourced under the [MIT License](LICENSE).