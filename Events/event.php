<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:04
 */

namespace Events;

class Event{

    public function newEvent(){
        echo "Penis";
    }

}

$event = new Event();

$event->newEvent();