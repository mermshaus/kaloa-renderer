<?php

namespace Kaloa\Tests;

use Kaloa\Renderer\Config;
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
        $inigo->addDefaultHandlers($config);

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
