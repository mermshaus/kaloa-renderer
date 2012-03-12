<?php

namespace Kaloa\Renderer\Xml\Rule;

use DOMElement;
use Kaloa\Renderer\Xml\Rule\AbstractRule;
use Kaloa\Renderer\SyntaxHighlighter;

class ListingsRule extends AbstractRule
{
    protected $listingCount;

    protected $positionCache;

    protected $sh;

    public function __construct(SyntaxHighlighter $sh)
    {
        $this->sh = $sh;
    }

    public function init()
    {
        $this->listingCount = 0;
        $this->positionCache = array();
    }

    public function render()
    {
        foreach ($this->runXpathQuery('//listing') as $node) {
            /* @var $node DOMElement */
            $parent = $node->parentNode;


            $fragment = $this->document->createDocumentFragment();

            $language = (string) $node->getAttribute('language');
            $caption  = (string) $node->getAttribute('caption');

            $file = (string) $node->getAttribute('file');
            $from =  $node->getAttribute('from');
            $length = $node->getAttribute('length');

            if ($from === '') {
                $from = null;
            }

            if ($length === '') {
                $length = null;
            }

            $source = '';

            if ('' === $file) {
                $source = $node->nodeValue;
            } else {
                $resourceBasePath = $this->renderer
                        ->getConfig()->getResourceBasePath();
                $file = $resourceBasePath . '/' . $file;

                $lines = file($file, FILE_IGNORE_NEW_LINES);
                if ($from === null && $length === null) {
                    $from = 1;
                    $length = count($lines);
                } else if ($from !== null && $length === null) {
                    $length = count($lines) - $from + 1;
                } else if ($from === null && $length !== null) {
                    if (isset($this->positionCache[$file])) {
                        $from = $this->positionCache[$file];
                    } else {
                        $from = 1;
                    }
                }

                $source = implode("\n", array_splice($lines, $from - 1, $length));

                $this->positionCache[$file] = $from + $length;
            }

            // Smart trim code
            $source = preg_replace('/(?:\s*\n)?(.*)$/s', '$1', $source);
            $source = rtrim($source);

            if ($language === 'xsl' || $language === 'xslt') {
                $language = 'xml';
            }

            if ($language !== '') {
                $source = $this->shHighlight($source, $language);
            } else {
                $source = '<pre><span class="preformatted">' . $this->escape($source) . '</span></pre>';
            }

            $s = '<div>';

            if ($caption !== '') {
                $this->listingCount++;
                $s .= '<p class="caption">Listing ' . $this->listingCount . ': ' . $caption . '</p>';
            }

            $s .= $source;

            $s .= '</div>';

            $fragment->appendXML($s);

            $parent->replaceChild($fragment, $node);
        }
    }

    protected function shHighlight($source, $language)
    {
        /*$this->geshi->set_language($language);
        $this->geshi->set_source($source);

        $this->geshi->highlight_lines_extra(array(-1));*/

        $source = $this->sh->highlight($source, $language);

        #$source = $this->geshi->parse_code();

        /** @todo Otherwise, DOMDocument would warn about unknown 'nbsp' entities */
        //$source = str_replace('&nbsp;', ' ', $source);

        //$source = preg_replace('/^(<pre[^>]*>)(.*)(<\/pre>)$/ms', '$1<code>$2</code>$3', $source);


        // Post process Geshi output
        // Get stuff between <pre>...</pre>

        $preTop = preg_replace('/(<pre[^>]*>).*/s', '\\1', $source);
        $code = preg_replace('/<pre[^>]*>(.*)<\/pre>/s', '\\1', $source);

        $code = explode("\n", $code);
        $count = count($code);
        $parsed_code = '';

        for ($i = 0; $i < $count; $i++) {
            /** @todo mermshaus Downstream hack */
            if (true) {
                $c = 0;
                if ($code[$i] === '&nbsp;') {
                    // Empty line
                    $c = 6;
                } else {
                    while (substr($code[$i], $c, 1) === ' ') {
                        $c++;
                    }
                }

                /*$code[$i] = substr($code[$i], 0, $c)
                        . '<span style="display: block; background: #eee;" id="' . $geshi->overall_id . '-' . $i . '">'
                        . substr($code[$i], $c)
                        . '</span>';*/

                $class = '';
                if (in_array($i, array(5, 9, 12))) {
                    $class = 'line hl';
                } else {
                    $class = 'line';
                }

                $code[$i] = '<span class="'.$class.'" id="hic-svnt-dracones">'
                        . $code[$i]
                        . '</span>';

                /*$code[$i] = '<span class="'.$class.'" id="' . $geshi->overall_id . '-' . $i . '">'
                        . $code[$i]
                        . '</span>';*/
            }

            //$code[$i] = preg_replace_callback('/>(.*?)</s', function ($matches) { return '>' . str_replace(' ', '&nbsp;<wbr/>', $matches[1]) . '<'; }, $code[$i]);

            $parsed_code .= $code[$i] . "\n";
        }

        $parsed_code = $preTop . '<code>'
        . rtrim($parsed_code)
        . '</code></pre>';


        return $parsed_code;
    }
}
