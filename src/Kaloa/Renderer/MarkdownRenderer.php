<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\AbstractRenderer;
use Kaloa\Renderer\Markdown\Parser;

class MarkdownRenderer extends AbstractRenderer
{
    public function render($input)
    {
        $md = new Parser();
        return $md->transform($input);
    }
}
