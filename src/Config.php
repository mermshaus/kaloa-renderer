<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

final class Config
{
    private string $resourceBasePath;
    private SyntaxHighlighterInterface $syntaxHighlighter;

    public function __construct(
        string $resourceBasePath = '.',
        ?SyntaxHighlighterInterface $syntaxHighlighter = null
    ) {
        $this->resourceBasePath = $resourceBasePath;

        $this->syntaxHighlighter =
            (is_null($syntaxHighlighter)) ? new SyntaxHighlighter() : $syntaxHighlighter;
    }

    public function getResourceBasePath(): string
    {
        return $this->resourceBasePath;
    }

    public function getSyntaxHighlighter(): SyntaxHighlighterInterface
    {
        return $this->syntaxHighlighter;
    }
}
