<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

interface RendererInterface
{
    public function render(string $input): string ;
}
