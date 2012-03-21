<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class UrlHandler extends ProtoHandler
{
    /**
     *
     */
    public function __construct()
    {
        $this->name = 'url|link';
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
            $href = $this->fillParam($data, 'href', '', true);
            $href = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');

            $title = $this->fillParam($data, 'title', null);

            if ($title !== null) {
                $title = ' title="' . $title . '"';
            } else {
                $title = ' title="Open &quot;' . $href . '&quot;"';
            }

            $ret = '<a href="' . $href . '"' . $title . '>';
        } else {
            $ret = '</a>';
        }

        return $ret;
    }
}
