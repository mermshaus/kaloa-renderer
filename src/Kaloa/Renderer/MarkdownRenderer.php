<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\AbstractRenderer;
use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\Parser;
use Kaloa\Renderer\Markdown\RegexManager;

class MarkdownRenderer extends AbstractRenderer
{
    public function render($input)
    {
        $md = new Parser(new Hasher(), new RegexManager());
        return $md->transform($input);
    }
}
