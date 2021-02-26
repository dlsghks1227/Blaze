<?php
namespace BlazeBase\Players;

use BlazeBase\Cards\Cards;
use BlazeBase\Cards\BattingCards;
use BlazeBase\Cards\TrophyCards;
use BlazeBase\Game\Notifications;

class Player extends \APP_GameClass
{
    protected $id;
    protected $no;
    protected $name;
    protected $color;
    protected $role;
    protected $eliminated = false;
    protected $zombie = false;

    public function __construct($row) {
        if (is_null($row) == false) {
            $this->id = $row['player_id'];
            $this->no = (int)$row['player_no'];
            $this->name = $row['player_name'];
            $this->color = $row['player_color'];
            $this->role = $row['player_role'];
            $this->eliminated = $row['player_eliminated'] == 1;
            $this->zombie = $row['player_zombie'] == 1;
        }
    }

    public function getId()         { return $this->id; }
    public function getNo()         { return $this->no; }
    public function getName()       { return $this->name; }
    public function getColor()      { return $this->color; }
    public function getRole()       { return $this->role; }
    public function isEliminated()  { return $this->eliminated;}
    public function isZombie()      { return $this->zombie; }
    
    public function getData($current_player_id = null) {
        $current = $this->id == $current_player_id;
        return array(
            'id'            => $this->id,
            'no'            => $this->no,
            'name'          => $this->name,
            'color'         => $this->color,
            'role'          => $this->role,
            'eliminated'    => $this->eliminated,
            'hand'          => ($current) ? array_values(Cards::getHand($this->id)) : Cards::countCards('hand', $this->id),
            'tokenCards'    => ($current) ? array_values(BattingCards::getHand($this->id)) : BattingCards::countCards('hand', $this->id),
            'score'         => $this->getScore(),
        );
    }

    public function getScore() {
        $total_score = 0;
        $betting_cards = BattingCards::getBettedCards();
        foreach ($betting_cards as $card) {
            if ($card['location_arg'] == $this->id) {
                $total_score += $card['value'];
            }
        }
        $trophy_cards = TrophyCards::getHandCards();
        foreach ($trophy_cards as $card) {
            if ($card['location_arg'] == $this->id) {
                $total_score += $card['value'];
            }
        }
        return $total_score;
    }

    public function getRoleFormat() {
        return ($this->role == ATTACKER ? 'attacker' : ($this->role == DEFENDER ? 'defender' : 'volunteer'));
    }

    public function save() {
        $eliminated = ($this->eliminated) ? 1 : 0;
        self::DbQuery("UPDATE player SET `player_eliminated` = $eliminated, `player_role` = {$this->role} WHERE `player_id` = {$this->id}");
    }

    public function eliminate($is_eliminated) {
        $this->eliminated = $is_eliminated;
        $this->save();
    }

    public function updateRole($role) {
        $this->role = $role;
        $this->save();
        if ($role != 0) {
            Notifications::changeRole($this);
        }
    }

    public function drawCards($amount) {
        if ($amount > 0) {
            $cards = Cards::draw($amount, $this->id);
            Notifications::drawCards($this, $cards);
        }
    }

    public function attack($cards) {
        Cards::moveAttackCards($cards);
        Notifications::attack($this, $cards);
    }

    public function defense($cards) {
        Cards::moveDefenseCards($cards);
        Notifications::defense($this, $cards);
    }
}
