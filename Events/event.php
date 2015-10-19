<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:04
 */

namespace Events;

class Event
{

    private $database;
    private $sqlserver;

    public function __construct()
    {
        $this->database = "applicationdb";
        $this->sqlserver = mysql_pconnect("localhost", "root");
    }

    private function getEventID($ID)
    {
        $query = "SELECT * FROM event WHERE EventID=$ID";
        if (mysqli_affected_rows(mysqli_query($this->database, $query, $this->sqlserver)) != 0) {
            return TRUE;
        } else return FALSE;
    }

    public function newEvent($Name, $LocationID, $Starttime, $Endtime, $MeetingPoint, $Description, $OwnerID, $Status, $MaxParticipants, $Transport)
    {
        //Ermitteln der EventID
        do {
            $ID = rand(0, 99999999999);
        }
        while ($this->getEventID($ID));
        $query = "INSERT INTO event(`EventID`,`Name`,`Location`,`Starttime`,`Endtime`,`Participants`,`MeetingPoint`,`Description`,`Owner`,`Status`,`MaxParticipants`,`Transport`) VALUES('$ID','$Name','$LocationID','$Starttime','$Endtime','Andere Tabelle','$MeetingPoint','$Description','$OwnerID','$Status','$MaxParticipants','$Transport') ";
        if (mysqli_affected_rows(mysqli_query($this->database, $query, $this->sqlserver)) == 1)
        {
            //Owner als Teilnehmer eintragen
            if ($this->addParticipant($OwnerID, $ID, 'PLANNED') != 'ERROR')
            {
                return 'Successful';
            }
            else
            {
                return "Error";
            }
        }
        else
        {
            return 'Error';
        }
    }

    public function deleteEvent($EventID, $OwnerID)
    {
        $query = "DELETE  FROM event WHERE EventID = $EventID AND OwnerID = $OwnerID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == -1) return 'Error';
        else{
            //Löschen aller Teilnehmer des Events
            $query = "DELETE FROM eventmebers WHERE EventID = $EventID";
            $result = mysql_db_query($this->database,$query,$this->sqlserver);
            if($result == -1) return 'Error';
            else return 'Successful';
        }
    }

    public function changeName($Name, $EventID)
    {
        $sqlserver = $this->sqlserver;
        $database = $this->database;
        $query = "UPDATE event SET Name = $Name WHERE EventID = $EventID";
        $result = mysql_db_query($database, $query, $sqlserver);
        if (mysql_affected_rows($result) < 1) {
            return 'Error';
        } else return 'Successful';
    }

    public function changeLocation($LocationID, $EventID)
    {
        $query = "UPDATE event SET Location = $LocationID WHERE EventID = $EventID";
        $result = mysql_db_query($this->database, $query, $this->sqlserver);
        if (mysql_affected_rows($result) < 1) {
            return 'Error';
        } else return 'Successful';
    }

    public function changeStarttime($EventID, $Starttime){
        $query = "UPDATE event SET Starttime = $Starttime WHERE EventID = $EventID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return 'Successful';
    }

    public function changeEndtime($Endtime, $EventID)
    {
        $query = "UPDATE event SET Endtime = $Endtime WHERE EventID = $EventID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return 'Successful';
    }

    public function changeDescription($Description, $EventID)
    {
        $query = "UPDATE event SET Description = $Description WHERE EventID = $EventID";
        $result = mysql_db_query($this->database, $query, $this->sqlserver);
        if (mysql_affected_rows($result) < 1) return 'Error';
        else return 'Successful';
    }

    public function changeMeetingPoint($MeetingPoint, $EventID)
    {
        $query = "UPDATE event SET MeetingPoint = $MeetingPoint WHERE EventID = $EventID";
        $result = mysql_db_query($this->database, $query, $this->sqlserver);
        if (mysql_affected_rows($result) < 1) return 'Error';
        else return 'Successful';
    }

    public function changeStatus($UserID, $EventID, $Status)
    {
        $query = "UPDATE eventmebers SET Status = $Status WHERE UserID = $UserID AND EventID = $EventID";
        if (mysql_affected_rows(mysql_db_query($this->database, $query, $this->sqlserver)) < 1) return 'Error';
        else return 'Successful';
    }

    public function changeTransport($EventID, $Transport)
    {
        $query = "UPDATE event SET Transport = $Transport WHERE EventID = $EventID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == FALSE) return 'Error';
        else return 'Successful';
    }

    public function changeMaxParticipants($MaxParticipants, $EventID)
    {
        $query = "UPDATE event SET MaxParticipants = $MaxParticipants WHERE EventID =$EventID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        if($result == -1) return 'Error';
        else return 'Successful';
    }

    public function addParticipant($UserID, $EventID, $Status)
    {
        $query = "SELECT MaxParticipants FROM event WHERE EventID = $EventID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        $query = "SELECT * FROM eventmembers WHERE EventID=$EventID";
        $result1 = mysql_db_query($this->database,$query,$this->sqlserver);
        if(mysql_affected_rows($result1) == $result) return 'Reached MaxParticipants';

        $query = "SELECT * FROM eventmebmers WHERE UserID = $UserID AND EventID = $EventID";
        if (mysql_affected_rows(mysql_db_query($this->database, $query, $this->sqlserver)) == 0) {
            $query = "INSERT INTO `eventmembers`(`UserID`, `EventID`, `Status`) VALUES ('$UserID'','$EventID'', '$Status'')";
            if (mysql_affected_rows(mysql_db_query($this->database, $query, $this->sqlserver)) != 1) return 'Error';
            else return 'Successful';
        } else {
            return 'Eventmember already existing';
        }
    }

    public function deleteParticipant($UserID, $EventID, $Status)
    {
        $query = "DELETE FROM eventmembers WHERE EventID = $EventID AND UserID = $UserID";
        $result = mysql_db_query($this->databse,$query,$this->sqlserver);
        if(mysql_affected_rows($result) < 1) return 'Error';
        else return 'Successful';
    }

}
