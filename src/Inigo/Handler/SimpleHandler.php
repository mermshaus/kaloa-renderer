<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

final class SimpleHandler extends ProtoHandler
{
    private string $front;
    private string $back;

    public function __construct(string $name, int $type, string $front, string $back)
    {
        $this->name  = $name;
        $this->type  = $type;
        $this->front = $front;
        $this->back  = $back;
    }

    public function draw(array $data): string
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
