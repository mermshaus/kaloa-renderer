<?php

namespace Kaloa\Renderer\Markdown\Filter;

use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\RegexManager;

/**
 * Process character escapes, code spans, and inline HTML in one shot.
 *
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber.
 *
 * See Kaloa\Renderer\Markdown\Parser for full license info.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class ParseSpanFilter extends AbstractFilter
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
     * @var bool
     */
    protected $no_markup;

    /**
     *
     * @param RegexManager $rem
     * @param Hasher       $hasher
     * @param bool         $no_markup
     */
    public function __construct(RegexManager $rem, Hasher $hasher, $no_markup)
    {
        $this->rem = $rem;
        $this->hasher = $hasher;
        $this->no_markup = $no_markup;
    }

    /**
     * Take the string $str and parse it into tokens, hashing embeded HTML,
     * escaped characters and handling code spans.
     *
     * @param  string $str
     * @return string
     */
    public function run($text)
    {
        $str = $text;
        $output = '';

        $span_re = '{
                (
                    \\\\'.$this->rem->getPattern('escape_chars').'
                |
                    (?<![`\\\\])
                    `+                        # code span marker
            '.( $this->no_markup ? '' : '
                |
                    <!--    .*?     -->        # comment
                |
                    <\?.*?\?> | <%.*?%>        # processing instruction
                |
                    <[/!$]?[-a-zA-Z0-9:_]+    # regular tags
                    (?>
                        \s
                        (?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
                    )?
                    >
            ').'
                )
                }xs';

        while (true) {
            // Each loop iteration seach for either the next tag, the next
            // openning code span marker, or the next escaped character.
            // Each token is then passed to handleSpanToken.
            $parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);

            // Create token from text preceding tag.
            if ($parts[0] != "") {
                $output .= $parts[0];
            }

            // Check if we reach the end.
            if (isset($parts[1])) {
                $output .= $this->handleSpanToken($parts[1], $parts[2]);
                $str = $parts[2];
            } else {
                break;
            }
        }

        return $output;
    }

    /**
     * Handle $token provided by parseSpan by determining its nature and
     * returning the corresponding value that should replace it.
     *
     * @param  string $token
     * @param  string $str
     * @return string
     */
    protected function handleSpanToken($token, &$str)
    {
        $matches = array();

        switch (substr($token, 0, 1)) {
            case "\\":
                return $this->hasher->hashPart("&#". ord($token{1}). ";");
            case "`":
                // Search for end marker in remaining text.
                if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm',
                    $str, $matches))
                {
                    $str = $matches[2];
                    $codespan = $this->makeCodeSpan($matches[1]);
                    return $this->hasher->hashPart($codespan);
                }
                // return as text since no ending marker found.
                return $token;
            default:
                return $this->hasher->hashPart($token);
        }
    }

    /**
     * Create a code span markup for $code. Called from handleSpanToken.
     *
     * @param type $code
     * @return type
     */
    protected function makeCodeSpan($code)
    {
        $code = htmlspecialchars(trim($code), ENT_NOQUOTES);
        return $this->hasher->hashPart("<code>$code</code>");
    }
}
