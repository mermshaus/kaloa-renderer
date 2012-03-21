<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class FootnotesHandler extends ProtoHandler
{
    /**
     *
     * @var int
     */
    protected $cnt;

    /**
     *
     * @var array
     */
    protected $footnotes;

    /**
     *
     */
    public function __construct()
    {
        $this->name = 'fn|fnt';

        $this->type[0] = (Parser::TAG_INLINE | Parser::TAG_SINGLE);
        $this->type[1] = (Parser::TAG_OUTLINE | Parser::TAG_CLEAR_CONTENT);
    }

    /**
     *
     * @param  array  $data
     * @return string
     */
    public function draw(array $data)
    {
        $ret = '';

        if ($data['tag'] === 'fn' && $data['front']) {
            $this->cnt++;
            $ret = '[' . $this->cnt . ']';
        } else if ($data['tag'] === 'fnt' && !$data['front']) {
            $this->footnotes[] = $data['content'];
        }

        return $ret;
    }

    /**
     *
     */
    public function initialize()
    {
        $this->cnt = 0;
        $this->footnotes = array();
    }

    /**
     *
     * @param  string $s
     * @param  array  $data
     * @return string
     */
    public function postProcess($s, array $data)
    {
        $ret = '';

        if (($data['tag'] === 'fnt') && ($this->cnt > 0)) {
            $ret .= '<ol>' . "\n";
            foreach ($this->footnotes as $f) {
                $ret .= '<li>' . $f . '</li>' . "\n";
            }
            $ret .= '</ol>';
        }

        return $s . $ret;
    }
}
