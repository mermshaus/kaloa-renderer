<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class YouTubeHandler extends ProtoHandler
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'youtube';
        $this->type = Parser::TAG_OUTLINE;
    }

    /**
     *
     * @param  array  $data
     * @return string
     */
    public function draw(array $data)
    {
        $ret = '';

//        if ($data['front']) {
//            $vid = $this->fillParam($data, 'id', '', true);
//
//            $ret .= '<div class="blog_youtube_container">';
//
//            $ret .=  '<object type="application/x-shockwave-flash"
//                              class="blog_youtube" data="http://www.youtube.com/v/' . $vid . '">';
//            $ret .= '  <param name="movie" value="http://www.youtube.com/v/' . $vid . '&amp;hl=en&amp;fs=0" />';
//
//            $ret .= '</object>';
//        } else {
//            $ret .= '</div>';
//        }

        if ($data['front']) {
            $vid = $this->fillParam($data, 'id', '', true);

            $ret .= '<div class="videoWrapper">' . "\n";

            $ret .= '  <iframe width="560" height="349" src="http://www.youtube.com/embed/' . $this->e($vid) . '" frameborder="0">';

        } else {
            $ret .= '</iframe>' . "\n" . '</div>' . "\n\n";
        }

        return $ret;
    }
}
