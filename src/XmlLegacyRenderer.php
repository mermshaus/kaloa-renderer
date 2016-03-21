<?php

namespace Kaloa\Renderer;

use DOMDocument;
use XSLTProcessor;

/**
 *
 */
final class XmlLegacyRenderer implements RendererInterface
{
    /**
     *
     * @var XmlLegacyRenderer
     */
    private static $myself;

    /**
     *
     * @var XSLTProcessor
     */
    private $xsltProcessor = null;

    /**
     *
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;

        self::$myself = $this;

        $this->initXsltProcessor();
    }

    /**
     *
     */
    private function initXsltProcessor()
    {
        $xsl = file_get_contents(__DIR__ . '/xmllegacy.xsl');

        $xsl = str_replace('__CLASS__', __CLASS__, $xsl);

        $xslDoc = new DOMDocument();
        $xslDoc->loadXML($xsl);

        $xsltProcessor = new XSLTProcessor();
        $xsltProcessor->registerPHPFunctions();
        $xsltProcessor->importStyleSheet($xslDoc);

        $this->xsltProcessor = $xsltProcessor;
    }

    /**
     *
     * @param  string $input
     * @return string
     */
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

        $html = $tmp->saveHTML($tmp->documentElement);
        $html = substr($html, 6, -7);

        return $html;
    }

    /**
     *
     * @param  string $path
     * @return string
     */
    public static function imageUrl($path)
    {
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }

        return self::$myself->config->getResourceBasePath() . '/' . $path;
    }

    /**
     *
     * @param  string $path
     * @return string
     */
    public static function linkUrl($path)
    {
        if (preg_match('/^(https?:\/\/|mailto:)/', $path)) {
            return $path;
        }

        return self::$myself->config->getResourceBasePath() . '/' . $path;
    }

    /**
     *
     * @todo OMG this method can't be efficient
     *
     * @param  string $source
     * @param  string $language
     * @return DOMElement?
     */
    public static function highlight($source, $language)
    {
        // Smart trim code
        $source = preg_replace('/(?:\s*\n)?(.*)$/s', '$1', $source);
        $source = rtrim($source);


        $source = self::$myself->config->getSyntaxHighlighter()->highlight($source, $language);

        $parsed_code = $source;

        $parsed_code = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root [
<!ENTITY nbsp "&#160;">
]>' . $parsed_code;

        $tmp = new DOMDocument();

        $tmp->loadXML($parsed_code);
        return $tmp->documentElement;
    }
}
