<?php

namespace Kaloa\Renderer\Inigo;

use Kaloa\Renderer\Inigo\Parser;

class Tag
{
    protected $m_name;
    protected $m_attributes;
    protected $m_isClosingTag;
    protected $m_rawData;
    protected $m_inigo;

    /**
     *
     * @param string $s
     * @param Parser $inigo
     */
    public function __construct($s, Parser $inigo)
    {
        $this->m_rawData      = $s;
        $this->m_inigo        = $inigo;
        $this->m_name         = $this->_extractTagName($s);
        $this->m_attributes   = $this->_extractTagAttributes($s);
        $this->m_isClosingTag = $this->_extractIsClosingTag($s);
    }

    /**
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->m_rawData;
    }

    /**
     *
     * @param  string $s
     * @return bool
     */
    private static function _extractIsClosingTag($s)
    {
        $s = trim(mb_substr($s, 1, mb_strlen($s) - 2));
        return (mb_substr($s, 0, 1) === '/');
    }

    /**
     *
     * @param  string $s
     * @return array
     */
    protected static function _extractTagAttributes($s)
    {
        $ret = array();

        $s = str_replace('&quot;', '"', $s);
        $s = str_replace("\\\"", '&quot;', $s);

        $s = trim(mb_substr($s, 1, mb_strlen($s) - 2));

        $i = mb_strpos($s, ' ');
        $j = mb_strpos($s, '=');
        if ((!($j === false)) && (($j < $i) || ($i === false))) {
            $i = $j;
        }

        if ($i === false) {
            return false;
        }

        $s = trim(mb_substr($s, $i));

        if (mb_substr($s, 0, 1) == '=') {
            //if
            $t = ltrim(mb_substr($s, 1));
            if (mb_substr($t, 0, 1) == '"') {
                $i = mb_strpos($s, '"');
                $j = mb_strpos($s, '"', $i + 1);
                $ret['(default)'] = mb_substr($s, $i + 1, $j - ($i + 1));
                $s = trim(mb_substr($s, $j + 1));
            } else {
                $i = mb_strpos($t, ' ');
                if ($i === false) {
                    $ret['(default)'] = trim($t);
                    $s = trim(mb_substr($t, $i + 1));
                } else {
                    $ret['(default)'] = mb_substr($t, 0, $i);
                    $s = trim(mb_substr($t, $i + 1));
                }
            }
        }

        $i = mb_strpos($s, '=');
        while (!($i === false)) {
            $j = mb_strpos($s, '"');
            $k = mb_strpos($s, '"', $j + 1);
            if (($k > -1) && (mb_substr($s, $k - 1, 1) == '\\')) {
                $k = mb_strpos($s, '"', $k + 1);
            }
            $ret[trim(mb_substr($s, 0, $i))] = mb_substr($s, $j + 1, $k - ($j + 1));
            $s = trim(mb_substr($s, $k + 1));
            $i = mb_strpos($s, '=');
        }

        return $ret;
    }

    /**
     *
     *
     * @param unknown_type $s
     * @return unknown
     */
    private static function _extractTagName($s)
    {
        if (trim($s) == '') {
            return '';
        }
        $s = trim(mb_substr($s, 1, mb_strlen($s) - 2));
        if (mb_substr($s, 0, 1) == '/') {
            $s = mb_substr($s, 1);
        }
        $i = mb_strpos($s, ' ');
        $j = mb_strpos($s, '=');
        if ((!($j === false)) && (($j < $i) || ($i === false))) {
            $i = $j;
        }
        return ($i === false) ? $s : mb_substr($s, 0, $i);
    }

    /**
     * Returns the tag name from a valid tag string
     * ("[tag=http://.../]" => "tag")
     *
     * - Might be done easier using RegExps
     */
    public function getName()
    {
        return $this->m_name;
    }

    /**
     *
     * @return unknown
     */
    public function getAttributes()
    {
        return $this->m_attributes;
    }

    /**
     *
     * @return bool
     */
    public function isClosingTag()
    {
        return $this->m_isClosingTag;
    }

    /**
     *
     * @return bool
     */
    public function isOfType($tagType)
    {
        $ret  = false;
        $b    = false;
        $i    = 0;

        $tags = $this->m_inigo->getHandlers();
        $cnt  = count($tags);

        while (!$b && $i < $cnt) {
            if ($this->m_name == $tags[$i]['name']) {
                if ($tags[$i]['type'] & $tagType) {
                    $ret = true;
                }

                $b = true;
            } else {
                $i++;
            }
        }

        return $ret;
    }

    /**
     *
     * @return bool
     */
    public function isValid()
    {
        $b    = false;
        $i    = 0;

        $tags = $this->m_inigo->getHandlers();
        $cnt  = count($tags);

        while (!$b && $i < $cnt) {
            if ($this->m_name == $tags[$i]['name']) {
                $b = true;
            } else {
                $i++;
            }
        }

        return $b;
    }
}
