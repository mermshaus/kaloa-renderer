<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Parser;

final class FootnotesHandler extends ProtoHandler
{
    private int $cnt;

    /**
     * @var list<string>
     */
    private array $footnotes;

    public function __construct()
    {
        $this->name = 'fn|fnt';

        $this->type = [
            (Parser::TAG_INLINE | Parser::TAG_SINGLE),
            (Parser::TAG_OUTLINE | Parser::TAG_CLEAR_CONTENT)
        ];
    }

    public function draw(array $data): string
    {
        $ret = '';

        if ($data['tag'] === 'fn' && $data['front']) {
            $this->cnt++;
            $ret = '[' . $this->cnt . ']';
        } elseif ($data['tag'] === 'fnt' && !$data['front']) {
            if (!isset($data['content'])) {
                throw new \RuntimeException('No content given but content expected for tag fnt.');
            }

            $this->footnotes[] = $data['content'];
        }

        return $ret;
    }

    public function initialize(): void
    {
        $this->cnt = 0;
        $this->footnotes = [];
    }

    public function postProcess(string $s, array $data): string
    {
        $ret = '';

        if (($data['tag'] === 'fnt') && ($this->cnt > 0)) {
            $ret .= '<ol>' . "\n";
            foreach ($this->footnotes as $f) {
                $ret .= '<li>' . $f . '</li>' . "\n";
            }
            $ret .= '</ol>';
        }

        return $s . $ret;
    }
}
