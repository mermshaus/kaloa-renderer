<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use League\CommonMark\CommonMarkConverter;

final class CommonMarkRenderer implements RendererInterface
{
    public function render(string $input): string
    {
        $converter = new CommonMarkConverter();
        return $converter->convertToHtml($input)->getContent();
    }
}
