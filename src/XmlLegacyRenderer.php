<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use DOMDocument;
use XSLTProcessor;

final class XmlLegacyRenderer implements RendererInterface
{
    private static self $myself;

    private XSLTProcessor|null $xsltProcessor = null;

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;

        self::$myself = $this;

        $this->initXsltProcessor();
    }

    private function initXsltProcessor(): void
    {
        $filepath = __DIR__ . '/xmllegacy.xsl';

        $xsl = file_get_contents($filepath);

        if (!is_string($xsl)) {
            throw new \RuntimeException(
                sprintf('Unable to read %s', $filepath)
            );
        }

        $xsl = str_replace('__CLASS__', __CLASS__, $xsl);

        $xslDoc = new DOMDocument();
        $xslDoc->loadXML($xsl);

        $xsltProcessor = new XSLTProcessor();
        $xsltProcessor->registerPHPFunctions();
        $xsltProcessor->importStylesheet($xslDoc);

        $this->xsltProcessor = $xsltProcessor;
    }

    public function render(string $input): string
    {
        $xmlCode = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root [
<!ENTITY nbsp "&#160;">
]>';

        $xmlCode .= '<root>' . $input . '</root>';

        $xmldoc = new DOMDocument();
        $xmldoc->loadXML($xmlCode);

        $tmp = $this->xsltProcessor->transformToDoc($xmldoc);

        if (!$tmp instanceof DOMDocument) {
            throw new \RuntimeException('Unable to run XSLT');
        }

        $htmlCandidate = $tmp->saveHTML($tmp->documentElement);

        if (!is_string($htmlCandidate)) {
            throw new \RuntimeException('Unable to saveHTML');
        }

        $html = substr($htmlCandidate, 6, -7);

        return $html;
    }

    public static function imageUrl(string $path): string
    {
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }

        return self::$myself->config->getResourceBasePath() . '/' . $path;
    }

    public static function linkUrl(string $path): string
    {
        if (preg_match('/^(https?:\/\/|mailto:)/', $path)) {
            return $path;
        }

        return self::$myself->config->getResourceBasePath() . '/' . $path;
    }

    /**
     * @todo OMG this method can't be efficient
     */
    public static function highlight(
        string $source,
        string $language
    ): \DOMElement {
        // Smart trim code
        /*$source = preg_replace('/(?:\s*\n)?(.*)$/s', '$1', $source);
        $source = rtrim($source);*/

        $parsed_code = self::$myself->config->getSyntaxHighlighter()->highlight(
            $source,
            $language
        );

        $parsed_code = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE root [
<!ENTITY nbsp "&#160;">
]>' . $parsed_code;

        $tmp = new DOMDocument();

        $tmp->loadXML($parsed_code);

        return $tmp->documentElement;
    }
}
