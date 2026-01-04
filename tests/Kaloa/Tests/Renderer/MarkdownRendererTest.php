<?php

namespace Kaloa\Tests\Renderer;

use Kaloa\Renderer\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MarkdownRendererTest extends TestCase
{
    public function testIntegrity(): void
    {
        $renderer = Factory::createRenderer('markdown');
        $output = $renderer->render('# Hello World!');

        $this->assertEquals("<h1>Hello World!</h1>\n", $output);
    }

    public static function basicParserProvider(): array
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

    #[DataProvider('basicParserProvider')]
    public function testBasicParser(string $fileInput, string $fileExpected): void
    {
        $renderer = Factory::createRenderer('markdown');

        $output = $renderer->render(file_get_contents($fileInput));

        $expected = file_get_contents($fileExpected);

        $output   = str_replace(array("\r\n", "\r"), "\n", $output);
        $expected = str_replace(array("\r\n", "\r"), "\n", $expected);

        $this->assertEquals($expected, $output);
    }
}
