<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\AbstractRenderer;
use Michelf\Markdown;

class MarkdownRenderer extends AbstractRenderer
{
    public function render($input)
    {
        return $parser = Markdown::defaultTransform($input);
    }
}
