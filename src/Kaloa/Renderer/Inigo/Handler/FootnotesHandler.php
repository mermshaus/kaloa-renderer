<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class FootnotesHandler extends ProtoHandler
{
    private $m_cnt;
    private $m_footnotes;

    public function __construct()
    {
        $this->name = 'fn|fnt';

        $this->type[0] = (Parser::TAG_INLINE | Parser::TAG_SINGLE);
        $this->type[1] = (Parser::TAG_OUTLINE | Parser::TAG_CLEAR_CONTENT);
    }

    public function draw(array $data)
    {
        if ($data['tag'] === 'fn' && $data['front']) {
            $this->m_cnt++;
            return '[' . $this->m_cnt . ']';
        } else if ($data['tag'] == 'fnt' && !$data['front']) {
            $this->m_footnotes[] = $data['content'];
        }
    }

    public function initialize()
    {
        $this->m_cnt = 0;
        $this->m_footnotes = array();
    }

    public function postProcess($s, array $data)
    {
        $ret = '';

        if (($data['tag'] === 'fnt') && ($this->m_cnt > 0)) {
            $ret .= '<ol>' . "\n";
            foreach ($this->m_footnotes as $f) {
                $ret .= '<li>' . $f . '</li>' . "\n";
            }
            $ret .= '</ol>';
        }

        return $s . $ret;
    }
}
