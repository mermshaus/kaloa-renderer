<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;
use Kaloa\Renderer\SyntaxHighlighter;

/**
 *
 */
class CodeHandler extends ProtoHandler
{
    /**
     *
     * @var string
     */
    protected $lang;

    /**
     *
     * @var SyntaxHighlighter
     */
    protected $syntaxHighlighter = null;

    /**
     *
     * @param SyntaxHighlighter $syntaxHighlighter
     */
    public function __construct(SyntaxHighlighter $syntaxHighlighter)
    {
        $this->name = 'code';

        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE
                | Parser::TAG_CLEAR_CONTENT;

        $this->syntaxHighlighter = $syntaxHighlighter;
    }

    /**
     *
     * @param  array  $data
     * @return string
     */
    public function draw(array $data)
    {
        $ret = '';

        if ($data['front']) {
            $lang = $this->fillParam($data, 'lang', '', true);

            $this->lang = $lang;
        } else {
            $classStr = ('' === $this->lang) ? '' : ' class="language-' . $this->e($this->lang) . '"';

            $ret = '<pre><code' . $classStr . '>' . $this->e($data['content']) . '</code></pre>';

            #$ret = $this->syntaxHighlighter->highlight($data['content'], $this->lang);

            $ret .= "\n\n";
        }

        return $ret;
    }
}
