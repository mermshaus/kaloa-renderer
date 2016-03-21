<?php

namespace Kaloa\Tests;

use Exception;
use Kaloa\Renderer\Factory;
use PHPUnit_Framework_TestCase;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     */
    public function testInvalidRenderer()
    {
        Factory::createRenderer('bogus');
    }
}
