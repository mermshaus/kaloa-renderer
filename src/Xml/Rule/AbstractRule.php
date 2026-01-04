<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Xml\Rule;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

abstract class AbstractRule
{
    private DOMDocument $document;

    public function init(): void
    {
        // nop
    }

    public function preSave(): void
    {
        // nop
    }

    public function setDocument(DOMDocument $document): void
    {
        $this->document = $document;
    }

    protected function getDocument(): DOMDocument
    {
        return $this->document;
    }

    public function preRender(): void
    {
        // nop
    }

    public function render(): void
    {
        // nop
    }

    public function postRender(): void
    {
        // nop
    }

    protected function escape(
        string $string,
        int $flags = ENT_QUOTES,
        string $charset = 'UTF-8'
    ): string {
        return htmlspecialchars($string, $flags, $charset);
    }

    /**
     * @return array<DOMElement>
     */
    protected function runXpathQuery(
        string $xpathExpression,
        ?DOMNode $contextNode = null,
        bool $documentOrdered = true
    ): array {
        $xp = new DOMXPath($this->document);
        $xp->registerNamespace('k', 'lalalala');
        $nodeList = $xp->query($xpathExpression, $contextNode);

        if ($nodeList === false) {
            throw new \RuntimeException(
                sprintf('Error in xpath query: %s', $xpathExpression)
            );
        }

        $arrayList = [];

        foreach ($nodeList as $node) {
            $arrayList[] = $node;
        }

        /* Shall the result be ordered?
         *
         * As far as I can tell from web sources, a NodeList returned by
         * DOMXPath is not necessarily in document order. So we need to sort it
         * on our own. This code iterates depth first over the DOM tree while
         * checking every node against the node list returned by XPath. If two
         * nodes are equal, delete the node from the XPath result set and add it
         * to a new (now sorted) list.
         *
         * Unfortunately, this is not the most efficient thing to do.
         */
        if ($documentOrdered) {
            $newList = [];

            // Traverse the tree
            $rec = function ($node) use (&$rec, &$newList, &$arrayList) {
                /* @var DOMElement $node */

                foreach ($arrayList as $index => $compareNode) {
                    if ($node->isSameNode($compareNode)) {
                        $newList[] = $node;
                        unset($arrayList[$index]);
                        break;
                    }
                }

                if ($node->hasChildNodes()) {
                    foreach ($node->childNodes as $child) {
                        $rec($child);
                    }
                }
            };

            $rec($this->document);
            $arrayList = $newList;
        }

        return $arrayList;
    }

    /**
     * @see http://www.php.net/manual/en/class.domelement.php#86803
     */
    protected function getInnerXml(DOMElement $elem): string
    {
        $innerHtml = '';

        foreach ($elem->childNodes as $child) {
            $tmp_doc = new DOMDocument('1.0', 'UTF-8');
            $tmp_doc->appendChild($tmp_doc->importNode($child, true));

            $tmp = $tmp_doc->saveXML();

            if (!is_string($tmp)) {
                throw new \RuntimeException('Unable to generate XML');
            }

            $tmp = preg_replace('/<\?xml[^>]*>\n/', '', $tmp);
            $tmp = rtrim($tmp, "\n");

            $innerHtml .= $tmp;
        }

        return $innerHtml;
    }
}
