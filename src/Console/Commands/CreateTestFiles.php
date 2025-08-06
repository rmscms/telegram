<?php

namespace RMS\Telegram\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateTestFiles extends Command
{
    protected $signature = 'telegram:test-files {--clean}';
    protected $description = 'Create or clean test files for rmscms/telegram package';

    public function handle()
    {
        if ($this->option('clean')) {
            Storage::disk('public')->deleteDirectory('images');
            Storage::disk('public')->deleteDirectory('documents');
            Storage::disk('public')->deleteDirectory('videos');
            $this->info('Test files cleaned.');
            return;
        }

        Storage::disk('public')->makeDirectory('images');
        Storage::disk('public')->makeDirectory('documents');
        Storage::disk('public')->makeDirectory('videos');

        // ساخت PDF تستی (اگه وجود نداشته باشه)
        if (!Storage::disk('public')->exists('documents/test.pdf')) {
            $pdfContent = "%PDF-1.0\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 44 >>\nstream\nBT /F1 12 Tf 100 700 Td (Test PDF File) Tj ET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000010 00000 n \n0000000065 00000 n \n0000000110 00000 n \n0000000179 00000 n \ntrailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n229\n%%EOF";
            Storage::disk('public')->put('documents/test.pdf', $pdfContent);
        }

        // ساخت عکس تستی (اگه وجود نداشته باشه)
        if (!Storage::disk('public')->exists('images/test.jpg')) {
            $image = imagecreatetruecolor(200, 200);
            $bgColor = imagecolorallocate($image, 255, 0, 0);
            imagefill($image, 0, 0, $bgColor);
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            Storage::disk('public')->put('images/test.jpg', $imageData);
            imagedestroy($image);
        }

        // ساخت ویدیوی تستی (اگه وجود نداشته باشه)
        if (!Storage::disk('public')->exists('videos/test.mp4')) {
            $videoContent = str_repeat("Fake MP4 content", 1000);
            Storage::disk('public')->put('videos/test.mp4', $videoContent);
        }

        $this->info('Test files created or kept: documents/test.pdf, images/test.jpg, videos/test.mp4');
    }
}
