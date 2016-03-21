<?php

namespace Kaloa\Renderer;

use DOMDocument;
use Kaloa\Renderer\Xml\Rule\AbstractRule;

/**
 *
 */
final class XmlRenderer implements RendererInterface
{
    private $rules = array();

//    /**
//     * Converts all HTML entities outside of CDATA elements to their corresponding
//     * UTF-8 characters
//     *
//     * @param string $xmlString
//     * @return string
//     */
//    private function decodeEntitiesFromXml($xmlString)
//    {
//        $retVal = '';
//
//        $parts = preg_split(
//            '/(<!\[CDATA\[.*?\]\]>)/s',
//            $xmlString,
//            -1,
//            PREG_SPLIT_DELIM_CAPTURE
//        );
//
//        foreach ($parts as $part) {
//            if (strpos($part, '<![CDATA[') === 0) {
//                $retVal .= $part;
//            } else {
//                $retVal .= html_entity_decode($part, ENT_QUOTES, 'UTF-8');
//            }
//        }
//
//        return $retVal;
//    }

    /**
     *
     * http://www.php.net/manual/en/domdocument.savexml.php#95252
     *
     * @param string $xml
     * @return string
     */
    private function xml2xhtml($xml)
    {
        return preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', create_function('$m', '
            $xhtml_tags = array("br", "hr", "input", "frame", "img",
                "area", "link", "col", "base", "basefont", "param");
            return in_array($m[1], $xhtml_tags) ? "<$m[1]$m[2] />" : "<$m[1]$m[2]></$m[1]>";
        '), $xml);
    }

    /**
     *
     * @param AbstractRule $rule
     * @param int $weight
     */
    public function registerRule(AbstractRule $rule, $weight = 0)
    {
        if (!isset($this->rules[$weight])) {
            $this->rules[$weight] = array();
        }

        $this->rules[$weight][] = $rule;

        krsort($this->rules);
    }

    /**
     *
     * @param string $xmlCode
     * @return string
     */
    public function render($xmlCode)
    {
        $xmlCode = '<root xmlns:k="lalalala">' . $xmlCode . '</root>';

        $xmldoc = new DOMDocument('1.0', 'UTF-8');
        $xmldoc->resolveExternals = true;

        $xmldoc->loadXML($xmlCode);

        foreach ($this->rules as $weight => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->setDocument($xmldoc);
                $rule->init();
                $rule->preRender();
            }
        }

        foreach ($this->rules as $weight => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->render();
            }
        }

        foreach ($this->rules as $weight => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->postRender();
            }
        }

        $s = preg_replace('!<root[^>]*>(.*)</root>!s', '$1', $xmldoc->saveXML($xmldoc->documentElement));

        // Handle self-closing tags
        $s = $this->xml2xhtml($s);

        return $s;
    }

    /**
     *
     * @param string $xmlCode
     * @return string
     */
    public function firePreSaveEvent($xmlCode)
    {
        $xmlCode = '<root xmlns:k="lalalala">' . $xmlCode . '</root>';

        $xmldoc = new DOMDocument('1.0', 'UTF-8');
        $xmldoc->loadXML($xmlCode);

        foreach ($this->rules as $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->setDocument($xmldoc);
                $rule->init();
                $rule->preSave();
            }
        }

        return preg_replace('!<root[^>]*>(.*)</root>!s', '$1', $xmldoc->saveXML($xmldoc->documentElement));
    }
}
