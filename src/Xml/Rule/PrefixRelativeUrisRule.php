<?php

namespace Kaloa\Renderer\Xml\Rule;

use DOMElement;
use Kaloa\Renderer\Xml\Rule\AbstractRule;

/**
 *
 */
final class PrefixRelativeUrisRule extends AbstractRule
{
    /**
     *
     * @var string
     */
    private $prefix;

    /**
     *
     * @param string $prefix
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     *
     */
    public function postRender()
    {
        $imageNodes = $this->runXpathQuery('//img[@src]');

        foreach ($imageNodes as $node) {
            /* @var $node DOMElement */
            $src = (string) $node->getAttribute('src');

            if (0 === preg_match('~^https?://|/~', $src)) {
                $src = $this->prefix . $src;
                $node->setAttribute('src', $src);
            }
        }

        $aNodes = $this->runXpathQuery('//a[@href]');

        foreach ($aNodes as $node) {
            /* @var $node DOMElement */
            $href = (string) $node->getAttribute('href');

            if (0 === preg_match('~^(https?://|mailto:|#|/)~', $href)) {
                $href = $this->prefix . $href;
                $node->setAttribute('href', $href);
            }
        }
    }
}
