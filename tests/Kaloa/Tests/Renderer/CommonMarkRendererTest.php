<?php

namespace Kaloa\Tests\Renderer;

use Kaloa\Renderer\Factory;
use PHPUnit\Framework\TestCase;

class CommonMarkRendererTest extends TestCase
{
    public function testIntegrity(): void
    {
        $renderer = Factory::createRenderer('commonmark');

        $this->assertEquals("<h1>Hello World!</h1>\n", $renderer->render('# Hello World!'));
    }
}
