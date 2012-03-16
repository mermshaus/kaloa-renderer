<?php

namespace Kaloa\Renderer\Markdown;

use ArrayObject;

use Kaloa\Renderer\Markdown\Encoder;
use Kaloa\Renderer\Markdown\Hasher;
use Kaloa\Renderer\Markdown\RegexManager;

use Kaloa\Renderer\Markdown\Filter\DoAnchorsFilter;
use Kaloa\Renderer\Markdown\Filter\DoAutoLinksFilter;
use Kaloa\Renderer\Markdown\Filter\DoEncodeAmpsAndAnglesFilter;
use Kaloa\Renderer\Markdown\Filter\DoHardBreaksFilter;
use Kaloa\Renderer\Markdown\Filter\DoImagesFilter;
use Kaloa\Renderer\Markdown\Filter\DoItalicsAndBoldFilter;
use Kaloa\Renderer\Markdown\Filter\HashHtmlBlocksFilter;
use Kaloa\Renderer\Markdown\Filter\ParseSpanFilter;
use Kaloa\Renderer\Markdown\Filter\SetupFilter;
use Kaloa\Renderer\Markdown\Filter\StripLinkDefinitionsFilter;

use Kaloa\Renderer\Markdown\Filter\DoHeadersFilter;
use Kaloa\Renderer\Markdown\Filter\DoHorizontalRulesFilter;
use Kaloa\Renderer\Markdown\Filter\DoListsFilter;
use Kaloa\Renderer\Markdown\Filter\DoCodeBlocksFilter;
use Kaloa\Renderer\Markdown\Filter\DoBlockQuotesFilter;

use Kaloa\Renderer\Markdown\Filter\FormParagraphsFilter;

/**
 * Markdown Parser
 *
 * This is a PHP 5.3 port of the PHP Markdown class written by Michel Fortin.
 * PHP Markdown is based on the work of John Gruber.
 *
 * Here's the full license text for PHP Markdown:
 *
 *     PHP Markdown & Extra
 *     Copyright (c) 2004-2012 Michel Fortin
 *     <http://michelf.com/>
 *     All rights reserved.
 *
 *     Based on Markdown
 *     Copyright (c) 2003-2006 John Gruber
 *     <http://daringfireball.net/>
 *     All rights reserved.
 *
 *     Redistribution and use in source and binary forms, with or without
 *     modification, are permitted provided that the following conditions are
 *     met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *     * Neither the name "Markdown" nor the names of its contributors may
 *       be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 *     This software is provided by the copyright holders and contributors "as
 *     is" and any express or implied warranties, including, but not limited
 *     to, the implied warranties of merchantability and fitness for a
 *     particular purpose are disclaimed. In no event shall the copyright owner
 *     or contributors be liable for any direct, indirect, incidental, special,
 *     exemplary, or consequential damages (including, but not limited to,
 *     procurement of substitute goods or services; loss of use, data, or
 *     profits; or business interruption) however caused and on any theory of
 *     liability, whether in contract, strict liability, or tort (including
 *     negligence or otherwise) arising in any way out of the use of this
 *     software, even if advised of the possibility of such damage.
 *
 * @author Marc Ermshaus <marc@ermshaus.org>
 */
class Parser
{
    const VERSION = '1.0.1o';

    /**
     * Change to ">" for HTML output.
     * @var string
     */
    public $empty_element_suffix = ' />';

    /**
     * Define the width of a tab for code blocks.
     * @var int
     */
    public $tab_width = 4;

    /**
     * Change to `true` to disallow markup or entities.
     */
    public $no_markup   = false;
    public $no_entities = false;

    /**
     * Predefined urls and titles for reference links and images.
     */
    public $predef_urls   = array();
    public $predef_titles = array();

    public $list_level = 0;

    /**
     * Internal hashes used during transformation.
     * @var type
     */
    protected $urls   = array();
    protected $titles = array();

    /**
     * Hasher
     * @var Hasher
     */
    protected $hasher;

    /**
     * RegexManager
     * @var RegexManager
     */
    protected $rem;

    /**
     *
     * @var Encoder
     */
    protected $encoder;

    /**
     * Status flag to avoid invalid nesting.
     * @var bool
     */
    public $in_anchor = false;

    /**
     * Constructor function. Initialize appropriate member variables.
     *
     * @param Hasher       $hasher
     * @param RegexManager $rem
     * @param Encoder      $encoder
     */
    public function __construct(Hasher $hasher, RegexManager $rem, Encoder $encoder)
    {
        $this->hasher = $hasher;
        $this->rem    = $rem;

        $encoder->setNoEntities($this->no_entities);
        $this->encoder = $encoder;
    }

    /**
     * Called before the transformation process starts to setup parser states.
     */
    protected function setup()
    {
        // Clear global hashes.
        $this->urls   = new ArrayObject($this->predef_urls);
        $this->titles = new ArrayObject($this->predef_titles);
        $this->hasher->clear();

        $this->in_anchor = false;
    }

    /**
     * Called after the transformation process to clear any variable which may
     * be taking up memory unnecessarly.
     */
    protected function teardown()
    {
        $this->urls   = array();
        $this->titles = array();
        $this->hasher->clear();
    }

    /**
     * Main function. Performs some preprocessing on the input text and pass it
     * through the document gamut.
     *
     * @param  string $text
     * @return string
     */
    public function transform($text)
    {
        $this->setup();

        $f = new SetupFilter($this->tab_width);
        $text = $f->run($text);

        $f = new HashHtmlBlocksFilter($this->hasher, $this->tab_width, $this->no_markup);
        $text = $f->run($text);

        // Strip any lines consisting only of spaces and tabs. This makes
        // subsequent regexen easier to write, because we can match consecutive
        // blank lines with /\n+/ instead of something like /[ ]*\n+/ .
        $text = preg_replace('/^[ ]+$/m', '', $text);

        $f = new StripLinkDefinitionsFilter($this->urls, $this->titles, $this->tab_width);
        $text = $f->run($text);

        $text = $this->runBasicBlockGamut($text);

        $this->teardown();

        return $text . "\n";
    }

    /**
     * Run block gamut tranformations.
     *
     * These are all the transformations that form block-level tags like
     * paragraphs, headers, and list items.
     *
     * @param  string $text
     * @return string
     */
    public function runBlockGamut($text)
    {
        // We need to escape raw HTML in Markdown source before doing anything
        // else. This need to be done for each block, and not only at the
        // begining in the Markdown function since hashed blocks can be part of
        // list items and could have been indented. Indented blocks would have
        // been seen as a code block in a previous pass of hashHTMLBlocks.
        // $text = $this->hashHTMLBlocks($text);

        $f = new HashHtmlBlocksFilter($this->hasher, $this->tab_width, $this->no_markup);
        $text = $f->run($text);

        return $this->runBasicBlockGamut($text);
    }

    /**
     * Run block gamut tranformations, without hashing HTML blocks. This is
     * useful when HTML blocks are known to be already hashed, like in the first
     * whole-document pass.
     *
     * @param  string $text
     * @return string
     */
    protected function runBasicBlockGamut($text)
    {
        $f = new DoHeadersFilter($this->hasher, $this);
        $text = $f->run($text);

        $f = new DoHorizontalRulesFilter($this->hasher, $this->empty_element_suffix);
        $text = $f->run($text);

        $f = new DoListsFilter($this->hasher, $this->tab_width, $this);
        $text = $f->run($text);

        $f = new DoCodeBlocksFilter($this->hasher, $this->tab_width, $this);
        $text = $f->run($text);

        $f = new DoBlockQuotesFilter($this->hasher, $this);
        $text = $f->run($text);

        $f = new FormParagraphsFilter($this->hasher, $this);
        $text = $f->run($text);

        return $text;
    }

    /**
     * Run span gamut tranformations.
     *
     * These are all the transformations that occur *within* block-level tags
     * like paragraphs, headers, and list items.
     *
     * @param  string $text
     * @return string
     */
    public function runSpanGamut($text)
    {
        $sf = new ParseSpanFilter($this->rem, $this->hasher, $this->no_markup);
        $text = $sf->run($text);

        $f = new DoImagesFilter($this->encoder, $this->rem, $this->hasher,
                $this->urls, $this->titles, $this->empty_element_suffix);
        $text = $f->run($text);

        $f = new DoAnchorsFilter($this->encoder, $this->rem, $this->hasher,
                $this->urls, $this->titles, $this);
        $text = $f->run($text);

        $f = new DoAutoLinksFilter($this->encoder, $this->hasher);
        $text = $f->run($text);

        $f = new DoEncodeAmpsAndAnglesFilter($this->encoder);
        $text = $f->run($text);

        $f = new DoItalicsAndBoldFilter($this->rem, $this->hasher, $this);
        $text = $f->run($text);

        $f = new DoHardBreaksFilter($this->hasher, $this->empty_element_suffix);
        $text = $f->run($text);

        return $text;
    }

    /**
     * Remove one level of line-leading tabs or spaces
     *
     * @param  string $text
     * @return string
     */
    public function outdent($text)
    {
        return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text);
    }
}
