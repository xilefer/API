<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 15:09
 */

namespace enum\tables;

use \enum\Enum;

class group extends  Enum{

    const GroupID = 'GroupID';
    const GroupName = 'GroupName';
    const Owner = 'Owner';
    const MaxMembers = 'MaxMembers';
    const CreationDate = 'CreationDate';
    const ModificationDate = 'ModificationDate';
    const Accessibility = 'Accessibility';
}