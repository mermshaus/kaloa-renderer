<?php

namespace Kaloa\Renderer\Xml\Rule;

use DOMElement;
use Kaloa\Renderer\Xml\Rule\AbstractRule;

/**
 *
 */
final class FootnotesRule extends AbstractRule
{
    /**
     *
     * @var array
     */
    private $config;

    /**
     *
     * @var array
     */
    private $footnotes;

    /**
     *
     * @var array
     */
    private $randomIdStore;

    /**
     *
     * @var array
     */
    private $usedIdentifiers;

    /**
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $configDefault = array(
            'footnoteIdFormat'       => 'fn:%s',
            'footnoteRefIdFormat'    => 'fnref:%s',
            'useRandomIdsNotNumbers' => true
        );

        $this->config = array_merge($configDefault, $config);
    }

    /**
     *
     */
    public function init()
    {
        $this->footnotes       = array();
        $this->randomIdStore   = array();
        $this->usedIdentifiers = array();
    }

    /**
     *
     */
    public function preSave()
    {
        if ($this->config['useRandomIdsNotNumbers']) {
            // Generate random footnote identifier for all footnotes without name
            // attribute
            foreach ($this->runXpathQuery('//footnote[not(@name)]') as $node) {
                $node->setAttribute(
                    'name',
                    $this->generateRandomFootnoteIdentifier()
                );
            }
        }
    }

    /**
     *
     * @return string
     */
    private function generateRandomFootnoteIdentifier()
    {
        $letters = range('a', 'z');

        do {
            $randomId = '';
            for ($i = 0; $i < 5; $i++) {
                $randomId .= $letters[mt_rand(0, 25)];
            }
        } while (in_array($randomId, $this->randomIdStore));

        $this->randomIdStore[] = $randomId;

        return $randomId;
    }

    /**
     *
     */
    public function render()
    {
        foreach ($this->runXpathQuery('//footnote') as $node) {
            $parent = $node->parentNode;

            /* @var $node DOMElement */

            $fragment = $this->getDocument()->createDocumentFragment();

            $identifier = (string) $node->getAttribute('name');
            $text = $this->getInnerXml($node);

            if ($identifier === '') {
                // This should only happen when a footnote has no name
                // attribute or when a name was already taken
                $identifier = count($this->footnotes) + 1;
            }

            $i = 0;
            $identifierClean = '';
            while (in_array($identifier, $this->usedIdentifiers)) {
                if ($i === 0) {
                    $identifier = count($this->footnotes) + 1;
                    $identifierClean = $identifier;
                } else {
                    $identifier = $identifierClean . '-' . $i;
                }
                $i++;
            }

            $this->usedIdentifiers[] = $identifier;

            $this->footnotes[] = array(
                'identifier' => $identifier,
                'text'       => $text
            );

            $number = count($this->footnotes);

            $id = sprintf($this->config['footnoteIdFormat'], $identifier);
            $idRef = sprintf($this->config['footnoteRefIdFormat'], $identifier);

            $xml = <<<EOT
<span id="$idRef"><a href="#$id">[$number]</a></span>
EOT;

            $fragment->appendXML($xml);

            $parent->replaceChild($fragment, $node);
        }
    }

    /**
     *
     */
    public function postRender()
    {
        if (count($this->footnotes) === 0) {
            return;
        }

        $fragment = $this->getDocument()->createDocumentFragment();

        $i = 1;
        $xml = '';

        $xml .= '<ol class="footnotes">';
        foreach ($this->footnotes as $element) {
            $identifier = $element['identifier'];
            $text       = $element['text'];

            $id    = sprintf($this->config['footnoteIdFormat'], $identifier);
            $idRef = sprintf($this->config['footnoteRefIdFormat'], $identifier);

            $xml .= '<li id="' . $id . '">';
            $xml .= $text;
            $xml .= ' <a href="#'.$idRef.'">'."\xE2\x86\x91".'</a>';
            $xml .= '</li>';

            $i++;
        }
        $xml .= '</ol>';

        $fragment->appendXML($xml);

        $this->getDocument()->documentElement->appendChild($fragment);
    }
}
