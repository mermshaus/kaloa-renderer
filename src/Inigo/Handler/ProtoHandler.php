<?php

namespace Kaloa\Renderer\Inigo\Handler;

/**
 *
 */
abstract class ProtoHandler
{
    public $name;
    public $type;
    public $defaultParam = null;

    /**
     *
     */
    public function initialize()
    {
        // nop
    }

    /**
     *
     */
    abstract public function draw(array $data);

    /**
     *
     * @param array $sourceData
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function fillParam(array $sourceData, $key, $defaultValue = null)
    {
        return (isset($sourceData['params'][$key]))
                ? $sourceData['params'][$key]
                : $defaultValue;
    }

    /**
     *
     * @param string $s
     * @return string
     */
    protected function e($s)
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    /**
     *
     * @param string $s
     * @param array $data
     * @return string
     */
    public function postProcess($s, array $data)
    {
        return $s;
    }
}
