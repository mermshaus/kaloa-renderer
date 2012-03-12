<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;

class SimpleHandler extends ProtoHandler
{
    private $m_front;
    private $m_back;

    public function __construct($name, $type, $front, $back)
    {
        $this->name = $name;
        $this->type = $type;
        $this->m_front = $front;
        $this->m_back = $back;
    }

    public function draw(array $data)
    {
        if ($data['front']) {
            return $this->m_front;
        } else {
            return $this->m_back;
        }
    }
}
