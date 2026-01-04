<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use Michelf\Markdown;

final class MarkdownRenderer implements RendererInterface
{
    public function render(string $input): string
    {
        return Markdown::defaultTransform($input);
    }
}
