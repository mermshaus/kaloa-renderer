<?php

namespace Kaloa\Renderer;

/**
 * This is a transitional dummy class. GeSHi had to be removed because it was
 * licensed as GPL, not MIT.
 */
class SyntaxHighlighter
{
    public function highlight($source, $language)
    {
        // I miss GeSHi

        return '<pre>' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
}
