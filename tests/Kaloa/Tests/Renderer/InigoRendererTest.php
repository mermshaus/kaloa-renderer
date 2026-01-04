<?php

declare(strict_types=1);

namespace Kaloa\Tests\Renderer;

use Kaloa\Renderer\Config;
use Kaloa\Renderer\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InigoRendererTest extends TestCase
{
    public function testIntegrity(): void
    {
        // Environment
        $contentToRender = file_get_contents(
            __DIR__ . '/examples/inigo/klangbilder.txt'
        );
        $resourceBasePath = __DIR__ . '/examples/inigo/res';
        $filter = 'inigo';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer($filter, $config);

        $output = $renderer->render($contentToRender);

        $expected = rtrim(
            file_get_contents(__DIR__ . '/examples/inigo/klangbilder.expected')
        );

        $expected = str_replace(
            '__RESOURCE_BASE_PATH__',
            $config->getResourceBasePath(),
            $expected
        );

        self::assertEquals($expected, $output);
    }

    public static function runSuiteProvider(): array
    {
        $sets = [];

        foreach (glob(__DIR__ . '/examples/inigo/*.txt') as $file) {
            $sets[basename($file)] = array(
                realpath($file),
                realpath(substr($file, 0, -4) . '.expected')
            );
        }

        return $sets;
    }

    #[DataProvider('runSuiteProvider')]
    public function testRunSuite(string $fileInput, string $fileExpected): void
    {
        $resourceBasePath = __DIR__ . '/examples/inigo/res';

        $config = new Config($resourceBasePath);

        $renderer = Factory::createRenderer('inigo', $config);

        $expected = rtrim(file_get_contents($fileExpected));

        $expected = str_replace(
            '__RESOURCE_BASE_PATH__',
            $config->getResourceBasePath(),
            $expected
        );

        if (str_starts_with($expected, 'Exception:')) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage(
                trim(substr($expected, strlen('Exception:')))
            );
        }

        $output = $renderer->render(file_get_contents($fileInput));

        self::assertEquals($expected, $output);
    }
}
