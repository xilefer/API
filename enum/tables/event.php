<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 15:09
 */

namespace enum\tables;

use \enum\Enum;

class event extends Enum{

    const EventID = 'EventID';
    const Name = 'Name';
    const Location = 'Location';
    const Starttime = 'Starttime';
    const Endtime = 'Endtime';
    const Participants = 'Participants';
    const MeetingPoint = 'MeetingPoint';
    const Description = 'Description';
    const OwnerID = 'Owner';
    const Status = 'Status';
    const MaxParticipants = 'MaxParticipants';
    const Transport = 'Transport';
}