<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;

/**
 *
 */
final class SimpleHandler extends ProtoHandler
{
    /**
     *
     * @var string
     */
    private $front;

    /**
     *
     * @var string
     */
    private $back;

    /**
     *
     * @param string $name
     * @param int $type
     * @param string $front
     * @param string $back
     */
    public function __construct($name, $type, $front, $back)
    {
        $this->name  = $name;
        $this->type  = $type;
        $this->front = $front;
        $this->back  = $back;
    }

    /**
     *
     * @param array $data
     *
     * @return string
     */
    public function draw(array $data)
    {
        $ret = '';

        if ($data['front']) {
            $ret = $this->front;
        } else {
            $ret = $this->back;
        }

        return $ret;
    }
}
