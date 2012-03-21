<?php

namespace Kaloa\Renderer\Inigo\Handler;

abstract class ProtoHandler
{
    public $name;
    public $type;

    public function initialize()
    {

    }

    public function draw(array $data)
    {

    }

    public function fillParam(array $sourceData, $key, $defaultValue, $isDefaultParam = false)
    {
        $ret = $defaultValue;

        if ($isDefaultParam && isset($sourceData['params']['(default)'])) {
            $ret = $sourceData['params']['(default)'];
        } else if (isset($sourceData['params'][$key])) {
            $ret = $sourceData['params'][$key];
        }

        return $ret;
    }

    public function postProcess($s, array $data)
    {
        return $s;
    }
}
