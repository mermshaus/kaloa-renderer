<?php

namespace Kaloa\Tests;

use Kaloa\Renderer\Config;
use Kaloa\Renderer\Inigo\Handler\AbbrHandler;
use Kaloa\Renderer\Inigo\Handler\AmazonHandler;
use Kaloa\Renderer\Inigo\Handler\CodeHandler;
use Kaloa\Renderer\Inigo\Handler\FootnotesHandler;
use Kaloa\Renderer\Inigo\Handler\HTMLHandler;
use Kaloa\Renderer\Inigo\Handler\ImgHandler;
use Kaloa\Renderer\Inigo\Handler\SimpleHandler;
use Kaloa\Renderer\Inigo\Handler\UrlHandler;
use Kaloa\Renderer\Inigo\Handler\YouTubeHandler;
use Kaloa\Renderer\Inigo\Parser;
use Kaloa\Renderer\Inigo\Tag;
use PHPUnit_Framework_TestCase;

/**
 *
 */
class TagTest extends PHPUnit_Framework_TestCase
{
    private function initParser()
    {
        $config = new Config();

        $inigo = new Parser();

        $inigo->addSetting('image-dir', $config->getResourceBasePath() . '/');

        // Example for multiple tags being displayed in the same way
        $inigo
        ->addHandler(new SimpleHandler('b|strong', Parser::TAG_INLINE, '<strong>', '</strong>'))
        ->addHandler(new SimpleHandler('i|em', Parser::TAG_INLINE, '<em>', '</em>'))

        // Used to display other tags. Tags with type Parser::TAG_PRE will not be parsed
        // This tag belongs also to two types

        ->addHandler(new SimpleHandler('off', Parser::TAG_INLINE | Parser::TAG_PRE, '', ''))

        //->addHandler(new SimpleHandler('u', Parser::TAG_INLINE, '<span class="underline">', '</span>'))
        ->addHandler(new SimpleHandler('var', Parser::TAG_INLINE | Parser::TAG_PRE, '<var>', '</var>'))
        //->addHandler(new SimpleHandler('strike', Parser::TAG_INLINE, '<span class="strike">', '</span>'))
        ->addHandler(new SimpleHandler(
            'quote',
            Parser::TAG_OUTLINE | Parser::TAG_FORCE_PARAGRAPHS,
            '<blockquote>',
            "</blockquote>\n\n"
        ))

        /* Most replacements are rather simple */
        ->addHandler(new SimpleHandler('h1', Parser::TAG_OUTLINE, "<h1>", "</h1>\n\n"))
        ->addHandler(new SimpleHandler('h2', Parser::TAG_OUTLINE, "<h2>", "</h2>\n\n"))
        ->addHandler(new SimpleHandler('h3', Parser::TAG_OUTLINE, "<h3>", "</h3>\n\n"))
        ->addHandler(new SimpleHandler('h4', Parser::TAG_OUTLINE, "<h4>", "</h4>\n\n"))
        ->addHandler(new SimpleHandler('h5', Parser::TAG_OUTLINE, "<h5>", "</h5>\n\n"))
        ->addHandler(new SimpleHandler('h6', Parser::TAG_OUTLINE, "<h6>", "</h6>\n\n"))
        ->addHandler(new SimpleHandler('dl', Parser::TAG_OUTLINE, "<dl>", "\n\n</dl>\n\n"))
        ->addHandler(new SimpleHandler('dt', Parser::TAG_OUTLINE, "\n\n<dt>", "</dt>"))
        ->addHandler(new SimpleHandler('dd', Parser::TAG_OUTLINE, "\n<dd>", "</dd>"))
        ->addHandler(new SimpleHandler('ul', Parser::TAG_OUTLINE, "<ul>", "\n</ul>\n\n"))
        ->addHandler(new SimpleHandler('ol', Parser::TAG_OUTLINE, "<ol>", "\n</ol>\n\n"))
        ->addHandler(new SimpleHandler('li', Parser::TAG_OUTLINE, "\n<li>", "</li>"))
        ->addHandler(new SimpleHandler('table', Parser::TAG_OUTLINE, "<table>", "\n</table>\n\n"))
        ->addHandler(new SimpleHandler('tr', Parser::TAG_OUTLINE, "\n<tr>", "\n</tr>"))
        ->addHandler(new SimpleHandler('td', Parser::TAG_OUTLINE, "\n<td>", "</td>"))
        ->addHandler(new SimpleHandler('th', Parser::TAG_OUTLINE, "\n<th>", "</th>"))

        ->addHandler(new UrlHandler())
        ->addHandler(new ImgHandler())
        ->addHandler(new AmazonHandler())
        ->addHandler(new AbbrHandler())
        ->addHandler(new HTMLHandler())
        ->addHandler(new CodeHandler($config->getSyntaxHighlighter()))
        ->addHandler(new FootnotesHandler())
        ->addHandler(new YouTubeHandler());

        return $inigo;
    }

    public function testFoo()
    {
        $handlers = $this->initParser();
        $handlers = $handlers->getHandlers();

        $tag = new Tag('[url=example.org]', $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals(array('href' => 'example.org'), $tag->getAttributes());
        $this->assertEquals('[url=example.org]', $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag('[url="example.org"]', $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals(array('href' => 'example.org'), $tag->getAttributes());
        $this->assertEquals('[url="example.org"]', $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag('[url href="example.org"]', $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals(array('href' => 'example.org'), $tag->getAttributes());
        $this->assertEquals('[url href="example.org"]', $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag('[url="example.org" title="This is a test"]', $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals(array('href' => 'example.org', 'title' => 'This is a test'), $tag->getAttributes());
        $this->assertEquals('[url="example.org" title="This is a test"]', $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag('[url href="example.org" title="This is a test"]', $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals('href,title', implode(',', array_keys($tag->getAttributes())));
        $this->assertEquals(array('href' => 'example.org', 'title' => 'This is a test'), $tag->getAttributes());
        $this->assertEquals('[url href="example.org" title="This is a test"]', $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag('[url title="This is a test" href="example.org"]', $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals('href,title', implode(',', array_keys($tag->getAttributes())));
        $this->assertEquals(array('href' => 'example.org', 'title' => 'This is a test'), $tag->getAttributes());
        $this->assertEquals('[url title="This is a test" href="example.org"]', $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag("[url   title=\"This is a test\" \n\t  href=\"example.org\"\n  ]", $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals('href,title', implode(',', array_keys($tag->getAttributes())));
        $this->assertEquals(array('href' => 'example.org', 'title' => 'This is a test'), $tag->getAttributes());
        $this->assertEquals("[url   title=\"This is a test\" \n\t  href=\"example.org\"\n  ]", $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag("[URl   tItLE=\"This is a test\" \n\t  HRef=\"example.org\"\n  ]", $handlers);

        $this->assertEquals('url', $tag->getName());
        $this->assertEquals(false, $tag->isClosingTag());
        $this->assertEquals('href,title', implode(',', array_keys($tag->getAttributes())));
        $this->assertEquals(array('href' => 'example.org', 'title' => 'This is a test'), $tag->getAttributes());
        $this->assertEquals("[URl   tItLE=\"This is a test\" \n\t  HRef=\"example.org\"\n  ]", $tag->getRawData());
        $this->assertEquals(true, $tag->isValid());
        $this->assertEquals(true, $tag->isOfType(Parser::TAG_INLINE));

        $this->assertEquals(false, $tag->isOfType(Parser::TAG_OUTLINE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_PRE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_SINGLE));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_CLEAR_CONTENT));
        $this->assertEquals(false, $tag->isOfType(Parser::TAG_FORCE_PARAGRAPHS));



        $tag = new Tag('[ url="example.org"]', $handlers);
        $this->assertEquals(false, $tag->isValid());

        $tag = new Tag('[url = "example.org" ]', $handlers);
        $this->assertEquals(true, $tag->isValid());

        $tag = new Tag('[/ url]', $handlers);
        $this->assertEquals(false, $tag->isValid());

        $tag = new Tag('[/url ]', $handlers);
        $this->assertEquals(true, $tag->isValid());

        $tag = new Tag('[/uRL ]', $handlers);
        $this->assertEquals(true, $tag->isValid());
    }
}
