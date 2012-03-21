<?php

namespace Kaloa\Tests;

use PHPUnit_Framework_TestCase;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

use Kaloa\Renderer\Inigo\Parser;
use Kaloa\Renderer\Inigo\Handler\AbbrHandler;
use Kaloa\Renderer\Inigo\Handler\FootnotesHandler;

/**
 *
 */
class InigoRendererTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testIntegrity()
    {
        // Environment
        $contentToRender = file_get_contents(__DIR__ . '/examples/inigo/klangbilder.txt');
        $resourceBasePath = __DIR__ . '/examples/inigo/res';
        $filter = 'inigo';

        $config = new Config();
        $config->setResourceBasePath($resourceBasePath);

        $renderer = Factory::createRenderer($config, $filter);

        /* Simulate run of preSave hook */
        $contentToRender = $renderer->firePreSaveEvent($contentToRender);

        $output = $renderer->render($contentToRender);

        $expected = file_get_contents(__DIR__ . '/examples/inigo/klangbilder.expected');

        $expected = str_replace('__RESOURCE_BASE_PATH__', $config->getResourceBasePath(), $expected);

        $this->assertEquals($expected, $output);
    }

    /**
     *
     */
    public function testAbbr()
    {
        $parser = new Parser();
        $parser->addHandler(new AbbrHandler());

        $this->assertEquals(
                '<p><abbr title="PHP&gt;=5.4">Traits</abbr></p>',
                $parser->Parse('[abbr="PHP>=5.4"]Traits[/abbr]'));

        $this->assertEquals(
                '<p><abbr title="PHP&gt;=5.4">Traits</abbr></p>',
                $parser->Parse('[abbr title="PHP>=5.4"]Traits[/abbr]'));

        $this->assertEquals(
                '<p><abbr>Traits</abbr></p>',
                $parser->Parse('[abbr]Traits[/abbr]'));
    }

    /**
     *
     */
    public function testRunSuite()
    {
        foreach (glob(__DIR__ . '/examples/inigo/*.txt') as $mdFile) {
            // Environment
            $contentToRender = file_get_contents($mdFile);
            $resourceBasePath = __DIR__ . '/examples/inigo/res';
            $filter = 'inigo';

            $config = new Config();
            $config->setResourceBasePath($resourceBasePath);

            $renderer = Factory::createRenderer($config, $filter);

            /* Simulate run of preSave hook */
            $contentToRender = $renderer->firePreSaveEvent($contentToRender);

            $output   = $renderer->render($contentToRender);
            $expected = file_get_contents(substr($mdFile, 0, -4) . '.expected');

            $output   = str_replace(array("\r\n", "\r"), "\n", $output);
            $expected = str_replace(array("\r\n", "\r"), "\n", $expected);

            $expected = str_replace('__RESOURCE_BASE_PATH__', $config->getResourceBasePath(), $expected);

            $this->assertEquals($expected, $output, $mdFile);
        }
    }
}
