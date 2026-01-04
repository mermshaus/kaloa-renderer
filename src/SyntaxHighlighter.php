<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

/**
 * This is a transitional dummy class. GeSHi had to be removed because it was
 * licensed as GPL, not MIT.
 */
class SyntaxHighlighter implements SyntaxHighlighterInterface
{
    public function highlight(string $source, string $language = ''): string
    {
        $e = function ($s) {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };

        $source = trim($source, "\r\n");

        $classStr = ($language === '') ? ' class="language-none"'
            : ' class="language-' . $e($language) . '"';

        return '<pre><code' . $classStr . '>' . $e($source) . '</code></pre>';
    }
}
