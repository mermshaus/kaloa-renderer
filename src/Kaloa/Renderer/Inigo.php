<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\AbstractRenderer;

use Inigo_Parser;
use Inigo_Handler_Simple;
use Inigo_Handler_Url;
use Inigo_Handler_Img;
use Inigo_Handler_Amazon;
use Inigo_Handler_Abbr;
use Inigo_Handler_HTML;
use Inigo_Handler_Code;
use Inigo_Handler_Footnotes;
use Inigo_Handler_YouTube;

/**
 *
 */
class Inigo extends AbstractRenderer
{
    /** @var Inigo_Parser */
    protected $inigoParser = null;

    protected function init()
    {
        $this->initInigoParser();
    }

    protected function initInigoParser()
    {
        #KaloaToolkit_MBString::language('uni');
        #KaloaToolkit_MBString::internalEncoding('UTF-8');

        $this->inigoParser = new Inigo_Parser();
        $inigo = $this->inigoParser;

        // TODO Change this path to some useful implementation
        //$inigo->addSetting('image-dir', 'http://' . $_SERVER['HTTP_HOST'] . $this->kaloaConfig->getSetting(KaloaConfig::S_SITE_DOCROOT) . 'img/');
        $inigo->addSetting('image-dir', $this->config->getResourceBasePath() . '/');
        $inigo->addSetting('quisp-dir', '');
        //

        /* Example for multiple tags being displayed in the same way */
        $inigo->addHandler(new Inigo_Handler_Simple('b|strong', Inigo_Parser::TAG_INLINE, '<strong>', '</strong>'))
              ->addHandler(new Inigo_Handler_Simple('i|em', Inigo_Parser::TAG_INLINE, '<em>', '</em>'))

        /*
         * Used to display other tags. Tags with type Inigo_Parser::TAG_PRE will not be parsed
         * This tag belongs also to two types
         */
              ->addHandler(new Inigo_Handler_Simple('off', Inigo_Parser::TAG_INLINE | Inigo_Parser::TAG_PRE, '', ''))

              ->addHandler(new Inigo_Handler_Simple('u', Inigo_Parser::TAG_INLINE, '<span class="underline">', '</span>'))
              ->addHandler(new Inigo_Handler_Simple('var', Inigo_Parser::TAG_INLINE | Inigo_Parser::TAG_PRE, '<var>', '</var>'))
              ->addHandler(new Inigo_Handler_Simple('strike', Inigo_Parser::TAG_INLINE, '<span class="strike">', '</span>'))
              ->addHandler(new Inigo_Handler_Simple('quote', Inigo_Parser::TAG_OUTLINE | Inigo_Parser::TAG_FORCE_PARAGRAPHS, '<blockquote>', "</blockquote>\n\n"))

              /* Most replacements are rather simple */
              ->addHandler(new Inigo_Handler_Simple("h1", Inigo_Parser::TAG_OUTLINE, "<h1>", "</h1>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("h2", Inigo_Parser::TAG_OUTLINE, "<h2>", "</h2>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("h3", Inigo_Parser::TAG_OUTLINE, "<h3>", "</h3>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("h4", Inigo_Parser::TAG_OUTLINE, "<h4>", "</h4>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("h5", Inigo_Parser::TAG_OUTLINE, "<h5>", "</h5>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("h6", Inigo_Parser::TAG_OUTLINE, "<h6>", "</h6>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("dl", Inigo_Parser::TAG_OUTLINE, "<dl>", "\n\n</dl>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("dt", Inigo_Parser::TAG_OUTLINE, "\n\n<dt>", "</dt>"))
              ->addHandler(new Inigo_Handler_Simple("dd", Inigo_Parser::TAG_OUTLINE, "\n<dd>", "</dd>"))
              ->addHandler(new Inigo_Handler_Simple("ul", Inigo_Parser::TAG_OUTLINE, "<ul>", "\n</ul>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("ol", Inigo_Parser::TAG_OUTLINE, "<ol>", "\n</ol>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("li", Inigo_Parser::TAG_OUTLINE, "\n<li>", "</li>"))
              ->addHandler(new Inigo_Handler_Simple("table", Inigo_Parser::TAG_OUTLINE, "<table cellspacing=\"0\">", "\n</table>\n\n"))
              ->addHandler(new Inigo_Handler_Simple("tr", Inigo_Parser::TAG_OUTLINE, "\n<tr>", "\n</tr>"))
              ->addHandler(new Inigo_Handler_Simple("td", Inigo_Parser::TAG_OUTLINE, "\n<td>", "</td>"))
              ->addHandler(new Inigo_Handler_Simple("th", Inigo_Parser::TAG_OUTLINE, "\n<th>", "</th>"))

              ->addHandler(new Inigo_Handler_Url())
              ->addHandler(new Inigo_Handler_Img())
              ->addHandler(new Inigo_Handler_Amazon())
              ->addHandler(new Inigo_Handler_Abbr())
              ->addHandler(new Inigo_Handler_HTML())
              ->addHandler(new Inigo_Handler_Code())
              ->addHandler(new Inigo_Handler_Footnotes())
              ->addHandler(new Inigo_Handler_YouTube());
    }

    /**
     *
     * @param Blog_Model_Entry $entry
     * @return string
     */
    public function render($input)
    {
        return $this->inigoParser->Parse($input);
    }
}
