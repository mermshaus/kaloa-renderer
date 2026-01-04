<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Parser;
use Kaloa\Renderer\SyntaxHighlighterInterface;

final class CodeHandler extends ProtoHandler
{
    private string $lang;

    private SyntaxHighlighterInterface $syntaxHighlighter;

    public function __construct(SyntaxHighlighterInterface $syntaxHighlighter)
    {
        $this->name = 'code';

        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE
                | Parser::TAG_CLEAR_CONTENT;

        $this->syntaxHighlighter = $syntaxHighlighter;

        $this->defaultParam = 'lang';
    }

    public function draw(array $data): string
    {
        $ret = '';

        if ($data['front']) {
            $this->lang = $this->fillParam($data, 'lang', '');
        } else {
            if (!isset($data['content'])) {
                throw new \RuntimeException('No content given.');
            }

            $ret = $this->syntaxHighlighter->highlight($data['content'], $this->lang);
            $ret .= "\n\n";
        }

        return $ret;
    }
}
