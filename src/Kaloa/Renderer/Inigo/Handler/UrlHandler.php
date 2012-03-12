<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class UrlHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'url|link';
        $this->type = Parser::TAG_INLINE;
    }

    public function draw(array $data)
    {
        if ($data['front']) {
            if (isset($data['params']['(default)'])) {
                $href = $data['params']['(default)'];
            } else if (isset($data['params']['href'])) {
                $href = $data['params']['href'];
            }

            $href = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');

            if (isset($data['params']['title'])) {
                $title = ' title="' . $data['params']['title'] . '"';
            } else {
                $title = ' title="Open &quot;' . $href . '&quot;"';
            }

            return '<a href="' . $href . '"' . $title . '>';
        } else {
            return '</a>';
        }
    }
}
