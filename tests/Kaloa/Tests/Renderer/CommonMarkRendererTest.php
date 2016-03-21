<?php

namespace Kaloa\Tests;

use Kaloa\Renderer\Factory;
use PHPUnit_Framework_TestCase;

class CommonMarkRendererTest extends PHPUnit_Framework_TestCase
{
    public function testIntegrity()
    {
        $renderer = Factory::createRenderer('commonmark');

        $this->assertEquals("<h1>Hello World!</h1>\n", $renderer->render('# Hello World!'));
    }
}
