<?php
namespace Blaze\Cards;

use Blaze\Game\Notifications;

class Card extends \APP_GameClass
{
    protected $id;
    protected $type;
    protected $value;
    protected $location_arg;
    
    public function __construct($id = null, $type = null, $value = "", $location_arg = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->value = $value;
        $this->location_arg = $location_arg;
    }

    public function getData()
    {
        return array(
            "id" => $this->id,
            "type" => $this->type,
            "value" => $this->value,
            "location_arg" => $this->location_arg
        );
    }

    public function getId()             { return $this->id; }
    public function getType()           { return $this->type; }
    public function getValue()          { return $this->value; }
    public function getLocationArg()    { return $this->location_arg; }
}