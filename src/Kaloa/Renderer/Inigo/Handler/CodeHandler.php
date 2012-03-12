<?php

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Handler\ProtoHandler;
use Kaloa\Renderer\Inigo\Parser;

/**
 *
 */
class CodeHandler extends ProtoHandler
{
    private $lang;

    public function __construct()
    {
        $this->name = "code";

        $this->type = Parser::TAG_OUTLINE | Parser::TAG_PRE
                | Parser::TAG_CLEAR_CONTENT;
    }

    public function draw(array $data)
    {
        /** @TODO */
        #global $quisp_parser;

        #$conf = KaloaConfig::getInstance();
        #$qpath = $conf->getSetting(KaloaConfig::S_PATH_SYSTEM);

        if ($data['front']) {
            $lang = '';
            if (isset($data['params']['(default)'])) {
                $lang = $data['params']['(default)'];
            } else if (isset($data['params']['lang'])) {
                $lang = $data['params']['lang'];
            }

            $this->lang = $lang;

            return '<pre>';
        } else {
            $ret = $data['content'];

            /*if ($this->lang == 'nxscript') {
                if (!isset($quisp_parser[$this->lang])) {
                    include_once $qpath . 'quisp/nxscript/QuispLinksNxScript.php';
                    $quisp_parser[$this->lang] = new Quisp($qpath .'quisp/nxscript/');
                    $quisp_parser[$this->lang]->setOption(Quisp::EXTENSION_LINKS_ACTIVE, TRUE);
                    $quisp_parser[$this->lang]->setOption(Quisp::EXTENSION_LINKS, new QuispLinksNxScript());
                }
                $ret = $quisp_parser[$this->lang]->parseToHtml($ret);
            } elseif ($this->lang == 'php') {
                if (!isset($quisp_parser[$this->lang])) {
                    $quisp_parser[$this->lang] = new Quisp($qpath . 'quisp/php/');
                }
                $ret = $quisp_parser[$this->lang]->parseToHtml($ret);
            } elseif ($this->lang == 'delphi') {
                if (!isset($quisp_parser[$this->lang])) {
                    $quisp_parser[$this->lang] = new Quisp($qpath . 'quisp/delphi/');
                }
                $ret = $quisp_parser[$this->lang]->parseToHtml($ret);
            }*/

            return $ret . '</pre>' . "\n\n";
        }
    }
}
