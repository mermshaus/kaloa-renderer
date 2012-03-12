<?php

namespace Kaloa\Renderer\Inigo\Handler;

abstract class ProtoHandler
{
    public $name;
    public $type;

    public function initialize()
    {

    }

    public function draw(array $data)
    {

    }

    public function postProcess($s, array $data)
    {
        return $s;
    }
}
