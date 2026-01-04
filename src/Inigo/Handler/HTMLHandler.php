<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Parser;

final class HTMLHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'html';
        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE
            | Parser::TAG_CLEAR_CONTENT;
    }

    public function draw(array $data): string
    {
        $ret = '';

        if (!$data['front']) {
            if (!isset($data['content'])) {
                throw new \RuntimeException('No content given.');
            }

            $ret = $data['content'] . "\n\n";
        }

        return $ret;
    }
}
