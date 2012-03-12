<?php

namespace Kaloa\Renderer\Xml\Rule;

use DOMDocument;
use DOMXPath;
use Kaloa\Renderer\XmlRenderer;

abstract class AbstractRule
{
    /** @var DOMDocument */
    protected $document;

    /** @var XmlRenderer */
    protected $renderer;

    public function init()
    {

    }

    public function preSave()
    {

    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function setDocument(DOMDocument $document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function preRender()
    {
    }

    public function render()
    {
    }

    public function postRender()
    {
    }

    protected function escape($string, $flags = ENT_QUOTES, $charset = 'UTF-8')
    {
        return htmlspecialchars($string, $flags, $charset);
    }

    /**
     *
     * @param <type> $xpathExpression
     * @param <type> $contextNode
     * @return array
     */
    protected function runXpathQuery($xpathExpression, $contextNode = null, $documentOrdered = true)
    {
        $xp = new DOMXPath($this->document);
        $xp->registerNamespace('k', 'lalalala');
        $nodeList = $xp->query($xpathExpression, $contextNode);

        $arrayList = array();

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
            $newList = array();

            // Traverse the tree
            $rec = function ($node) use (&$rec, &$newList, &$arrayList) {
                /* @var $node DOMElement */

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
     * @param mixed $elem
     */
    protected function getInnerXml($elem)
    {
        $innerHtml = '';

        foreach ($elem->childNodes as $child) {
            $tmp_doc = new DOMDocument('1.0', 'UTF-8');
            $tmp_doc->appendChild($tmp_doc->importNode($child, true));

            $tmp = $tmp_doc->saveXML();
            $tmp = preg_replace('/<\?xml[^>]*>\n/', '', $tmp);
            $tmp = rtrim($tmp, "\n");

            $innerHtml .= $tmp;
        }

        return $innerHtml;
    }
}
