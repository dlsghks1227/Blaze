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

    public function format()
    {
        return array(
            "id" => $this->id,
            "type" => $this->type,
            "value" => $this->value
        );
    }

    public function GetId()     { return $this->id; }
    public function GetType()   { return $this->type; }
    public function GetValue()  { return $this->value; }
}