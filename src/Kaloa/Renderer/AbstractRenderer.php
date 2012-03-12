<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\Config;

/**
 *
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
abstract class AbstractRenderer
{
    /** @var Config */
    protected $config;

    abstract public function render($input);

    public function firePreSaveEvent($input)
    {
        return $input;
    }

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->init();
    }

    protected function init()
    {
        // nop
    }

    /**
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
    }
}
