<?php

namespace Kaloa\Renderer;

class Config
{
    protected $resourceBasePath = '.';

    public function getResourceBasePath()
    {
        return $this->resourceBasePath;
    }

    public function setResourceBasePath($resourceBasePath)
    {
        $this->resourceBasePath = $resourceBasePath;
    }
}
