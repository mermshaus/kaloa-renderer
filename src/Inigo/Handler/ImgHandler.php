<?php

declare(strict_types=1);

namespace Kaloa\Renderer\Inigo\Handler;

use Kaloa\Renderer\Inigo\Parser;

final class ImgHandler extends ProtoHandler
{
    private string $last_img_align = '';

    public function __construct()
    {
        $this->name = 'img';
        $this->type = Parser::TAG_OUTLINE;

        $this->defaultParam = 'src';
    }

    private function drawImage(
        string $path,
        string $alt = '',
        string $title = '',
        int $align = Parser::PC_IMG_ALIGN_LEFT,
        string $dir = ''
    ): string {
        // width of framing div's border (one side)
        $border_width = 1;
        $padding      = 2;
        $ret          = '';
        $path         = $dir . $path;

        //$title = $this->ParseInline($title);

        $width = 150;
        $attr = 'width="150" height="150"';

        // Image exists?
        $imageData = @getimagesize($path);
        if ($imageData !== false) {
            list($width, $height, $type, $attr) = $imageData;
        }

        //$path = $this->m_rel_path . $path;

        if ($alt === '') {
            $alt = $path;
        }

        $style = 'width: ' . ($width + 2 * (1 + $border_width + $padding)) . 'px;';
        //$style2 .= "height: " . ($height + 2 * ($border_width )) . "px;";

        if ($align == Parser::PC_IMG_ALIGN_RIGHT) {
            $ret .= '<div class="img">' . "\n"
                    . '  <div class="img_right" style="' . $style . '">' . "\n"
                    . '    <div class="img_border">' . "\n"
                    . '      <img src="' . $this->e($path) . '" alt="Bild: ' . $this->e($alt) . '" title="'
                        . $this->e($alt) . '" ' . $attr . ' />' . "\n"
                    . '    </div>' . "\n"
                    . '    <div class="img_content">';
        } elseif ($align == Parser::PC_IMG_ALIGN_CENTER) {
            $ret .= '<div class="center">' . "\n"
                    . '  <div class="img">' . "\n"
                    . '    <div class="img_center" style="' . $style . '">' . "\n"
                    . '      <div class="img_border">' . "\n"
                    . '        <img src="' . $this->e($path) . '" alt="Bild: ' . $this->e($alt) . '" title="'
                        . $this->e($alt) . '" ' . $attr . ' />' . "\n"
                    . '      </div>' . "\n"
                    . '      <div class="img_content">';
        } elseif ($align == Parser::PC_IMG_ALIGN_LEFT) {
            $ret .= '<div class="img">' . "\n"
                    . '  <div class="img_left" style="' . $style . '">' . "\n"
                    . '    <div class="img_border">' . "\n"
                    . '      <img src="' . $this->e($path) . '" alt="Bild: ' . $this->e($alt) . '" title="'
                        . $this->e($alt) . '" ' . $attr . ' />' . "\n"
                    . '    </div>' . "\n"
                    . '    <div class="img_content">';
        }

        return $ret;
    }

    public function draw(array $data): string
    {
        $ret = '';

        if ($data['front']) {
            $src   = $this->fillParam($data, 'src', '');
            $title = $this->fillParam($data, 'title', '');
            $align = $this->fillParam($data, 'align', 'left');

            //$src = $this->m_image_path . $src;
            $this->last_img_align = $align;

            switch ($align) {
                case 'right':
                    $align = Parser::PC_IMG_ALIGN_RIGHT;
                    break;
                case 'center':
                    $align = Parser::PC_IMG_ALIGN_CENTER;
                    break;
                default:
                    $align = Parser::PC_IMG_ALIGN_LEFT;
                    break;
            }

            $ret = $this->drawImage($src, $title, '', $align, $data['vars']['image-dir']);
        } elseif ($this->last_img_align === 'center') {
            $this->last_img_align = '';
            $ret = "</div>\n    </div>\n  </div>\n</div>\n\n";
        } else {
            $ret = "</div>\n  </div>\n</div>\n\n";
        }

        return $ret;
    }
}
