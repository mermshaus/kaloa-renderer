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

        #$contentToRender = file_get_contents('./examples/inigo/klangbilder.txt');
        #$resourceBasePath = './examples/inigo/klangbilder';
        #$filter = 'inigo';

        #$contentToRender = file_get_contents('./examples/xmllegacy/arrayobject.xml');
        #$resourceBasePath = './examples/xmllegacy';
        #$filter = 'xmllegacy';

        $config = new Config();
        $config->setResourceBasePath($resourceBasePath);

        $renderer = Factory::createRenderer($config, $filter);

        /* Simulate run of preSave hook */
        $contentToRender = $renderer->firePreSaveEvent($contentToRender);

        $output = $renderer->render($contentToRender);

        #var_dump($output);
    }
}
