<?php

namespace Kaloa\Renderer;

use League\CommonMark\CommonMarkConverter;

/**
 *
 */
final class CommonMarkRenderer implements RendererInterface
{
    public function render($input)
    {
        $converter = new CommonMarkConverter();
        return $converter->convertToHtml($input);
    }
}
