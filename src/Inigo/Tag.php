<?php

namespace Kaloa\Renderer\Inigo;

/**
 *
 */
final class Tag
{
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var array
     */
    private $attributes;

    /**
     *
     * @var boolean
     */
    private $isClosingTag;

    /**
     *
     * @var string
     */
    private $rawData;

    /**
     *
     * @var boolean
     */
    private $isValid;

    /**
     *
     * @var array
     */
    private $myHandler;

    /**
     *
     * @param string $tagString
     * @param array $handlers
     */
    public function __construct($tagString, array $handlers)
    {
        $this->rawData = $tagString;
        $this->name    = $this->extractTagName($tagString);

        $this->myHandler = null;

        foreach ($handlers as $handler) {
            if ($handler['name'] === $this->name) {
                $this->myHandler = $handler;
                break;
            }
        }

        $this->isValid      = (null !== $this->myHandler);
        $this->isClosingTag = ('/' === mb_substr($tagString, 1, 1));
        $this->attributes   = $this->extractTagAttributes($tagString);
    }

    /**
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Returns the tag name from a valid tag string
     *
     * "[tag=http://.../]" => "tag"
     * "[/TAG]"            => "tag"
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     *
     * @return boolean
     */
    public function isClosingTag()
    {
        return $this->isClosingTag;
    }

    /**
     *
     * @return boolean
     */
    public function isOfType($tagType)
    {
        return ($this->isValid && $this->myHandler['type'] & $tagType);
    }

    /**
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     *
     *
     * @param string $tagString
     * @return string
     */
    private function extractTagName($tagString)
    {
        $name       = '';
        $tagPattern = '[A-Za-z][A-Za-z0-9_]*';
        $matches    = array();

        if (1 === preg_match('/\A\[(' . $tagPattern . ')[\s=\]]/', $tagString, $matches)) {
            $name = strtolower($matches[1]);
        } elseif (1 === preg_match('/\A\[\/(' . $tagPattern . ')\s*\]\z/', $tagString, $matches)) {
            $name = strtolower($matches[1]);
        }

        return $name;
    }

    /**
     *
     * @param  string $tagString
     * @return array
     */
    private function extractTagAttributes($tagString)
    {
        if (false === $this->isValid) {
            return array();
        }

        $ret = array();

        $tagString = str_replace('&quot;', '"', $tagString);
        $tagString = str_replace("\\\"", '&quot;', $tagString);

        $tagString = trim(mb_substr($tagString, 1, mb_strlen($tagString) - 2));

        $i = mb_strpos($tagString, ' ');
        $j = mb_strpos($tagString, '=');
        if ((!($j === false)) && (($j < $i) || ($i === false))) {
            $i = $j;
        }

        if ($i === false) {
            return false;
        }

        $tagString = trim(mb_substr($tagString, $i));

        if (mb_substr($tagString, 0, 1) == '=') {
            //if
            $t = ltrim(mb_substr($tagString, 1));
            if (mb_substr($t, 0, 1) == '"') {
                $i = mb_strpos($tagString, '"');
                $j = mb_strpos($tagString, '"', $i + 1);
                $ret['__default'] = mb_substr($tagString, $i + 1, $j - ($i + 1));
                $tagString = trim(mb_substr($tagString, $j + 1));
            } else {
                $i = mb_strpos($t, ' ');
                if ($i === false) {
                    $ret['__default'] = trim($t);
                    $tagString = trim(mb_substr($t, $i + 1));
                } else {
                    $ret['__default'] = mb_substr($t, 0, $i);
                    $tagString = trim(mb_substr($t, $i + 1));
                }
            }
        }

        $i = mb_strpos($tagString, '=');
        while (!($i === false)) {
            $j = mb_strpos($tagString, '"');
            $k = mb_strpos($tagString, '"', $j + 1);
            if (($k > -1) && (mb_substr($tagString, $k - 1, 1) == '\\')) {
                $k = mb_strpos($tagString, '"', $k + 1);
            }
            $ret[trim(mb_substr($tagString, 0, $i))] = mb_substr($tagString, $j + 1, $k - ($j + 1));
            $tagString = trim(mb_substr($tagString, $k + 1));
            $i = mb_strpos($tagString, '=');
        }

        $retNew = array();
        foreach ($ret as $key => $value) {
            $retNew[strtolower($key)] = $value;
        }
        $ret = $retNew;
        unset($retNew);

        if (isset($ret['__default'])) {
            $defaultParam = $this->myHandler['function']->defaultParam;

            if (!isset($ret[$defaultParam])) {
                $ret[$defaultParam] = $ret['__default'];
            }

            unset($ret['__default']);
        }

        ksort($ret);

        return $ret;
    }
}
