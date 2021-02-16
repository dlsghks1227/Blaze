<?php
namespace Blaze\Cards;

use Blaze\Game\Notifications;

class Card extends \APP_GameClass
{
    protected $id;
    protected $type;
    protected $value;
    
    public function __construct($id = null, $type = null, $value = "")
    {
        $this->id = $id;
        $this->type = $type;
        $this->value = $value;
    }

    public function getData()
    {
        return array(
            "id" => $this->id,
            "type" => $this->type,
            "value" => $this->value
        );
    }

    public function getId()     { return $this->id; }
    public function getType()   { return $this->type; }
    public function getValue()  { return $this->value; }
}