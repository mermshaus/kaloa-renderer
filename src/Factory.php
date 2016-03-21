<?php

namespace Kaloa\Renderer;

use Exception;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Xml\Rule\FootnotesRule;
use Kaloa\Renderer\Xml\Rule\ListingsRule;
use Kaloa\Renderer\Xml\Rule\PrefixRelativeUrisRule;
use Kaloa\Renderer\Xml\Rule\TocRule;
use Kaloa\Renderer\Xml\Rule\YouTubeRule;

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
            case 'commonmark':
                $renderer = new CommonMarkRenderer($config);
                break;
            case 'inigo':
                $renderer = new InigoRenderer($config);
                break;
            case 'markdown':
                $renderer = new MarkdownRenderer($config);
                break;
            case 'xml':
                $renderer = new XmlRenderer();
                $renderer->registerRule(new TocRule());
                $renderer->registerRule(new YouTubeRule());
                $renderer->registerRule(new ListingsRule());
                $renderer->registerRule(new PrefixRelativeUrisRule());
                $renderer->registerRule(new FootnotesRule());
                break;
            case 'xmllegacy':
                $renderer = new XmlLegacyRenderer($config);
                break;
            default:
                throw new Exception('Unknown renderer "' . $type . '"');
                // no break
        }

        return $renderer;
    }
}
