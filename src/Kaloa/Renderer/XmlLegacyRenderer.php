<?php

namespace Kaloa\Renderer;

use DOMDocument;
use XSLTProcessor;
use Kaloa\Renderer\AbstractRenderer;

class XmlLegacyRenderer extends AbstractRenderer
{
    protected static $myself;

    /** @var XSLTProcessor */
    protected $xsltProcessor = null;

    protected function init()
    {
        self::$myself = $this;

        $this->initXsltProcessor();
    }

    protected function initXsltProcessor()
    {
        $className = __CLASS__;

        $xsl = <<<EOT
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl">
<xsl:output method="xml" encoding="UTF-8" indent="no"/>

<xsl:template match="youtube">
<div class="blog_youtube_container">
    <object type="application/x-shockwave-flash"
            class="blog_youtube"
            data="http://www.youtube.com/v/{@id}"
    >
      <param name="movie"
             value="http://www.youtube.com/v/{@id}&amp;hl=en&amp;fs=0"
      />
    </object>
</div>
</xsl:template>

<xsl:template match="code">
    <xsl:copy-of select="php:function('$className::highlight', string(.), string(@lang))" />
</xsl:template>

<xsl:template match="img/@src">
    <xsl:attribute name="src">
        <xsl:copy-of select="php:function('$className::imageUrl', string(.))" />
    </xsl:attribute>
</xsl:template>

<xsl:template match="a/@href">
    <xsl:attribute name="href">
        <xsl:copy-of select="php:function('$className::linkUrl', string(.))" />
    </xsl:attribute>
</xsl:template>

<!-- the identity template -->
<xsl:template match="@*|node()">
  <xsl:copy>
    <xsl:apply-templates select="@*|node()"/>
  </xsl:copy>
</xsl:template>

</xsl:stylesheet>
EOT;

        $xslDoc = new DOMDocument();
        $xslDoc->loadXML($xsl);

        $xsltProcessor = new XSLTProcessor();
        $xsltProcessor->registerPHPFunctions();
        $xsltProcessor->importStyleSheet($xslDoc);

        $this->xsltProcessor = $xsltProcessor;
    }

    public function render($input)
    {
        $xmlCode = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root [
<!ENTITY nbsp "&#160;">
]>';

        $xmlCode .= '<root>' . $input . '</root>';

        $xmldoc = new DOMDocument();
        $xmldoc->loadXML($xmlCode);

        $tmp = $this->xsltProcessor->transformToDoc($xmldoc);

        return substr($tmp->saveXML($tmp->documentElement), 6, -7);
    }

    public static function imageUrl($path)
    {
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }

        return self::$myself->config->getResourceBasePath() . '/'
               . $path;
    }

    public static function linkUrl($path)
    {
        if (preg_match('/^(https?:\/\/|mailto:)/', $path)) {
            return $path;
        }

        return self::$myself->config->getResourceBasePath() . '/'
               . $path;
    }

    /**
     *
     *
     * @todo OMG this method can't be efficient
     *
     * @param string $source
     * @param string $lang
     * @return DOMElement?
     */
    public static function highlight($source, $language)
    {
        #$geshi = new Geshi_Parser();

        #$geshi->overall_class = 'geshi';
        #$geshi->keyword_links = false;
        #$geshi->overall_id = 'test';

        #$geshi->enable_classes();
        #$geshi->enable_ids();

        // Smart trim code
        $source = preg_replace('/(?:\s*\n)?(.*)$/s', '$1', $source);
        $source = rtrim($source);

        #$geshi->set_language($language);
        #$geshi->set_source($source);

        #$geshi->highlight_lines_extra(array(-1));


        #$source = $geshi->parse_code();

        $source = '<pre>' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8') . '</pre>';

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

                #$code[$i] = '<span class="'.$class.'" id="' . $geshi->overall_id . '-' . $i . '">'
                #        . $code[$i]
                #        . '</span>';
            }

            $code[$i] = preg_replace_callback('/>(.*?)</s', function ($matches) { return '>' . str_replace(' ', '&nbsp;<wbr/>', $matches[1]) . '<'; }, $code[$i]);

            $parsed_code .= $code[$i] . "\n";
        }

        $parsed_code = $preTop . '<code>'
        . rtrim($parsed_code)
        . '</code></pre>';

        $parsed_code = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root [
<!ENTITY nbsp "&#160;">
]>' . $parsed_code;


        $tmp = new DOMDocument();

        $tmp->loadXML($parsed_code);
        return $tmp->documentElement;
    }
}
