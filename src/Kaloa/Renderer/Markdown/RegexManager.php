<?php

namespace Kaloa\Renderer\Markdown;

use Exception;

/**
 *
 *
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber.
 *
 * See Kaloa\Renderer\Markdown\Parser for full license info.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class RegexManager
{
    protected $em_relist = array(
        ''  => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?=\S|$)(?![\.,:;]\s)',
        '*' => '(?<=\S|^)(?<!\*)\*(?!\*)',
        '_' => '(?<=\S|^)(?<!_)_(?!_)',
        );
    protected $strong_relist = array(
        ''   => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?=\S|$)(?![\.,:;]\s)',
        '**' => '(?<=\S|^)(?<!\*)\*\*(?!\*)',
        '__' => '(?<=\S|^)(?<!_)__(?!_)',
        );
    protected $em_strong_relist = array(
        ''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?=\S|$)(?![\.,:;]\s)',
        '***' => '(?<=\S|^)(?<!\*)\*\*\*(?!\*)',
        '___' => '(?<=\S|^)(?<!_)___(?!_)',
        );

    /**
     * Regex to match balanced [brackets].
     * Needed to insert a maximum bracked depth while converting to PHP.
     */
    protected $nested_brackets_depth = 6;
    protected $nested_brackets_re;

    protected $nested_url_parenthesis_depth = 4;
    protected $nested_url_parenthesis_re;

    /**
     * Table of hash values for escaped characters:
     */
    protected $escape_chars = '\`*_{}[]()>#+-.!';
    protected $escape_chars_re;

    protected $patterns = array();

    public function __construct()
    {
        $this->patterns['em_strong_prepared'] = array();

        $this->prepareItalicsAndBold();

        $this->patterns['nested_brackets'] =
            str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth).
            str_repeat('\])*', $this->nested_brackets_depth);

        $this->patterns['nested_url_parenthesis'] =
            str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth).
            str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth);

        $this->patterns['escape_chars'] = '['.preg_quote($this->escape_chars).']';
    }

    /**
     * Prepare regular expressions for searching emphasis tokens in any context.
     */
    protected function prepareItalicsAndBold()
    {
        foreach ($this->em_relist as $em => $em_re) {
            foreach ($this->strong_relist as $strong => $strong_re) {
                # Construct list of allowed token expressions.
                $token_relist = array();
                if (isset($this->em_strong_relist["$em$strong"])) {
                    $token_relist[] = $this->em_strong_relist["$em$strong"];
                }
                $token_relist[] = $em_re;
                $token_relist[] = $strong_re;

                # Construct master expression from list.
                $token_re = '{('. implode('|', $token_relist) .')}';
                $this->patterns['em_strong_prepared'][$em . $strong] = $token_re;
            }
        }
    }

    public function getPattern($key)
    {
        if (!isset($this->patterns[$key])) {
            throw new Exception('Pattern ' . $key . ' not found.');
        }

        return $this->patterns[$key];
    }
}
