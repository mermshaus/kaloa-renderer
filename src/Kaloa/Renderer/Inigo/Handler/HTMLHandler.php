<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class HTMLHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'html';
        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE | Parser::TAG_CLEAR_CONTENT;
    }

    public function draw(array $data)
    {
        if ($data['front']) {

        } else {
            return $data['content'];
        }
    }
}
