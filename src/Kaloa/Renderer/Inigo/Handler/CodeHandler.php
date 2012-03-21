<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class CodeHandler extends ProtoHandler
{
    /**
     *
     * @var string
     */
    protected $lang;

    /**
     *
     */
    public function __construct()
    {
        $this->name = 'code';

        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE
                | Parser::TAG_CLEAR_CONTENT;
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
            $lang = $this->fillParam($data, 'lang', '', true);

            $this->lang = $lang;

            $ret = '<pre>';
        } else {
            $ret = $data['content'];

            $ret .= '</pre>' . "\n\n";
        }

        return $ret;
    }
}
