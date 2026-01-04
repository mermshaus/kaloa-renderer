<?php

namespace Kaloa\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;

class XmlLegacyRendererTest extends TestCase
{
    public function testIntegrity(): void
    {
        // Environment
        #$contentToRender = file_get_contents(__DIR__ . '/examples/xmllegacy/arrayobject.xml');
        $contentToRender = file_get_contents(__DIR__ . '/examples/xmllegacy/youtube.xml');
        $resourceBasePath = __DIR__ . '/examples/xmllegacy';
        $filter = 'xmllegacy';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer($filter, $config);

        $output = $renderer->render($contentToRender);

        self::assertIsString($output);
        self::assertNotEmpty($output);
    }
}
