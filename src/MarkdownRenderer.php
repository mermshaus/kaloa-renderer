<?php

namespace Kaloa\Renderer;

use Michelf\Markdown;

/**
 *
 */
final class MarkdownRenderer implements RendererInterface
{
    public function render($input)
    {
        return Markdown::defaultTransform($input);
    }
}
