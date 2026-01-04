<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Structs;

/**
 * @phpstan-import-type HandlerDataStruct from Structs
 */
abstract class ProtoHandler
{
    public string $name;
    /**
     * @var list<int>|int
     */
    public array|int $type;
    public ?string $defaultParam = null;

    public function initialize(): void
    {
        // nop
    }

    /**
     * @param HandlerDataStruct $data
     */
    abstract public function draw(array $data): string;

    /**
     * @param HandlerDataStruct $sourceData
     */
    protected function fillParam(
        array $sourceData,
        string $key,
        mixed $defaultValue = null
    ): mixed {
        return (isset($sourceData['params'][$key]))
            ? $sourceData['params'][$key] : $defaultValue;
    }

    protected function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param array{vars: array<mixed>, tag: string} $data
     */
    public function postProcess(string $s, array $data): string
    {
        return $s;
    }
}
