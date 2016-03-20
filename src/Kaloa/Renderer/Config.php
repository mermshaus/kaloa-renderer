<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\SyntaxHighlighter;
use Kaloa\Renderer\SyntaxHighlighterInterface;

/**
 *
 * @api
 */
final class Config
{
    /**
     *
     * @var string
     */
    private $resourceBasePath;

    /**
     *
     * @var SyntaxHighlighterInterface
     */
    private $syntaxHighlighter;

    /**
     *
     * @param string $resourceBasePath
     * @param SyntaxHighlighterInterface $syntaxHighlighter
     */
    public function __construct(
        $resourceBasePath = '.',
        SyntaxHighlighterInterface $syntaxHighlighter = null
    ) {
        $this->resourceBasePath = (string) $resourceBasePath;

        $this->syntaxHighlighter = (null === $syntaxHighlighter)
                ? new SyntaxHighlighter()
                : $syntaxHighlighter;
    }

    /**
     *
     * @return string
     */
    public function getResourceBasePath()
    {
        return $this->resourceBasePath;
    }

    /**
     *
     * @return SyntaxHighlighterInterface
     */
    public function getSyntaxHighlighter()
    {
        return $this->syntaxHighlighter;
    }
}
