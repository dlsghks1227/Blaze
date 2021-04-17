<?php
namespace BlazeBase\Players;


use BlazeBase\Game\Notifications;
use BlazeBase\Cards\Cards;

class Player extends \APP_GameClass
{
    protected $id;
    protected $no;
    protected $name;
    protected $color;
    protected $role;
    protected $eliminated   = false;
    protected $zombie       = false;

    public function __construct($row) {
        if (is_null($row) == false) {
            $this->id           = $row['player_id'];
            $this->no           = (int)$row['player_no'];
            $this->name         = $row['player_name'];
            $this->color        = $row['player_color'];
            $this->role         = $row['player_role'];
            $this->eliminated   = $row['player_eliminated'] == 1;
            $this->zombie       = $row['player_zombie'] == 1;
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
            'hand'          => ($current) ? array_values(Cards::getCardsInLocation('hand', $this->id))          : Cards::getCountCards('hand', $this->id),
            'bettingHand'   => ($current) ? array_values(Cards::getCardsInLocation('betting_hand', $this->id))  : Cards::getCountCards('betting_hand', $this->id),
            'score'         => $this->getScore(),
        );
    }

    public function getScore()
    {
        $total_score = 0;
        $betted_cards = Cards::getCardsInLocation('betted', $this->id);
        $trophy_cards = Cards::getCardsInLocation('trophy', $this->id);
        foreach ($betted_cards as $card)
        {
            $total_score += $card['value'];
        }
        foreach ($trophy_cards as $card)
        {
            $total_score += $card['value'];
        }
        return $total_score;
    }

    public function getRoleFormat()
    {
        return $this->role == ROLE_ATTACKER ? 'attacker' : ($this->role == ROLE_DEFENDER ? 'defender' : 'supporter');
    }

    private function save()
    {
        $eliminated = ($this->eliminated) ? 1 : 0;
        self::DbQuery("UPDATE player SET `player_eliminated` = $eliminated, `player_role` = {$this->role} WHERE `player_id` = {$this->id}");
    }

    public function eliminate($is_eliminated)
    {
        $this->eliminated = $is_eliminated;
        $this->save();
    }

    public function changeRole($role)
    {
        $this->role = $role;
        $this->save();

        // Notifications
        if ($role != ROLE_NONE)
        {
            Notifications::changeRolePrivate($this);
        }
    }

    public function drawCards($amount)
    {
        if ($amount > 0)
        {
            $cards = Cards::draw($amount, $this->id);
            // Notifications
            Notifications::draw($this, $cards);
        }
    }

    public function attack($cards)
    {
        $relocation_cards = Cards::moveAttackCards($cards);

        // Notifications
        Notifications::attack($this, $relocation_cards);
    }

    public function defense($cards)
    {
        $relocation_cards = Cards::moveDefenseCards($cards);

        // Notifications
        Notifications::defense($this, $relocation_cards);
    }

    public function betting($betting_card, $selected_player_id)
    {
        // Notifications
        Notifications::bettingPrivate($this, $betting_card, $selected_player_id);
    }
}