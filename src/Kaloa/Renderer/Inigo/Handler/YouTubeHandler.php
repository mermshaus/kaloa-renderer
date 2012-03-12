<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class YouTubeHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'youtube';
        $this->type = Parser::TAG_OUTLINE;
    }

    public function draw(array $data)
    {
        $ret = '';

        if ($data['front']) {
            $vid = '';

            if (isset($data['params']['(default)'])) {
                $vid = $data['params']['(default)'];
            } else if (isset($data['params']['id'])) {
                $vid = $data['params']['id'];
            }

            $ret .= '<div class="blog_youtube_container">';

            $ret .=  '<object type="application/x-shockwave-flash" class="blog_youtube" data="http://www.youtube.com/v/' . $vid . '">';
            $ret .= '  <param name="movie" value="http://www.youtube.com/v/' . $vid . '&amp;hl=en&amp;fs=0" />';

            $ret .= '</object>';
        } else {
            $ret .= '</div>';
        }

        return $ret;
    }
}
