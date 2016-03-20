<?php

namespace Kaloa\Tests;

use PHPUnit_Framework_TestCase;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class XmlLegacyRendererTest extends PHPUnit_Framework_TestCase
{
    public function testIntegrity()
    {
        // Environment
        #$contentToRender = file_get_contents(__DIR__ . '/examples/xmllegacy/arrayobject.xml');
        $contentToRender = file_get_contents(__DIR__ . '/examples/xmllegacy/youtube.xml');
        $resourceBasePath = __DIR__ . '/examples/xmllegacy';
        $filter = 'xmllegacy';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer($filter, $config);

        /* Simulate run of preSave hook */
        $contentToRender = $renderer->firePreSaveEvent($contentToRender);

        $output = $renderer->render($contentToRender);

        #var_dump($output);
    }
}
