<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
final class AmazonHandler extends ProtoHandler
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'amazon';
        $this->type = Parser::TAG_OUTLINE;
    }

    /**
     *
     * @param  string $asin
     * @param  string $title
     * @param  string $author
     * @return string
     */
    private function drawBox($asin, $title, $author)
    {
        $img = 'http://images.amazon.com/images/P/' . $this->e($asin)
                . '.01._SCMZZZZZZZ_V65020934_.jpg';

        $ret = '<div class="amazon clear">'
                . '<div class="img-border">'
                . '<div class="img" style="background-image: url(' . $this->e($img) . ');"></div>'
                . '</div>'
                . '<p class="title"><strong>' . $this->e($title) . '</strong></p>'
                . '<p class="author">' . $this->e($author) . '</p>'
                . '</div>';

        return $ret;
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
            $asin   = $this->fillParam($data, 'asin', '');
            $title  = $this->fillParam($data, 'title', '');
            $author = $this->fillParam($data, 'author', '');

            $ret = $this->drawBox($asin, $title, $author);
        }

        return $ret;
    }
}
