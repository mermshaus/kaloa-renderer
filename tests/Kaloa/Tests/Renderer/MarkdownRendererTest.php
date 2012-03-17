<?php

namespace Kaloa\Tests;

use PHPUnit_Framework_TestCase;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class MarkdownRendererTest extends PHPUnit_Framework_TestCase
{
    public function testIntegrity()
    {
        // Environment
        $contentToRender = file_get_contents(__DIR__ . '/examples/markdown/Auto Links.text');
        $resourceBasePath = __DIR__ . '/examples/markdown';
        $filter = 'markdown';

        $config = new Config();
        $config->setResourceBasePath($resourceBasePath);

        $renderer = Factory::createRenderer($config, $filter);

        /* Simulate run of preSave hook */
        $contentToRender = $renderer->firePreSaveEvent($contentToRender);

        $output = $renderer->render($contentToRender);
    }

    public function testBasicParser()
    {
        foreach (glob(__DIR__ . '/examples/markdown/*.text') as $mdFile) {
            // Environment
            $contentToRender = file_get_contents($mdFile);
            $resourceBasePath = __DIR__ . '/examples/markdown';
            $filter = 'markdown';

            $config = new Config();
            $config->setResourceBasePath($resourceBasePath);

            $renderer = Factory::createRenderer($config, $filter);

            /* Simulate run of preSave hook */
            $contentToRender = $renderer->firePreSaveEvent($contentToRender);

            $output = $renderer->render($contentToRender);

            #var_dump('blaaaaaaaa' . $output);

            $expected = file_get_contents(substr($mdFile, 0, -5) . '.xhtml');

            $output = str_replace(array("\r\n", "\r"), "\n", $output);
            $expected = str_replace(array("\r\n", "\r"), "\n", $expected);

            $this->assertEquals($expected, $output, $mdFile);
        }
    }
}
