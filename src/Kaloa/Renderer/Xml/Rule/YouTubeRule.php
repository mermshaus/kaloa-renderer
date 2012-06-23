<?php

namespace Kaloa\Renderer\Xml\Rule;

use DOMDocument;
use Kaloa\Renderer\Xml\Rule\AbstractRule;

class YouTubeRule extends AbstractRule
{
    public function render()
    {
        foreach ($this->runXpathQuery('//youtube') as $node) {
            /* @var $node DOMElement */
            $parent = $node->parentNode;

            $fragment = $this->document->createDocumentFragment();

            $id = $node->getAttribute('id');

            $xml = <<<EOT
<div class="videoWrapper">
<iframe src="http://www.youtube.com/embed/$id" frameborder="0"></iframe>
</div>
EOT;

            $fragment->appendXML($xml);

            $parent->replaceChild($fragment, $node);
        }
    }
}
