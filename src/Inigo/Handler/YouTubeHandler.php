<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
final class YouTubeHandler extends ProtoHandler
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'youtube';
        $this->type = Parser::TAG_OUTLINE;
        $this->defaultParam = 'id';
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
            $vid = $this->fillParam($data, 'id', '');

            $ret .= '<div class="videoWrapper">' . "\n";

            $ret .= '  <iframe width="560" height="349" src="https://www.youtube.com/embed/'
                . $this->e($vid) . '" frameborder="0">';
        } else {
            $ret .= '</iframe>' . "\n" . '</div>' . "\n\n";
        }

        return $ret;
    }
}
