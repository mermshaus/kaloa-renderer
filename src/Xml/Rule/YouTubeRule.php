<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Xml\Rule;

final class YouTubeRule extends AbstractRule
{
    public function render(): void
    {
        foreach ($this->runXpathQuery('//youtube') as $node) {
            /* @var $node \DOMElement */
            $parent = $node->parentNode;

            $fragment = $this->getDocument()->createDocumentFragment();

            $id = $node->getAttribute('id');

            $xml = <<<EOT
<div class="videoWrapper">
<iframe src="https://www.youtube.com/embed/$id" frameborder="0"></iframe>
</div>
EOT;

            $fragment->appendXML($xml);

            $parent->replaceChild($fragment, $node);
        }
    }
}
