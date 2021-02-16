<?php
namespace Blaze\Players;

use Blaze\Cards\Cards;
use Blaze\Game\Notifications;

class Player extends \APP_GameClass
{
    protected $id;
    protected $no;
    protected $name;
    protected $color;
    protected $zombie = false;

    public function __construct($row) {
        if (is_null($row) == false) {
            $this->id = $row['player_id'];
            $this->no = (int)$row['player_no'];
            $this->name = $row['player_name'];
            $this->color = $row['player_color'];
            $this->zombie = $row['player_zombie'] == 1;
        }
    }

    public function GetId()     { return $this->id; }
    public function GetNo()     { return $this->no; }
    public function GetName()   { return $this->name; }
    public function GetColor()  { return $this->color; }
    public function IsZombie()  { return $this->zombie; }

    public function DrawCards($amount) {
        $cards = Cards::Draw($amount, $this->id);
    }
}