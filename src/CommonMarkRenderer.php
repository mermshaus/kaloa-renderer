<?php

declare(strict_types=1);

namespace Kaloa\Renderer;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\ConverterInterface;

final class CommonMarkRenderer implements RendererInterface
{
    private ConverterInterface $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter();
    }

    public function render(string $input): string
    {
        return $this->converter->convert($input)->getContent();
    }
}
