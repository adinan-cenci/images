<?php
namespace AdinanCenci\Images;

/**
    TODO: Refactor this entire thing
*/

class Repeater 
{
    protected $image;

    protected $columns;
    protected $rows;

    protected $gutterH = 20;
    protected $gutterV = 20;

    protected $grid;

    protected $modifier;

    public function __construct($image) 
    {
        $this->image = $image;
        $this->modifier = function($column, $row) 
        {
            return array('x' => 0, 'y' => 0);
        };
    }

    public function modify($function) 
    {
        $this->modifier = $function;
        return $this;
    }

    public function columns($c) 
    {
        $this->columns = $c;
        return $this;
    }

    public function rows($r) 
    {
        $this->rows = $r;
        return $this;
    }

    public function order() 
    {
        $iterations = $this->columns * $this->rows;
        
        for ($row = 0; $row < $this->rows; $row++) {
            for ($column = 0; $column < $this->columns; $column++) {

                $index = $column.'x'.$row;
                
                $x = $column * ($this->image->width + $this->gutterV);
                $y = $row * ($this->image->height + $this->gutterH);

                //---------

                $deviation = call_user_func_array($this->modifier, array($column, $row));

                $x += $deviation['x'];
                $y += $deviation['y'];

                //---------

                $this->grid[$index] = array(
                    'x' => $x, 
                    'y' => $y
                );

                //---------

                if ($row == 0 && $y > 0) {
                    $this->grid[$column.'x'.($row-1)] = array(
                        'x' => $x, 
                        'y' => $y - $this->image->height - $this->gutterH
                    );
                }



            }
        }
    }

    public function generateImage() 
    {
        $end = end($this->grid);
        $width  = $end['x'] + $this->image->width;
        $height = $end['y'] + $this->image->height;

        //------------

        $thumb = new TrueColor($width, $height);
        $thumb->fill('rgba(255,255,255,127)');

        //------------

        foreach ($this->grid as $v) {

            $thumb->paste($this->image, $v['x'], $v['y']);
        }

        //------------

        return $thumb;
    }
}
