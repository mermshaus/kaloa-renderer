<?php

namespace Kaloa\Renderer;

use DOMDocument;
use Kaloa\Renderer\AbstractRenderer;
use Kaloa\Renderer\Xml\Rule\AbstractRule;

/**
 *
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class XmlRenderer extends AbstractRenderer
{
    protected $rules = array();

    /**
     * Converts all HTML entities outside of CDATA elements to their corresponding
     * UTF-8 characters
     *
     * @param string $xmlString
     * @return string
     */
    protected function decodeEntitiesFromXml($xmlString)
    {
        $retVal = '';

        $parts = preg_split('/(<!\[CDATA\[.*?\]\]>)/s', $xmlString, -1,
                PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $part) {
            if (strpos($part, '<![CDATA[') === 0) {
                $retVal .= $part;
            } else {
                $retVal .= html_entity_decode($part, ENT_QUOTES, 'UTF-8');
            }
        }

        return $retVal;
    }

    public function registerRule(AbstractRule $rule, $weight = 0)
    {
        if (!isset($this->rules[$weight])) {
            $this->rules[$weight] = array();
        }

        $this->rules[$weight][] = $rule;

        krsort($this->rules);
    }

    public function render($xmlCode)
    {
        $xmlCode = '<root xmlns:k="lalalala">' . $xmlCode . '</root>';

        $xmldoc = new DOMDocument('1.0', 'UTF-8');
        $xmldoc->resolveExternals = true;

        $xmldoc->loadXML($xmlCode);

        foreach ($this->rules as $weight => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->setDocument($xmldoc);
                $rule->setRenderer($this);
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

        return preg_replace('!<root[^>]*>(.*)</root>!s', '$1', $xmldoc->saveXML($xmldoc->documentElement));
    }

    public function firePreSaveEvent($xmlCode)
    {
        $xmlCode = '<root xmlns:k="lalalala">' . $xmlCode . '</root>';

        $xmldoc = new DOMDocument('1.0', 'UTF-8');
        $xmldoc->loadXML($xmlCode);

        foreach ($this->rules as $weight => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->setDocument($xmldoc);
                $rule->setRenderer($this);
                $rule->init();
                $rule->preSave();
            }
        }

        return preg_replace('!<root[^>]*>(.*)</root>!s', '$1', $xmldoc->saveXML($xmldoc->documentElement));
    }
}
