<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Parser;

final class QuoteHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'quote';
        $this->type = Parser::TAG_OUTLINE | Parser::TAG_FORCE_PARAGRAPHS;
        $this->defaultParam = 'author';
    }

    public function draw(array $data): string
    {
        $ret = '';

        if ($data['front']) {
            $author = $this->fillParam($data, 'author', '');

            if ('' !== $author) {
                $ret .= '<p>' . $this->e($author) . ':</p>' . "\n\n";
            }

            $ret .= '<blockquote>' . "\n";
        } else {
            $ret .= '</blockquote>' . "\n\n";
        }

        return $ret;
    }
}
