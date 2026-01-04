<?php

namespace Kaloa\Tests\Renderer;

use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;
use PHPUnit\Framework\TestCase;

class XmlRendererTest extends TestCase
{
    public function testIntegrity(): void
    {
        // Environment
        $contentToRender = file_get_contents(__DIR__ . '/examples/xml/mvc.xml');
        $resourceBasePath = __DIR__ . '/examples/xml/mvc';

        #$contentToRender = file_get_contents('./examples/xml/kaloa_renderer.xml');
        #$resourceBasePath = './examples/xml/kaloa_renderer';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer('xml', $config);

        /* Simulate run of preSave hook */
        $contentToRender = $renderer->firePreSaveEvent($contentToRender);

        $output = $renderer->render($contentToRender);

        self::assertIsString($output);
        self::assertNotEmpty($output);

        $ctr = '<k:toc/>
            <h2>Test</h2>
            <h3>Foo</h3>
            <img src="test.png" />
            <img src="http://test.png" />
            <a href="foo">link</a>
            <a href="https://example.org">link</a>
            <listing>1+1=2</listing>
            <h4>Bar</h4>
            <youtube id="dQw4w9WgXcQ" />
            <p>Test.<footnote>bla</footnote></p>
            <h2>Quz</h2>
            <p>Qux.<footnote><strong>Test</strong></footnote></p>
            <p>Qix.<footnote name="x">Qix</footnote></p>
            <p>Qaz.<footnote name="x">Qaz</footnote></p>
            <p>Quz.<footnote name="x">Quz</footnote></p>
            <p>Quz.<footnote name="fn:5">Quz</footnote></p>
            <p>Quz.<footnote name="fn:6">Quz</footnote></p>
            <p>Quz.<footnote name="fn:7">Quz</footnote></p>
            <h3>Foo</h3>
            <h4>Bar</h4>';

        $ctr = $renderer->firePreSaveEvent($ctr);

        $output = $renderer->render($ctr);

        self::assertIsString($output);
        self::assertNotEmpty($output);
    }
}
