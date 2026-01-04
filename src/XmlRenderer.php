<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use DOMDocument;
use Kaloa\Renderer\Xml\Rule\AbstractRule;

final class XmlRenderer implements RendererInterface
{
    /** @var array<int, list<AbstractRule>> */
    private array $rules = [];

    //    /**
    //     * Converts all HTML entities outside CDATA elements to their corresponding
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
     * http://www.php.net/manual/en/domdocument.savexml.php#95252
     */
    private function xml2xhtml(string $xml): string
    {
        return preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', function ($m) {
            $xhtml_tags = array(
                'br',
                'hr',
                'input',
                'frame',
                'img',
                'area',
                'link',
                'col',
                'base',
                'basefont',
                'param'
            );

            return in_array($m[1], $xhtml_tags) ? "<$m[1]$m[2] />"
                : "<$m[1]$m[2]></$m[1]>";
        }, $xml);
    }

    public function registerRule(AbstractRule $rule, int $weight = 0): void
    {
        if (!isset($this->rules[$weight])) {
            $this->rules[$weight] = [];
        }

        $this->rules[$weight][] = $rule;

        krsort($this->rules);
    }

    public function render(string $input): string
    {
        $xmlCode = $input;

        $xmlCode = '<root xmlns:k="lalalala">' . $xmlCode . '</root>';

        $xmldoc = new DOMDocument('1.0', 'UTF-8');
        $xmldoc->resolveExternals = true;

        $xmldoc->loadXML($xmlCode);

        foreach ($this->rules as $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->setDocument($xmldoc);
                $rule->init();
                $rule->preRender();
            }
        }

        foreach ($this->rules as $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->render();
            }
        }

        foreach ($this->rules as $rulesArray) {
            foreach ($rulesArray as $rule) {
                $rule->postRender();
            }
        }

        $xml = $xmldoc->saveXML($xmldoc->documentElement);
        if (!is_string($xml)) {
            throw new \RuntimeException('XML document could not be generated.');
        }
        $s = preg_replace('!<root[^>]*>(.*)</root>!s', '$1', $xml);

        // Handle self-closing tags
        $s = $this->xml2xhtml($s);

        return $s;
    }

    public function firePreSaveEvent(string $xmlCode): string
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

        $xml = $xmldoc->saveXML($xmldoc->documentElement);
        if (!is_string($xml)) {
            throw new \RuntimeException('XML document could not be generated.');
        }
        return preg_replace('!<root[^>]*>(.*)</root>!s', '$1', $xml);
    }
}
