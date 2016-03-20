<?php

namespace Kaloa\Renderer;

/**
 *
 */
interface RendererInterface
{
    /**
     *
     * @param string $input
     * @return string
     */
    public function render($input);
}
