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
            $stmt->execute();
        }while($stmt->rowCount() != 0);
        return $rand;
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

    //OwnerID ist die UserID des Erstellers und somit auch Gruppenadministrator
    public function newEvent($Name, $LocationID, $Starttime, $Endtime, $MeetingPoint, $Description, $OwnerID, $Status, $MaxParticipants, $Transport)
    {
        //Ermitteln der EventID
        $PDO = $this->PDO;
        $EventID = $this->createEventID();
        $query = "INSERT INTO event(`EventID`,`Name`,`Location`,`Starttime`,`Endtime`,`Participants`,`MeetingPoint`,`Description`,`Owner`,`Status`,`MaxParticipants`,`Transport`) VALUES
          (:EventID,:EventName,:LocationID,:Starttime,:Endtime,'Andere Tabelle',:MeetingPoint,:Description,:OwnerID,:Status,:MaxParticipants,:Transport) ";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventName",$Name,$PDO::PARAM_STR);
        $stmt->bindParam(":LocationID",$LocationID,$PDO::PARAM_INT);
        $stmt->bindParam(":Starttime",$Starttime,$PDO::PARAM_STR);
        $stmt->bindParam(":Endtime",$Endtime,$PDO::PARAM_STR);
        $stmt->bindParam(":MeetingPoint",$MeetingPoint,$PDO::PARAM_STR);
        $stmt->bindParam(":Description",$Description,$PDO::PARAM_STR);
        $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
        $stmt->bindParam(":Status",$Status,$PDO::PARAM_STR);
        $stmt->bindParam(":MaxParticipants",$MaxParticipants,$PDO::PARAM_INT);
        $stmt->bindParam(":Transport",$Transport,$PDO::PARAM_STR);
        if ($stmt->execute())
        {
            //Owner als Teilnehmer eintragen
            if ($this->addParticipant($OwnerID, $EventID, 'PLANNED') != 'ERROR')
            {
                return 'Successful';
            }
            else
            {
                return "ErrorParticipant";
            }
        }
        else
        {
            return 'Error';
        }
    }//Index

    //Methode darf nur vom GroupOwner ausgeführt werden
    public function deleteEvent($EventID, $OwnerID)
    {
        if($this->isEventOwner($OwnerID,$EventID))
        {
            $query = "DELETE  FROM event WHERE EventID = :EventID AND Owner = :OwnerID";
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
        else return 'UserNotEventAdmin';
    }//Index

    #region Change-Methoden

    public function setValue($EventID,$Param,$Value,$UserID)
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
    }//Index

#endregion



    public function addParticipant($UserID, $EventID, $Status)
    {
        $PDO = $this->PDO;

        $query = "SELECT MaxParticipants FROM event WHERE EventID = :EventID";
        $stmt1 = $PDO->prepare($query);
        $stmt1->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt1->execute()){
            $query = "SELECT * FROM eventmembers WHERE EventID=:EventID";
            $stmt2= $PDO->prepare($query);
            $stmt2->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
            if($stmt2->execute()){
                $rows2 = $stmt2->rowCount();
                $Value1 = $stmt1->fetchColumn();
                if($rows2 == $Value1) return 'Reached MaxParticipants';

                $query = "SELECT * FROM eventmembers WHERE UserID = :UserID AND EventID = :EventID";
                $stmt3 = $PDO->prepare($query);
                $stmt3->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
                $stmt3->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
                if($stmt3->execute()){
                    if ($stmt3->rowCount() == 0) {
                        $query = "INSERT INTO `eventmembers`(`UserID`, `EventID`, `Status`) VALUES (:UserID,:EventID, :Status)";
                        $stmt4 = $PDO->prepare($query);
                        $stmt4->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
                        $stmt4->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
                        $stmt4->bindParam(":Status",$Status,$PDO::PARAM_STR);
                        if($stmt4->execute()) return 'Successful';
                    } else {
                        return 'Eventmember already existing';
                    }
                }


            }

        }

    }//Index

    public function deleteParticipant( $EventID, $UserID) // Admin darf jeden löschen
    {
        $query = "DELETE FROM eventmembers WHERE EventID = :EventID AND UserID = :UserID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }//Index

    public function addGroup($EventID,$GroupID)
    {
        $PDO = $this->PDO;
        $query = "INSERT into groupevents (`GroupID`,`EventID`) VALUES (:GroupID,:EventID)";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }//Index

    public function removeGroup($EventID,$GroupID)
    {
        $PDO = $this->PDO;
        $query = "DELETE FROM groupevents WHERE EventID=:EventID AND GroupID=:GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }//Index

    public function getGroupsForEvent($EventID)
    {
        $PDO = $this->PDO;
        $query ="SELECT GroupID FROM groupevents WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll();
        else return 'Error';
    }//Index

    public function replaceAdminWithParticipant($EventID,$DeletedUserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM `eventmembers` WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            if(count($data) == 1){
                $this->deleteEvent($EventID,$DeletedUserID);
                return 0;
            }
            foreach($data as $temp){
                if($temp != $DeletedUserID){
                    $query = "UPDATE `event` SET Owner = :OwnerID WHERE EventID = :EventID";
                    $stmt = $PDO->prepare($query);
                    $stmt->bindParam(":OwnerID",$temp,$PDO::PARAM_INT);
                    $stmt->bindParam(":EventID",$EventID);
                    if($stmt->execute()) return 0;
                    else return 1;
                }
            }
            return 0;
        }
        else{
            return '1';
        }
    }

    public function deleteUserFromEvent($UserID)
    {
        $PDO = $this->PDO;
        //User als Eventowner
        $query = "SELECT EventID FROM `event` WHERE Owner = :OwnerID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":OwnerID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $result = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            foreach($result as $temp){
                $this->replaceAdminWithParticipant($temp,$UserID);
            }
        }
        else return 1;
        //User als Eventteilnehmer
        $query = "DELETE FROM `eventmembers` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            return 0;
        }
        else return 1;
    }

    public function getEventsWhereUserIsParticipant($UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT EventID FROM eventmembers WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute())return $stmt->fetchAll($PDO::FETCH_COLUMN,0);
        else return 'Error';
    }

    public function getEventsWhereUserIsOwner($UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT EventID FROM event WHERE Owner = :OwnerID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":OwnerID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll($PDO::FETCH_COLUMN,0);
        else return 'Error';
    }

    public function getEventMember($EventID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM `eventmember` WHERE EventID = :EventID";
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
    }//Index

    public function getEventProperties($EventID)
    {
        $query = "SELECT EventID,Name,Location,Starttime,Endtime,MeetingPoint,Description,Status,MaxParticipants,Transport FROM event WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data=$stmt->fetchAll($PDO::FETCH_ASSOC);
            $data[0]['ReturnCode']='0';
            return $data[0];
        }
        else return 'Error';
    }//Index
}

