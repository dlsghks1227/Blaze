<?php
namespace BlazeBase\Cards;

class Card extends \APP_GameClass
{
    protected $id;
    protected $color;
    protected $value;
    protected $weight;
    
    public function __construct($id = null, $color = null, $value = "", $weigth = null)
    {
        $this->id       = $id;
        $this->color    = $color;
        $this->value    = $value;
        $this->weight   = $weigth;
    }

    public function getData()
    {
        return array(
            "id"        => $this->id,
            "color"     => $this->color,
            "value"     => $this->value,
            "weight"    => $this->weight
        );
    }

    public function getId()             { return $this->id; }
    public function getColor()          { return $this->color; }
    public function getValue()          { return $this->value; }
    public function getWeight()         { return $this->weight; }

    public function setWeight($weight)  { $this->weight = $weight; }
}