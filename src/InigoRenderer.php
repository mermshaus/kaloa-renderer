<?php

namespace Kaloa\Renderer;

use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
final class InigoRenderer implements RendererInterface
{
    /**
     * @var Parser
     */
    private $parser = null;

    /**
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->parser = new Parser();
        $this->parser->addDefaultHandlers($config);
    }

    /**
     *
     * @param  string $input
     * @return string
     */
    public function render($input)
    {
        return $this->parser->parse($input);
    }
}
