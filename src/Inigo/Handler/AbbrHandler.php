<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class AbbrHandler extends ProtoHandler
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'abbr';
        $this->type = Parser::TAG_INLINE;
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
            $title = $this->fillParam($data, 'title', '', true);

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
