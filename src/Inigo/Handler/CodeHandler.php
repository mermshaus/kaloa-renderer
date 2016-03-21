<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;
use Kaloa\Renderer\SyntaxHighlighterInterface;

/**
 *
 */
final class CodeHandler extends ProtoHandler
{
    /**
     *
     * @var string
     */
    private $lang;

    /**
     *
     * @var SyntaxHighlighterInterface
     */
    private $syntaxHighlighter;

    /**
     *
     */
    public function __construct(SyntaxHighlighterInterface $syntaxHighlighter)
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
            $this->lang = $this->fillParam($data, 'lang', '', true);
        } else {
            $ret = $this->syntaxHighlighter->highlight($data['content'], $this->lang);
            $ret .= "\n\n";
        }

        return $ret;
    }
}
