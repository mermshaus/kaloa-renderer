<?php

namespace Kaloa\Renderer\Inigo\Handler;

abstract class ProtoHandler
{
    public $name;
    public $type;

    public function initialize()
    {
        // nop
    }

    abstract public function draw(array $data);

    public function fillParam(array $sourceData, $key, $defaultValue, $isDefaultParam = false)
    {
        $ret = $defaultValue;

        if ($isDefaultParam && isset($sourceData['params']['(default)'])) {
            $ret = $sourceData['params']['(default)'];
        } elseif (isset($sourceData['params'][$key])) {
            $ret = $sourceData['params'][$key];
        }

        return $ret;
    }

    protected function e($s)
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    public function postProcess($s, array $data)
    {
        return $s;
    }
}
