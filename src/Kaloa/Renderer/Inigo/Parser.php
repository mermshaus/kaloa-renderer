<?php

namespace Kaloa\Renderer\Inigo;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Tag;

use SplStack;

/**
 * Inigo
 *
 * Do not use this renderer. The code is very (!) old. It's in here for
 * backwards compatibility reasons
 *
 * @author Marc Ermshaus
 */
class Parser
{
    const TAG_OUTLINE          = 0x1;
    const TAG_INLINE           = 0x2;
    const TAG_PRE              = 0x4;
    const TAG_SINGLE           = 0x8;
    const TAG_CLEAR_CONTENT    = 0x10;
    const TAG_FORCE_PARAGRAPHS = 0x20;

    const PC_IMG_ALIGN_LEFT   = 0;
    const PC_IMG_ALIGN_RIGHT  = 1;
    const PC_IMG_ALIGN_CENTER = 2;

    /* "German" style
    const PC_PARSER_QUOTE_LEFT = '&#8222;';
    const PC_PARSER_QUOTE_RIGHT = '&#8220;');
    /**/
    /* "French" style */
    const PC_PARSER_QUOTE_LEFT  = '&raquo;';
    const PC_PARSER_QUOTE_RIGHT = '&laquo;';
    /**/

    protected $s = '';
    protected $m_stack;
    protected $m_handlers;
    protected $m_vars;

    /**
     *
     */
    public function addHandler(ProtoHandler $class)
    {
        $tags = explode('|', $class->name);

        $j = 0;

        foreach ($tags as $tag) {
            if (trim($tag) !== '') {
                $temp = array();
                $temp['name'] = $tag;

                if (is_array($class->type)) {
                    $temp['type'] = $class->type[$j];
                } else {
                    $temp['type'] = $class->type;
                }
                $temp['function'] = $class;

                $this->m_handlers[] = $temp;
            }

            $j++;
        }

        return $this;
    }

    /**
     *
     */
    public function addSetting($name, $value = '')
    {
        $this->m_vars[$name] = $value;
    }

    /**
     *
     */
    protected function printHandlerMarkup(Tag $tag, $front = true, $tag_content = '')
    {
        $data = array();

        $data['tag']    = $tag->getName();
        $data['params'] = $tag->getAttributes();
        $data['front']  = $front;
        $data['vars']   = $this->m_vars;

        if ($tag_content !== '') {
            $data['content'] = $tag_content;
        }

        $i = 0;

        $tagCnt = count($this->m_handlers);

        while (($i < $tagCnt) && ($this->m_handlers[$i]['name'] !== $data['tag'])) {
            $i++;
        }

        return $this->m_handlers[$i]['function']->draw($data);
    }

    /**
     * Gets the next tag
     *
     * @param  string $s        String to parse
     * @param  int    $i        Offset where search begins
     * @param  int    $position Will be filled with next tag's offset (FALSE if
     *                          there are no more tags)
     * @return Tag
     */
    protected function getNextTag(&$s, $i, &$position)
    {
        $j = mb_strpos($s, '[', $i);
        $k = mb_strpos($s, ']', $j + 1);

        if ($j === false || $k === false) {
            $position = false;
            return null;
        }

        $t = mb_substr($s, $j + 1, $k - ($j + 1));
        $l = mb_strrpos($t, '[');

        if ($l !== false) {
            $j += $l + 1;
        }

        $position = $j;

        $tagString = mb_substr($s, $j, $k - $j + 1);

        return new Tag($tagString, $this);
    }

    /**
     *
     * @return
     */
    public function getHandlers()
    {
        return $this->m_handlers;
    }

    /**
     *
     */
    public function Parse($s)
    {
        // Cleaning the data that shall be parsed

        $s = trim($s);
        $s = str_replace("\r\n", "\n", $s);
        $s = str_replace("\r", "\n", $s);
        $s = preg_replace("/\n{3,}/", "\n\n", $s);

        $this->s = $s;

        // Preprocessing

        foreach ($this->m_handlers as $h) {
            $h['function']->initialize();
        }

        // Heavy lifting

        $ret = ($this->s === '') ? '' : $this->parseEx();

        // Postprocessing

        $data = array();
        $data['vars'] = $this->m_vars;

        foreach ($this->m_handlers as $h) {
            $data['tag'] = $h['name'];
            $ret = $h['function']->postProcess($ret, $data);
        }

        return trim($ret);
    }

    /**
     *
     * @param  Tag  $tag
     * @return bool
     */
    protected function fitsStack(Tag $tag)
    {
        return ($tag->getName() === $this->m_stack->top()->getName());
    }

    /**
     *
     */
    protected function parseEx()
    {
        $ret = '';

        $cdata = '';
        $last_pos = 0;
        $f_clear_content = false;

        $this->m_stack = new SplStack();

        $tag_content = '';
        $pos = 0;

        $tag = $this->getNextTag($this->s, $pos, $tag_pos);

        while ($tag !== null) {
            // Handle all occurences of "[...]" that are not part of the list of
            // registered tags (m_handlers) as CDATA
            $executeTag = $tag->isValid();

            // If we are parsing inside of a TAG_PRE tag, do not execute current
            // tag (= pretend it is CDATA) unless it is the corresponding
            // closing tag to the active TAG_PRE tag
            if ($executeTag
                && $this->m_stack->count() > 0
                && $this->m_stack->top()->isOfType(self::TAG_PRE)
            ) {
                $executeTag = ($tag->isClosingTag() && $this->fitsStack($tag));
            }

            if ($executeTag) {
                // Tag is valid and not inside of a TAG_PRE tag, execute it

                // Get CDATA
                $cdata .= $this->formatString(mb_substr($this->s, $last_pos,
                        $tag_pos - $last_pos));

                if (!$tag->isClosingTag()) {
                    // Opening tag

                    if (!$tag->isOfType(self::TAG_INLINE)) {
                        // Opening tag, outline tag

                        if ($f_clear_content) {
                            $tag_content .= $this->printCData($cdata, true);
                            $tag_content .= $this->printHandlerMarkup($tag, true);
                        } else {
                            $ret .= $this->printCData($cdata, true);
                            $ret .= $this->printHandlerMarkup($tag, true);
                        }

                        // If clear content tag, detect content and skip parsing
                        if ($tag->isOfType(self::TAG_CLEAR_CONTENT)) {
                            $f_clear_content = true;
                        }
                    } else {
                        // Opening tag, inline tag

                        $cdata .= $this->printHandlerMarkup($tag, true);
                    }

                    if (!$tag->isOfType(self::TAG_SINGLE)) {
                        $this->m_stack->push($tag);
                    }
                } else {
                    // Closing tag

                    if (!$tag->isOfType(self::TAG_INLINE)) {
                        // Closing tag, outline tag

                        if ($tag->isOfType(self::TAG_CLEAR_CONTENT)) {
                            // Closing tag, outline tag, clear content tag

                            $f_clear_content = false;
                            $tag_content .= $this->printCData($cdata);
                            $ret .= $this->printHandlerMarkup($tag, false, $tag_content);
                            $tag_content = '';
                        } else {
                            // Closing tag, outline tag, NOT clear content tag

                            if ($f_clear_content) {
                                $tag_content .= $this->printCData($cdata);
                                $tag_content .= $this->printHandlerMarkup($tag, false);
                            } else {
                                $ret .= $this->printCData($cdata);
                                $ret .= $this->printHandlerMarkup($tag, false);
                            }
                        }
                    } else {
                        // Closing tag, inline tag

                        $cdata .= $this->printHandlerMarkup($tag, false);
                    }

                    // Tag complete, remove from stack
                    if ($this->fitsStack($tag)) {
                        $this->m_stack->pop();
                    } else {
                        // Markup error
                    }
                }
            } else {
                // Tag is CDATA

                $cdata .= $this->formatString(mb_substr($this->s,
                        $last_pos, $tag_pos - $last_pos) . $tag->getRawData());
            }

            $pos = $tag_pos + mb_strlen($tag->getRawData());
            $last_pos = $pos;
            $tag = $this->getNextTag($this->s, $pos, $tag_pos);
        }

        // Add string data after last tag as CDATA
        $cdata .= $this->formatString(mb_substr($this->s, $last_pos));
        $ret   .= $this->printCData($cdata, true);

        return $ret;
    }

    /**
     * Formats small pieces of CDATA
     *
     * @param  string $s
     * @return string
     */
    protected function formatString($s)
    {
        static $last_tag = null;

        if ($this->m_stack->count() > 0
            && $this->m_stack->top()->isOfType(self::TAG_PRE)
        ) {
            // Do not format text inside of TAG_PRE tags

            return $s;
        }

        /*
         * TODO Replace double-quotes alternately with nice left and right
         * quotes
         */

        if ($last_tag !== null) {
            #echo $last_tag->getName();
        }

        #echo '|' . $s . '|';

        // opening quote
        if ($last_tag !== null && $last_tag->isOfType(self::TAG_INLINE)) {
            $s = preg_replace('/([\s])&quot;/', '\1&raquo;', $s);

            #echo 'without';
        } else {
            $s = preg_replace('/([\s]|^)&quot;/', '\1&raquo;', $s);
        }

        // [X][X] will always be rendered as [S][S], not as [S][E]
        $s = preg_replace('/(&raquo;)&quot;/', '\1&raquo;', $s);

        #echo '<br />';

        // everything else is closing quote
        $s = str_replace('&quot;', '&laquo;', $s);

        $s = str_replace('--', '&ndash;', $s);

        if ($this->m_stack->count() > 0) {
            $last_tag = $this->m_stack->top();
        } else {
            $last_tag = null;
        }

        return $s;
    }

    /**
     * Formats whole blocks of CDATA
     *
     * @param  &string $cdata
     * @param  boolean $outline
     * @return string
     */
    protected function printCData(&$cdata, $outline = false)
    {
        $cdata = trim($cdata);
        $ret = '';

        /*$t = '';

        if ($outline) {
            //echo $tag->getName();
            $t = ' yes';
        }*/

        if ($cdata === '') {
            return $ret;
        }

        if (
            // All top-level blocks of CDATA have to be surrounded with <p>
            //($this->m_stack->size() == 0)

            // An outline tag starts after this CDATA block
            //|| ($tag != null)
            /*||*/ $outline

            /*
             * Obvious. Should make a difference between \n and \n\n though
             * TODO
             */
            || (mb_strpos($cdata, "\n"))

            /* TODO Add FORCE_PARAGRAPHS parameter to tags (li?, blockquote, ...) */
            || ($this->m_stack->count() > 0
                    && $this->m_stack->top()->isOfType(self::TAG_FORCE_PARAGRAPHS))
        ) {
            if ($this->m_stack->count() > 0
                && $this->m_stack->top()->isOfType(self::TAG_PRE)
            ) {
                /*
                 * We are inside of a TAG_PRE tag and do not want the CDATA to
                 * be reformatted
                 */

                //$ret .= '[CDATA' . $t . ']' . $cdata . '[/CDATA]';
                $ret .= $cdata;
            } else {
                $cdata = str_replace("\n\n", '</p><p>', $cdata);
                $cdata = str_replace("\n", "<br />\n", $cdata);
                $cdata = str_replace('</p><p>', "</p>\n\n<p>", $cdata);
                //$ret .= '<p>' . '[CDATA' . $t . ']' . $cdata . '[/CDATA]' . "</p>\n";
                $ret .= '<p>' . $cdata . "</p>\n";
            }
        } else {
            /* No need to add paragraph markup (something like [li]CDATA[/li]) */

            //$ret .= '[CDATA' . $t . ']' . $cdata . '[/CDATA]';
            $ret .= $cdata;
        }

        $cdata = '';

        return $ret;
    }
}
