<?php
/**
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber. See README and LICENSE
 * files in the root directory of this package for full license info.
 */

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Filter\AbstractFilter;
use Kaloa\Renderer\Markdown\RegexManager;
use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\Parser;

class DoItalicsAndBoldFilter extends AbstractFilter
{
    /**
     *
     * @var RegexManager
     */
    protected $rem;

    /**
     *
     * @var Hasher
     */
    protected $hasher;

    /**
     *
     * @var Parser
     */
    protected $parser;

    /**
     *
     * @param RegexManager $rem
     * @param Hasher $hasher
     * @param Parser $parser
     */
    public function __construct(RegexManager $rem, Hasher $hasher, Parser $parser)
    {
        $this->rem = $rem;
        $this->hasher = $hasher;
        $this->parser = $parser;
    }

    /**
     *
     * @param  string $text
     * @return string
     */
    public function run($text)
    {
        $token_stack = array('');
        $text_stack = array('');
        $em = '';
        $strong = '';
        $tree_char_em = false;

        $relist = $this->rem->getPattern('em_strong_prepared');

        while (true) {
            // Get prepared regular expression for seraching emphasis tokens
            // in current context.
            $token_re = $relist["$em$strong"];

            // Each loop iteration search for the next emphasis token.
            // Each token is then passed to handleSpanToken.
            $parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
            $text_stack[0] .= $parts[0];
            $token =& $parts[1];
            $text =& $parts[2];

            if (empty($token)) {
                // Reached end of text span: empty stack without emitting.
                // any more emphasis.
                while ($token_stack[0]) {
                    $text_stack[1] .= array_shift($token_stack);
                    $text_stack[0] .= array_shift($text_stack);
                }
                break;
            }

            $token_len = strlen($token);
            if ($tree_char_em) {
                // Reached closing marker while inside a three-char emphasis.
                if ($token_len === 3) {
                    // Three-char closing marker, close em and strong.
                    array_shift($token_stack);
                    $span = array_shift($text_stack);
                    $span = $this->parser->runSpanGamut($span);
                    $span = "<strong><em>$span</em></strong>";
                    $text_stack[0] .= $this->hasher->hashPart($span);
                    $em = '';
                    $strong = '';
                } else {
                    // Other closing marker: close one em or strong and
                    // change current token state to match the other
                    $token_stack[0] = str_repeat($token{0}, 3-$token_len);
                    $tag = $token_len == 2 ? "strong" : "em";
                    $span = $text_stack[0];
                    $span = $this->parser->runSpanGamut($span);
                    $span = "<$tag>$span</$tag>";
                    $text_stack[0] = $this->hasher->hashPart($span);
                    $$tag = ''; // $$tag stands for $em or $strong
                }
                $tree_char_em = false;
            } else if ($token_len === 3) {
                if ($em) {
                    // Reached closing marker for both em and strong.
                    // Closing strong marker:
                    for ($i = 0; $i < 2; ++$i) {
                        $shifted_token = array_shift($token_stack);
                        $tag = strlen($shifted_token) == 2 ? "strong" : "em";
                        $span = array_shift($text_stack);
                        $span = $this->parser->runSpanGamut($span);
                        $span = "<$tag>$span</$tag>";
                        $text_stack[0] .= $this->hasher->hashPart($span);
                        $$tag = ''; // $$tag stands for $em or $strong
                    }
                } else {
                    // Reached opening three-char emphasis marker. Push on token
                    // stack; will be handled by the special condition above.
                    $em = $token{0};
                    $strong = "$em$em";
                    array_unshift($token_stack, $token);
                    array_unshift($text_stack, '');
                    $tree_char_em = true;
                }
            } else if ($token_len === 2) {
                if ($strong) {
                    // Unwind any dangling emphasis marker:
                    if (strlen($token_stack[0]) == 1) {
                        $text_stack[1] .= array_shift($token_stack);
                        $text_stack[0] .= array_shift($text_stack);
                    }
                    // Closing strong marker:
                    array_shift($token_stack);
                    $span = array_shift($text_stack);
                    $span = $this->parser->runSpanGamut($span);
                    $span = "<strong>$span</strong>";
                    $text_stack[0] .= $this->hasher->hashPart($span);
                    $strong = '';
                } else {
                    array_unshift($token_stack, $token);
                    array_unshift($text_stack, '');
                    $strong = $token;
                }
            } else {
                // Here $token_len == 1
                if ($em) {
                    if (strlen($token_stack[0]) === 1) {
                        // Closing emphasis marker:
                        array_shift($token_stack);
                        $span = array_shift($text_stack);
                        $span = $this->parser->runSpanGamut($span);
                        $span = "<em>$span</em>";
                        $text_stack[0] .= $this->hasher->hashPart($span);
                        $em = '';
                    } else {
                        $text_stack[0] .= $token;
                    }
                } else {
                    array_unshift($token_stack, $token);
                    array_unshift($text_stack, '');
                    $em = $token;
                }
            }
        }

        return $text_stack[0];
    }
}
