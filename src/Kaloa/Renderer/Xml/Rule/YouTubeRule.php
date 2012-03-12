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
<div class="blog_youtube_container" style="width: 400px; height: 300px;">
<object type="application/x-shockwave-flash"
        class="blog_youtube"
            style="display: block; width: 100%; height: 100%;"
        data="http://www.youtube.com/v/$id"
>
  <param name="movie"
         value="http://www.youtube.com/v/$id&amp;hl=en&amp;fs=0"
  />
</object>
</div>
EOT;

            $fragment->appendXML($xml);

            $parent->replaceChild($fragment, $node);
        }
    }
}
