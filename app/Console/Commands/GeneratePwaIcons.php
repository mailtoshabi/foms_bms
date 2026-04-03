<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:icons
                            {--source= : Absolute path to source image (default: public/assets/favicon/android-chrome-512x512.png)}';

    protected $description = 'Generate PWA icons in all required sizes from a source image (requires GD)';

    /** Sizes required by the manifest */
    private array $sizes = [72, 96, 128, 144, 152, 192, 256, 384, 512];

    public function handle(): int
    {
        if (!extension_loaded('gd')) {
            $this->error('PHP GD extension is not enabled. Please enable it in php.ini and restart your server.');
            return Command::FAILURE;
        }

        $sourcePath = $this->option('source')
            ?? public_path('assets/favicon/android-chrome-512x512.png');

        if (!file_exists($sourcePath)) {
            $this->error("Source image not found: {$sourcePath}");
            $this->line('Hint: pass --source=/absolute/path/to/your-logo.png');
            return Command::FAILURE;
        }

        $outputDir = public_path('images/icons');

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
            $this->info("Created directory: {$outputDir}");
        }

        $source = $this->loadImage($sourcePath);

        if (!$source) {
            $this->error('Failed to load source image. Supported formats: PNG, JPEG, GIF, WebP.');
            return Command::FAILURE;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        foreach ($this->sizes as $size) {
            $canvas = imagecreatetruecolor($size, $size);

            // Preserve transparency
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
            imagealphablending($canvas, true);

            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $size, $size, $srcW, $srcH);

            $outputPath = "{$outputDir}/icon-{$size}x{$size}.png";
            imagepng($canvas, $outputPath, 9);
            imagedestroy($canvas);

            $this->line("  <comment>✓</comment> icon-{$size}x{$size}.png");
        }

        imagedestroy($source);

        $this->newLine();
        $this->info('All PWA icons generated at: public/images/icons/');
        $this->line('Icons are referenced in public/manifest.json and the PWA install component.');

        return Command::SUCCESS;
    }

    private function loadImage(string $path): \GdImage|false
    {
        $mime = mime_content_type($path);

        // @ suppresses non-fatal GD warnings (e.g. iCCP profile mismatch)
        return match ($mime) {
            'image/png'         => @imagecreatefrompng($path),
            'image/jpeg',
            'image/jpg'         => @imagecreatefromjpeg($path),
            'image/gif'         => @imagecreatefromgif($path),
            'image/webp'        => @imagecreatefromwebp($path),
            default             => false,
        };
    }
}
