<?php

namespace Kaloa\Tests;

use PHPUnit_Framework_TestCase;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class XmlRendererTest extends PHPUnit_Framework_TestCase
{
    public function testIntegrity()
    {
        // Environment
        $contentToRender = file_get_contents(__DIR__ . '/examples/xml/mvc.xml');
        $resourceBasePath = __DIR__ . '/examples/xml/mvc';
        $filter = 'xml';

        #$contentToRender = file_get_contents('./examples/xml/kaloa_renderer.xml');
        #$resourceBasePath = './examples/xml/kaloa_renderer';
        #$filter = 'xml';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer($filter, $config);

        /* Simulate run of preSave hook */
        $contentToRender = $renderer->firePreSaveEvent($contentToRender);

        $renderer->render($contentToRender);
    }
}
