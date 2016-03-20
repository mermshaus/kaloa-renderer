<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\Config;

/**
 *
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     *
     * @param string $input
     * @return string
     */
    public function firePreSaveEvent($input)
    {
        return $input;
    }

    /**
     *
     */
    protected function init()
    {
        // nop
    }

    /**
     *
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }
}
