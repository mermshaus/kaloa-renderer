<?php

namespace Kaloa\Renderer\Xml\Rule;

use Kaloa\Renderer\Xml\Rule\AbstractRule;

/**
 *
 */
final class TocRule extends AbstractRule
{
    private $config;

    /**
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $configDefault = array(
            'idFormat' => 'h:%s'
        );

        $this->config = array_merge($configDefault, $config);
    }

    /**
     *
     */
    public function preRender()
    {
        if (count($this->runXpathQuery('//k:toc')) > 0) {
            $this->createToc();
        }
    }

    /**
     *
     */
    private function createToc()
    {
        $curDepth = 2;
        $i = 0;
        $toc = '';

        $toc .= "<ul>\n";
        foreach ($this->runXpathQuery('//h2|//h3|//h4|//h5|//h6') as $node) {
            $thisDepth = (int) substr($node->nodeName, 1);

            $identifier = (string) $node->getAttribute('id');

            if ('' === $identifier) {
                // No id set, generate a reasonable one
                $tmp = $node->nodeValue;
                $tmp = mb_strtolower($tmp);
                $tmp = str_replace(' ', '-', $tmp);
                $tmp = preg_replace('/[^\p{L}0-9-]/u', '', $tmp);
                $identifier = mb_substr($tmp, 0, 30);
                $identifier = rtrim($identifier, '-');
            }

            $identifierFormatted = sprintf(
                $this->config['idFormat'],
                $identifier
            );

            $node->setAttribute('id', $identifierFormatted);

            if ($thisDepth > $curDepth) {
                $toc .= "\n".str_repeat('  ', $thisDepth - 1)."<ul>\n";
            } elseif ($i > 0) {
                $toc .= "</li>\n";
            }

            while ($thisDepth < $curDepth) {
                $toc .= str_repeat('  ', $curDepth - 1) . "</ul>\n</li>\n";
                $curDepth--;
            }

            $toc .= str_repeat('  ', $thisDepth) . '<li>';
            $toc .= '<a href="#' . $identifierFormatted . '">';
            $toc .= $this->getInnerXml($node);
            $toc .= '</a>';

            $curDepth = $thisDepth;
            $i++;
        }

        $toc .= "</li>\n</ul>\n";
        while ($curDepth > 2) {
            $toc .= str_repeat('  ', $curDepth - 1) . "</li>\n</ul>\n";
            $curDepth--;
        }

        $tocFragment = $this->getDocument()->createDocumentFragment();
        $tocFragment->appendXML($toc);

        foreach ($this->runXpathQuery('//k:toc') as $node) {
            $parent = $node->parentNode;
            $parent->replaceChild($tocFragment->cloneNode(true), $node);
        }
    }
}
