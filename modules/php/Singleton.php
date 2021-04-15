<?php
namespace BlazeBase;

class Singleton extends \APP_GameClass
{
    public static function getInstance()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}