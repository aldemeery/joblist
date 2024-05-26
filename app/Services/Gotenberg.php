<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Gotenberg
{
    private readonly string $url;

    private function __construct(
        private readonly string $html,
    ) {
        $this->url = sprintf(
            '%s/%s',
            trim(config('services.gotenberg.host'), '/'),
            'forms/chromium/convert/html',
        );
    }

    public static function usingHtml(string $html): self
    {
        return new self($html);
    }

    public function getPdf(): string
    {
        return Http::attach([
            ['files', $this->html, 'index.html'],
            ['paperWidth', '8.27'],
            ['paperHeight', '11.7'],
            ['marginTop', '0.0'],
            ['marginBottom', '0.0'],
            ['marginLeft', '0.0'],
            ['marginRight', '0.0'],
        ])->post($this->url)->body();
    }
}
