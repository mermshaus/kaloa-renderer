<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use Kaloa\Renderer\Xml\Rule\FootnotesRule;
use Kaloa\Renderer\Xml\Rule\ListingsRule;
use Kaloa\Renderer\Xml\Rule\PrefixRelativeUrisRule;
use Kaloa\Renderer\Xml\Rule\TocRule;
use Kaloa\Renderer\Xml\Rule\YouTubeRule;

final class Factory
{
    public static function createRenderer(
        string $type,
        ?Config $config = null
    ): RendererInterface {
        $renderer = null;

        if ($config === null) {
            $config = new Config();
        }

        switch ($type) {
            case 'commonmark':
                $renderer = new CommonMarkRenderer();
                break;
            case 'inigo':
                $renderer = new InigoRenderer($config);
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
                throw new \Exception('Unknown renderer "' . $type . '"');
        }

        return $renderer;
    }
}
