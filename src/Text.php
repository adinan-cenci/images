<?php
namespace AdinanCenci\Images;

class Text 
{
    protected $text             = 'Foo bar';
    protected $lines            = [];
    protected $wordWidths       = [];

    protected $fontSize         = 16; // pt
    protected $lineHeight       = 16; // pt
    protected $color            = '#fff';
    protected $alignment        = 'left';
    protected $fontFile         = 1;

    protected $width            = null;
    protected $maxWidth         = null;
    
    protected $paddingTop       = 0;
    protected $paddingRight     = 0;
    protected $paddingBottom    = 0;
    protected $paddingLeft      = 0;

    protected $background       = null;

    protected $imageWidth       = 0;
    protected $imageHeight      = 0;

    public function __construct($text) 
    {
        $this->text = str_replace("\r", '', $text);
    }

    public function width($w) 
    {
        $this->width = $w;
        return $this;
    }

    public function maxWidth($max) 
    {
        $this->maxWidth = $max;
        return $this;
    }

    public function padding($pdT, $pdR = null, $pdB = null, $pdL = null) 
    {
        if (!$pdR && !$pdB && !$pdL) {
            $this->paddingTop = $this->paddingRight = $this->paddingBottom = $this->paddingLeft = $pdT;
            return $this;
        }

        if (!$pdB && !$pdL) {
            $this->paddingTop   = $this->paddingBottom = $pdB;
            $this->paddingRight = $this->paddingLeft   = $pdL;
            return $this;
        }

        $this->paddingTop       = $pdT;
        $this->paddingBottom    = $pdB;
        $this->paddingRight     = $pdR;
        $this->paddingLeft      = $pdL;

        return $this;
    }

    public function background($bckg) 
    {
        $this->background = $bckg;
        return $this;
    }

    public function color($color) 
    {
        $this->color = $color;
        return $this;
    }

    public function fontFile($fontFile) 
    {
        $this->fontFile = $fontFile;
        return $this;
    }

    public function fontSize($ftSz) 
    {
        $this->fontSize = $ftSz;
        return $this;
    }

    public function lineHeight($lH) 
    {
        $this->lineHeight = $lH;
        return $this;
    }

    public function alignment($alg) 
    {
        $this->alignment = $alg;
        return $this;
    }

    public function align($alg) 
    {
        return $this->alignment($alg);
    }

    public function getImage() 
    {
        $this->sortLines();

        switch ($this->alignment) {
            case 'left':
                return $this->printTextLeft();
                break;            
            case 'center':
                return $this->printTextCenter();
                break;
            case 'right':
                return $this->printTextRight();
                break;
            case 'justify':
                return $this->printTextJustify();
                break;
        }
    }

    public function sortLines() 
    {        
        $this->lines    = explode("\n", $this->text);
        
        if ($this->maxWidth == false and $this->width == false) {
            $d                  = $this->textDim($this->text);
            $this->imageWidth   = $d['width'] + $this->paddingLeft + $this->paddingRight;
            $this->imageHeight  = $d['height'] + $this->paddingTop + $this->paddingBottom;
            return;
        } else if ($this->width) {
            $this->imageWidth = $this->width;
        }

        $longestLine    = 0;
        $mxLw           = $this->maxWidth ?: $this->width ?: 9000000000;
        $mxLw           = $mxLw - $this->paddingLeft - $this->paddingRight;
        $countLines     = count($this->lines);

        for ($lineN = 0; $lineN < $countLines; $lineN++) {

            $line   = $this->lines[$lineN];
            $w      = $this->textWidth($line);

            if ($w <= $mxLw) {
                continue;
            }

            $newLine = '';

            while ($w > $mxLw) {                
                $word       = $this->removeLastWord($line);                
                $newLine    = $word.' '.$newLine;
                $w          = $this->textWidth($line);
            }

            if ($w > $longestLine) {
                $longestLine = $w;
            }

            $newLine = trim($newLine);
            $this->lines[$lineN] = $line;
            array_splice($this->lines, $lineN, 1, [$line, $newLine]);
            $countLines++;
        }

        if (! $this->width) {
            $this->imageWidth   = $longestLine + $this->paddingLeft + $this->paddingRight;
        }

        $this->imageHeight  = $countLines * Image::pointToPixel($this->lineHeight) + $this->paddingTop + $this->paddingBottom;
    }

    protected function prepareImage() 
    {
        $image = new Image($this->imageWidth, $this->imageHeight);
        $image->paint($this->background);

        return $image;
    }

    protected function printTextLeft() 
    {
        $image      = $this->prepareImage();
        $lineHeight = Image::pointToPixel($this->lineHeight);
        
        $x          = $this->paddingLeft;
        $y          = $lineHeight + $this->paddingTop;

        foreach ($this->lines as $line) {
            $image->ttfText($this->fontSize, 0, $x, $y, $this->color, $this->fontFile, $line);
            $y += $lineHeight;
        }

        return $image;
    }

    protected function printTextRight() 
    {
        $image          = $this->prepareImage();
        $lineHeight     = Image::pointToPixel($this->lineHeight);
        
        $contentWidth   = $this->imageWidth - $this->paddingLeft - $this->paddingRight;
        $y              = $lineHeight + $this->paddingTop;

        foreach ($this->lines as $line) {

            $w = $this->textWidth($line);
            $x = $contentWidth - $w + $this->paddingLeft;

            $image->ttfText($this->fontSize, 0, $x, $y, $this->color, $this->fontFile, $line);
            $y += $lineHeight;
        }

        return $image;
    }

    protected function printTextCenter() 
    {
        $image          = $this->prepareImage();
        $lineHeight     = Image::pointToPixel($this->lineHeight);
        
        $y              = $lineHeight + $this->paddingTop;

        foreach ($this->lines as $line) {

            $w = $this->textWidth($line);
            $x = ($this->imageWidth - $w) / 2;

            $image->ttfText($this->fontSize, 0, $x, $y, $this->color, $this->fontFile, $line);
            $y += $lineHeight;
        }

        return $image;
    }

    protected function printTextJustify() 
    {
        $image          = $this->prepareImage();
        $lineHeight     = Image::pointToPixel($this->lineHeight);
        
        $y              = $lineHeight + $this->paddingTop;

        foreach ($this->lines as $line) {
            $x          = $this->paddingLeft;
            $words      = explode(' ', $line);
            $whiteSpace = ($this->imageWidth - $this->paddingLeft - $this->paddingRight - $this->textWidth(str_replace(' ', '', $line))) / (count($words) - 1) ;

            foreach ($words as $word) {
                $image->ttfText($this->fontSize, 0, $x, $y, $this->color, $this->fontFile, $word);
                $x += $this->textWidth($word) + $whiteSpace;
            }

            $y += $lineHeight;         
        }

        return $image;
    }

    protected function removeLastWord(&$lineOfText) 
    {
        $match = preg_match('/ ([^ ]+)$/', $lineOfText, $matches);
        $lineOfText = preg_replace('/ ([^ ]+)$/', '', $lineOfText);

        return $match ? $matches[1] : null;
    }

    protected function textDim($text) 
    {
        return Image::imageTtfBbox($this->fontSize, 0, $this->fontFile, $text);
    }

    protected function textWidth($text) 
    {
        if (isset($this->wordWidths[$text])) {
            return $this->wordWidths[$text];
        }

        return $this->wordWidths[$text] = $this->textDim($text)['width'];
    }

    public function textHeight($text) 
    {
        return $this->textDim($text)['height'];
    }

    public function getWidth() 
    {
        return $this->width ? $this->width : $this->imageWidth;
    }
}
