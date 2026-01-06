<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo;

/**
 * @phpstan-import-type HandlerStruct from Structs
 */
final class Tag
{
    private string $name;
    /**
     * @var array<string>
     */
    private array $attributes;
    private bool $isClosingTag;
    private string $rawData;
    private bool $isValid;
    /**
     * @var HandlerStruct|null
     */
    private ?array $myHandler;

    /**
     * @param list<HandlerStruct> $handlers
     */
    public function __construct(string $tagString, array $handlers)
    {
        $this->rawData = $tagString;
        $this->name = $this->extractTagName($tagString);

        $this->myHandler = null;

        foreach ($handlers as $handler) {
            if ($handler['name'] === $this->name) {
                $this->myHandler = $handler;
                break;
            }
        }

        $this->isValid = !is_null($this->myHandler);
        $this->isClosingTag = (mb_substr($tagString, 1, 1) === '/');
        $this->attributes = $this->extractTagAttributes($tagString);
    }

    public function getRawData(): string
    {
        return $this->rawData;
    }

    /**
     * Returns the tag name from a valid tag string
     *
     * "[tag=http://.../]" => "tag"
     * "[/TAG]"            => "tag"
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function isClosingTag(): bool
    {
        return $this->isClosingTag;
    }

    public function isOfType(int $tagType): bool
    {
        return ($this->isValid && $this->myHandler['type'] & $tagType);
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    private function extractTagName(string $tagString): string
    {
        $name = '';
        $tagPattern = '[A-Za-z][A-Za-z0-9_]*';
        $matches = [];

        if (1 === preg_match(
                '/\A\[(' . $tagPattern . ')[\s=\]]/',
                $tagString,
                $matches
            )
        ) {
            $name = strtolower($matches[1]);
        } elseif (1 === preg_match(
                '/\A\[\/(' . $tagPattern . ')\s*\]\z/',
                $tagString,
                $matches
            )
        ) {
            $name = strtolower($matches[1]);
        }

        return $name;
    }

    /**
     * @return array<string>
     */
    private function extractTagAttributes(string $tagString): array
    {
        if ($this->isValid === false) {
            return [];
        }

        $ret = [];

        $tagString = str_replace('&quot;', '"', $tagString);
        $tagString = str_replace("\\\"", '&quot;', $tagString);

        $tagString = trim(mb_substr($tagString, 1, mb_strlen($tagString) - 2));

        $i = mb_strpos($tagString, ' ');
        $j = mb_strpos($tagString, '=');
        if ((!($j === false)) && (($j < $i) || ($i === false))) {
            $i = $j;
        }

        if ($i === false) {
            return [];
        }

        $tagString = trim(mb_substr($tagString, $i));

        if (mb_substr($tagString, 0, 1) === '=') {
            //if
            $t = ltrim(mb_substr($tagString, 1));
            if (mb_substr($t, 0, 1) == '"') {
                $i = mb_strpos($tagString, '"');
                $j = mb_strpos($tagString, '"', $i + 1);
                $ret['__default'] = mb_substr(
                    $tagString,
                    $i + 1,
                    $j - ($i + 1)
                );
                $tagString = trim(mb_substr($tagString, $j + 1));
            } else {
                $i = mb_strpos($t, ' ');
                if ($i === false) {
                    $ret['__default'] = trim($t);
                    $tagString = trim(mb_substr($t, $i + 1));
                } else {
                    $ret['__default'] = mb_substr($t, 0, $i);
                    $tagString = trim(mb_substr($t, $i + 1));
                }
            }
        }

        $i = mb_strpos($tagString, '=');
        while (!($i === false)) {
            $j = mb_strpos($tagString, '"');
            $k = mb_strpos($tagString, '"', $j + 1);
            if (($k > -1) && (mb_substr($tagString, $k - 1, 1) == '\\')) {
                $k = mb_strpos($tagString, '"', $k + 1);
            }
            $ret[trim(mb_substr($tagString, 0, $i))] = mb_substr(
                $tagString,
                $j + 1,
                $k - ($j + 1)
            );
            $tagString = trim(mb_substr($tagString, $k + 1));
            $i = mb_strpos($tagString, '=');
        }

        $retNew = [];
        foreach ($ret as $key => $value) {
            $retNew[strtolower($key)] = $value;
        }
        $ret = $retNew;
        unset($retNew);

        if (isset($ret['__default'])) {
            $defaultParam = $this->myHandler['function']->defaultParam;

            if (!isset($ret[$defaultParam])) {
                $ret[$defaultParam] = $ret['__default'];
            }

            unset($ret['__default']);
        }

        ksort($ret);

        return $ret;
    }
}
