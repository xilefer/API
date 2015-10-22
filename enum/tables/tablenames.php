<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 16:21
 */
namespace enum\tables;

use enum\Enum;

class tablenames extends Enum{

    const Event = 'event';
    const EventMembers = 'eventmembers';
    const Group = 'group';
    const GroupEvents = 'groupevents';
    const GroupMembers = 'groupmember';
    const Location = 'location';
    const User = 'user';
}