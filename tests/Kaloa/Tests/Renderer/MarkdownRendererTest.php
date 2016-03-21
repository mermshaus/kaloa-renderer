<?php

namespace Kaloa\Tests;

use Kaloa\Renderer\Factory;
use PHPUnit_Framework_TestCase;

class MarkdownRendererTest extends PHPUnit_Framework_TestCase
{
    public function testIntegrity()
    {
        $renderer = Factory::createRenderer('markdown');
        $output = $renderer->render('# Hello World!');

        $this->assertEquals("<h1>Hello World!</h1>\n", $output);
    }

    public function basicParserProvider()
    {
        $sets = array();

        foreach (glob(__DIR__ . '/examples/markdown/*.text') as $file) {
            $sets[] = array(
                realpath($file),
                realpath(substr($file, 0, -5) . '.xhtml')
            );
        }

        return $sets;
    }

    /**
     * @dataProvider basicParserProvider
     */
    public function testBasicParser($fileInput, $fileExpected)
    {
        $renderer = Factory::createRenderer('markdown');

        $output = $renderer->render(file_get_contents($fileInput));

        $expected = file_get_contents($fileExpected);

        $output   = str_replace(array("\r\n", "\r"), "\n", $output);
        $expected = str_replace(array("\r\n", "\r"), "\n", $expected);

        $this->assertEquals($expected, $output);
    }
}
