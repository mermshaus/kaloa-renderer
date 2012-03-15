<?php

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\Encoder;

/**
 *
 */
class DoEncodeAmpsAndAnglesFilter extends AbstractFilter
{
    /**
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     *
     * @param Encoder $encoder
     */
    public function __construct(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        return $this->encoder->encodeAmpsAndAngles($text);
    }
}
