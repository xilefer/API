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
    private $PDO;

    public function __construct()
    {
        $this->database = "applicationdb";
        $this->sqlserver = mysql_pconnect("localhost", "root");
        try{
            $this->PDO = new \PDO("mysql:host=localhost;dbname=applicationdb","root","");
        }
        catch(\PDOException $e){
            echo $e->getMessage();
            exit;
        }
    }

    private function createEventID()
    {
        $query = "SELECT * FROM event WHERE EventID=:EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        do{
            $rand = rand(0,99999999999);
            $stmt->bindParam(":EventID",$rand,$PDO::PARAM_INT);
        }while($stmt->execute());
        return $rand;
    }

    //OwnerID ist die UserID des Erstellers und somit auch Gruppenadministrator
    public function newEvent($Name, $LocationID, $Starttime, $Endtime, $MeetingPoint, $Description, $OwnerID, $Status, $MaxParticipants, $Transport)
    {
        //Ermitteln der EventID
        $PDO = $this->PDO;
        $EventID = $this->createEventID();
        $query = "INSERT INTO event(`EventID`,`Name`,`Location`,`Starttime`,`Endtime`,`Participants`,`MeetingPoint`,`Description`,`Owner`,`Status`,`MaxParticipants`,`Transport`) VALUES('$EventID','$Name','$LocationID','$Starttime','$Endtime','Andere Tabelle','$MeetingPoint','$Description','$OwnerID','$Status','$MaxParticipants','$Transport') ";
        $stmt = $PDO->prepare($query);
        if ($stmt->execute())
        {
            //Owner als Teilnehmer eintragen
            if ($this->addParticipant($OwnerID, $EventID, 'PLANNED') != 'ERROR')
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

    //Methode darf nur vom GroupOwner ausgeführt werden
    public function deleteEvent($EventID, $OwnerID)
    {
        if($this->isGroupAdmin($OwnerID,$EventID))
        {
            $query = "DELETE  FROM event WHERE EventID = :EventID AND OwnerID = :OwnerID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
            $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
            if($stmt->execute()){
                //Löschen aller Teilnehmer des Events
                $query = "DELETE FROM eventmembers WHERE EventID = :EventID";
                $stmt = $this->PDO->prepare($query);
                $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
                if($stmt->execute()) return 'Successful';
                else return 'Error';
            }
            else{

                return 'Error';
            }
        }
    }

    #region Change-Methoden

    public function setValue($Param,$Value,$EventID,$UserID)
    {
        if($this->isEventOwner($UserID,$EventID))
        {
            $query = "UPDATE event SET :Param = :Value WHERE EventID = :EventID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":Param",$Param,$PDO::PARAM_STR);
            $stmt->bindParam(":Value",$Value, $PDO::PARAM_STR);
            $stmt->bindParam(":EventID",$EventID, $PDO::PARAM_STR);
            if($stmt->execute()) return 'Successful';
            else return 'Error';
        }
        else return 'User is not Groupadmin';
    }
/*
    public function changeName($Name, $EventID)
    {
        $query = "UPDATE event SET Name = :Name WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID, $PDO::PARAM_INT);
        $stmt->bindParam(":Name",$Name,$PDO::PARAM_STR);
        if ($stmt->execute()) {
            return 'Successful';
        } else return 'Error';
    }

    public function changeLocation($LocationID, $EventID)
    {
        $query = "UPDATE event SET Location = :LocationID WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if ($stmt->execute()) {
            return 'Successful';
        } else return 'Error';
    }

    public function changeStarttime($EventID, $Starttime){
        $query = "UPDATE event SET Starttime = :Starttime WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt=$PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":Starttime",$Starttime,$PDO::PARAM_STR);
        if($stmt->execute() == FALSE) return 'Error';
        else return 'Successful';
    }

    public function changeEndtime($Endtime, $EventID)
    {
        $query = "UPDATE event SET Endtime = :Endtime WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":Endtime",$Endtime,$PDO::PARAM_STR);
        if($stmt->execute() == FALSE) return 'Error';
        else return 'Successful';
    }

    public function changeDescription($Description, $EventID)
    {
        $query = "UPDATE event SET Description = $Description WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if ($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function changeMeetingPoint($MeetingPoint, $EventID)
    {
        $query = "UPDATE event SET MeetingPoint = $MeetingPoint WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if ($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function changeStatus($UserID, $EventID, $Status)
    {
        $query = "UPDATE eventmembers SET Status = :Status WHERE UserID = :UserID AND EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":Status",$Status,$PDO::PARAM_STR);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function changeTransport($EventID, $Transport)
    {
        $query = "UPDATE event SET Transport = :Transport WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":Transport",$Transport,$PDO::PARAM_STR);
        $stmt->bindParam(":EventID", $EventID,$PDO::PARAM_INT);
        if($stmt->execute() == FALSE) return 'Error';
        else return 'Successful';
    }

    public function changeMaxParticipants($MaxParticipants, $EventID)
    {
        $query = "UPDATE event SET MaxParticipants = :MaxParticipants WHERE EventID =:EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":MaxParticipants",$MaxParticipants,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute() == false) return 'Error';
        else return 'Successful';
    }
*/

#endregion



    public function addParticipant($UserID, $EventID, $Status)
    {
        //Evtl. Noch auf PDO umstellen
        $PDO = $this->PDO;
        $query = "SELECT MaxParticipants FROM event WHERE EventID = :EventID";
        $result = mysql_db_query($this->database,$query,$this->sqlserver);
        $query = "SELECT * FROM eventmembers WHERE EventID=$EventID";
        $result1 = mysql_db_query($this->database,$query,$this->sqlserver);
        if(mysql_affected_rows($result1) == $result) return 'Reached MaxParticipants';

        $query = "SELECT * FROM eventmebmers WHERE UserID = $UserID AND EventID = $EventID";
        if (mysql_affected_rows(mysql_db_query($this->database, $query, $this->sqlserver)) == 0) {
            $query = "INSERT INTO `eventmembers`(`UserID`, `EventID`, `Status`) VALUES ('$UserID','$EventID', '$Status')";
            if (mysql_affected_rows(mysql_db_query($this->database, $query, $this->sqlserver)) != 1) return 'Error';
            else return 'Successful';
        } else {
            return 'Eventmember already existing';
        }
    }

    public function deleteParticipant($UserID, $EventID)
    {
        $query = "DELETE FROM eventmembers WHERE EventID = :EventID AND UserID = :UserID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function addGroup($EventID,$GroupID)
    {
        $PDO = $this->PDO;
        $query = "INSERT into groupevents (`GroupID`,`EventID`) VALUES (':GroupID',':EventID')";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function removeGroup($EventID,$GroupID)
    {
        $PDO = $this->PDO;
        $query = "DELETE FROM groupevents WHERE EventID=:EventID AND GroupID=:GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function getGroupsForEvent($EventID)
    {
        $PDO = $this->PDO;
        $query ="SELECT GroupID FROM groupevents WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll();
        else return 'Error';
    }

    public function isParticipant($UserID,$EventID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM eventmember WHERE EventID = :EventID AND UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 1) return TRUE;
            else return FALSE;
        }
        return FALSE;
    }

    private function isEventOwner($UserID,$EventID)
    {
        $query = "SELECT OwnerID FROM event WHERE EventID =:EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute())
        {
            if($UserID == $stmt->fetchColumn()) return TRUE;
            else return FALSE;
        }
        else return 'Error';
    }

    public function getEventProperties($EventID)
    {
        $query = "SELECT EventID,Name,Location,Starttime,Endtime,MeetingPoint,Description,Status,MaxParticipants,Transport FROM event WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll();
        else return 'Error';
    }
}

