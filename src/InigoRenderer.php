<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use Kaloa\Renderer\Inigo\Parser;

final class InigoRenderer implements RendererInterface
{
    private Parser $parser;

    public function __construct(Config $config)
    {
        $this->parser = new Parser();
        $this->parser->addDefaultHandlers($config);
    }

    public function render(string $input): string
    {
        return $this->parser->parse($input);
    }
}
