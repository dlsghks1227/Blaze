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
    protected $eliminated = false;
    protected $zombie = false;

    public function __construct($row) {
        if (is_null($row) == false) {
            $this->id = $row['player_id'];
            $this->no = (int)$row['player_no'];
            $this->name = $row['player_name'];
            $this->color = $row['player_color'];
            $this->eliminated = $row['player_eliminated'] == 1;
            $this->zombie = $row['player_zombie'] == 1;
        }
    }

    public function getId()         { return $this->id; }
    public function getNo()         { return $this->no; }
    public function getName()       { return $this->name; }
    public function getColor()      { return $this->color; }
    public function isEliminated()  { return $this->eliminated;}
    public function isZombie()      { return $this->zombie; }

    public function getData($current_player_id = null) {
        $current = $this->id == $current_player_id;
        return array(
            'id'            => $this->id,
            'no'            => $this->no,
            'name'          => $this->name,
            'color'         => $this->color,
            'eliminated'    => $this->eliminated,
            'hand'          => ($current) ? array_values(Cards::getHand($this->id, true)) : Cards::countCards('hand', $this->id),
        );
    }

    public function drawCards($amount) {
        $cards = Cards::draw($amount, $this->id);
    }
}