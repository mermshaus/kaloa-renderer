<?php

namespace Kaloa\Renderer;

use Exception;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Inigo;
use Kaloa\Renderer\Xml\Rule\FootnotesRule;
use Kaloa\Renderer\Xml\Rule\ListingsRule;
use Kaloa\Renderer\Xml\Rule\PrefixRelativeUrisRule;
use Kaloa\Renderer\Xml\Rule\TocRule;
use Kaloa\Renderer\Xml\Rule\YouTubeRule;
use Kaloa\Renderer\XmlLegacyRenderer;
use Kaloa\Renderer\XmlRenderer;

/**
 *
 * @api
 */
final class Factory
{
    /**
     *
     * @param string $type
     * @param Config $config
     * @return RendererInterface
     * @throws Exception
     */
    public static function createRenderer($type, Config $config = null)
    {
        $renderer = null;

        if (null === $config) {
            $config = new Config();
        }

        switch ($type) {
            case 'inigo':
                $renderer = new Inigo($config);
                break;
            case 'xml':
                $renderer = new XmlRenderer($config);
                $renderer->registerRule(new TocRule());
                $renderer->registerRule(new YouTubeRule());
                $renderer->registerRule(new ListingsRule($config->getSyntaxHighlighter()));
                $renderer->registerRule(new PrefixRelativeUrisRule());
                $renderer->registerRule(new FootnotesRule());
                break;
            case 'xmllegacy':
                $renderer = new XmlLegacyRenderer($config);
                break;
            case 'markdown':
                $renderer = new MarkdownRenderer($config);
                break;
            default:
                throw new Exception('Unknown renderer "' . $type . '"');
                // no break
        }

        return $renderer;
    }
}
