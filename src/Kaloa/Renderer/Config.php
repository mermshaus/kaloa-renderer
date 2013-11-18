<?php

namespace Kaloa\Renderer;

class Config
{
    protected $resourceBasePath = '.';

    /**
     *
     * @var SyntaxHighlighter
     */
    protected $syntaxHighlighter = null;

    public function getResourceBasePath()
    {
        return $this->resourceBasePath;
    }

    public function setResourceBasePath($resourceBasePath)
    {
        $this->resourceBasePath = $resourceBasePath;
    }

    /**
     *
     * @return SyntaxHighlighter
     */
    public function getSyntaxHighlighter()
    {
        if ($this->syntaxHighlighter === null) {
            $this->syntaxHighlighter = new SyntaxHighlighter();
        }

        return $this->syntaxHighlighter;
    }

    public function setSyntaxHighlighter(SyntaxHighlighter $syntaxHighlighter)
    {
        $this->syntaxHighlighter = $syntaxHighlighter;
    }
}
