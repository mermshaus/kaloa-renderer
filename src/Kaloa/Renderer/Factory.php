<?php

namespace Kaloa\Renderer;

use Exception;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Inigo;
use Kaloa\Renderer\XmlRenderer;
use Kaloa\Renderer\Xml\Rule\TocRule;
use Kaloa\Renderer\Xml\Rule\YouTubeRule;
use Kaloa\Renderer\Xml\Rule\ListingsRule;
use Kaloa\Renderer\Xml\Rule\PrefixRelativeUrisRule;
use Kaloa\Renderer\Xml\Rule\FootnotesRule;
use Kaloa\Renderer\XmlLegacyRenderer;
use Kaloa\Renderer\SyntaxHighlighter;

class Factory
{
    public static function createRenderer(Config $config = null, $type = 'xml')
    {
        $renderer = null;

        if ($config === null) {
            $config = new Config();
        }

        switch ($type) {
            case 'inigo':
                $renderer = new Inigo($config);
                break;
            case 'xml':
                $sh = new SyntaxHighlighter();

                /*$geshi->overall_class = 'geshi';
                $geshi->keyword_links = false;
                $geshi->overall_id = 'test';

                $geshi->enable_classes();
                $geshi->enable_ids();*/

                $renderer = new XmlRenderer($config);
                $renderer->registerRule(new TocRule());
                $renderer->registerRule(new YouTubeRule());
                $renderer->registerRule(new ListingsRule($sh));
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
                break;
        }

        return $renderer;
    }
}
