<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class AbbrHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'abbr';
        $this->type = Parser::TAG_INLINE;
    }

    public function draw(array $data)
    {
        $ret = '';

        if ($data['front']) {
            $title = '';

            if (isset($data['params']['(default)'])) {
                $title = $data['params']['(default)'];
            } else if (isset($data['params']['title'])) {
                $title = $data['params']['title'];
            }

            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

            if ($title !== '') {
                $ret = '<abbr title="' . $title . '">';
            } else {
                $ret = '<abbr>';
            }
        } else {
            $ret = '</abbr>';
        }

        return $ret;
    }
}
