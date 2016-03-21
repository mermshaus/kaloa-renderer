<?php

namespace Kaloa\Tests;

use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;
use Kaloa\Renderer\Inigo\Handler\AbbrHandler;
use Kaloa\Renderer\Inigo\Parser;
use PHPUnit_Framework_TestCase;

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

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer($filter, $config);

        $output = $renderer->render($contentToRender);

        $expected = file_get_contents(__DIR__ . '/examples/inigo/klangbilder.expected');

        $expected = str_replace('__RESOURCE_BASE_PATH__', $config->getResourceBasePath(), $expected);

        $this->assertEquals($expected, $output);
    }

    public function runSuiteProvider()
    {
        $sets = array();

        foreach (glob(__DIR__ . '/examples/inigo/*.txt') as $file) {
            $sets[] = array(
                realpath($file),
                realpath(substr($file, 0, -4) . '.expected')
            );
        }

        return $sets;
    }

    /**
     * @dataProvider runSuiteProvider
     */
    public function testRunSuite($fileInput, $fileExpected)
    {
        $resourceBasePath = __DIR__ . '/examples/inigo/res';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer('inigo', $config);

        $output   = $renderer->render(file_get_contents($fileInput));
        $expected = file_get_contents($fileExpected);

        $expected = str_replace('__RESOURCE_BASE_PATH__', $config->getResourceBasePath(), $expected);

        $this->assertEquals($expected, $output);
    }
}
