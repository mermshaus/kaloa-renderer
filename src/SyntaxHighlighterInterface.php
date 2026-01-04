<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

interface SyntaxHighlighterInterface
{
    public function highlight(string $source, string $language): string ;
}
