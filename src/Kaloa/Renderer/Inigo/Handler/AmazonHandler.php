<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class AmazonHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'amazon';
        $this->type = Parser::TAG_OUTLINE;
    }

    private function DrawBox($asin, $title, $author)
    {
        $img = 'http://images.amazon.com/images/P/' . $asin
                . '.01._SCMZZZZZZZ_V65020934_.jpg';

        $ret = '';
        $ret .= '<div class="amazon clear">';
        $ret .= '<div class="img-border">';
        $ret .= '<div class="img" style="background-image: url(' . $img . ');"></div>';
        $ret .= '</div>';
        $ret .= '<p class="title"><strong>' . $title . '</strong></p>';
        $ret .= '<p class="author">' . $author . '</p>';
        $ret .= '</div>';

        return $ret;
    }

    public function draw(array $data)
    {
        $ret = '';

        if ($data['front']) {
            $asin   = $data['params']['asin'];
            $title  = $data['params']['title'];
            $author = $data['params']['author'];

            $ret = $this->DrawBox($asin, $title, $author);
        }

        return $ret;
    }
}
