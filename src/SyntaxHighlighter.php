<?php

namespace Kaloa\Renderer;

/**
 * This is a transitional dummy class. GeSHi had to be removed because it was
 * licensed as GPL, not MIT.
 */
class SyntaxHighlighter implements SyntaxHighlighterInterface
{
    /**
     * @param string $source
     * @param string $language
     *
     * @return string
     */
    public function highlight($source, $language = '')
    {
        $e = function ($s) {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };

        $source = trim($source, "\r\n");

        $classStr = ($language === '') ? ' class="language-none"' : ' class="language-' . $e($language) . '"';

        return '<pre><code' . $classStr . '>' . $e($source) . '</code></pre>';
    }
}
