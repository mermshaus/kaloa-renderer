<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Parser;

final class UrlHandler extends ProtoHandler
{
    public function __construct()
    {
        $this->name = 'url|link';
        $this->type = Parser::TAG_INLINE;
        $this->defaultParam = 'href';
    }

    public function draw(array $data): string
    {
        $ret = '';

        if ($data['front']) {
            $href = $this->fillParam($data, 'href', '');

            $title = $this->fillParam($data, 'title', null);

            if ($title !== null) {
                $title = ' title="' . $this->e($title) . '"';
            } else {
                $title = ' title="Open &quot;' . $this->e($href) . '&quot;"';
            }

            $ret = '<a href="' . $this->e($href) . '"' . $title . '>';
        } else {
            $ret = '</a>';
        }

        return $ret;
    }
}
