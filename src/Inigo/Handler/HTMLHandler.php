<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
final class HTMLHandler extends ProtoHandler
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'html';
        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE | Parser::TAG_CLEAR_CONTENT;
    }

    /**
     *
     * @param  array  $data
     * @return string
     */
    public function draw(array $data)
    {
        $ret = '';

        if (!$data['front']) {
            $ret = $data['content'] . "\n\n";
        }

        return $ret;
    }
}
