<?php

namespace Kaloa\Tests\Renderer;

use Exception;
use Kaloa\Renderer\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testInvalidRenderer(): void
    {
        $this->expectException(Exception::class);
        Factory::createRenderer('bogus');
    }
}
