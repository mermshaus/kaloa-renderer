<?php

namespace Kaloa\Renderer;

interface SyntaxHighlighterInterface
{
    public function highlight($source, $language);
}
