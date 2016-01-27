<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.10.2015
 * Time: 15:04
 */

namespace Events;

use enum\returncodes;

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

    public function getNumberOfParticipants($EventID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM `eventmembers` WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->rowCount();
        else return 7;
    }

    public function isEventOwner($UserID,$EventID)
    {
        $query = "SELECT Owner FROM event WHERE EventID =:EventID";
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

    /**
     * Erstellt ein neues Event
     * Returncodes: 0,7,25,201,202
     * @param $Name
     * @param $Starttime
     * @param $Endtime
     * @param $MeetingPoint
     * @param $Description
     * @param $OwnerID
     * @param $Status
     * @param $MaxParticipants
     * @param $Transport
     * @return int
     */
    public function newEvent($Name, $Starttime, $Endtime, $MeetingPoint, $Description, $OwnerID, $Status, $MaxParticipants, $Transport)
    {
        //Ermitteln der EventID
        $PDO = $this->PDO;
        $EventID = $this->createEventID();
        $query = "INSERT INTO `event`(`EventID`,`Name`,`Location`,`Starttime`,`Endtime`,`Participants`,`MeetingPoint`,`Description`,`Owner`,`Status`,`MaxParticipants`,`Transport`) VALUES(:EventID,:EventName,:LocationID,:Starttime,:Endtime,'Andere Tabelle',:MeetingPoint,:Description,:OwnerID,:Status,:MaxParticipants,:Transport) ";
        $stmt = $PDO->prepare($query);
        $LocationID = 0;
        $Endtime = str_replace('%20', ' ',$Endtime);
        $Starttime =str_replace('%20',' ',$Starttime);
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
            $ReturnParticipant = $this->addParticipant($OwnerID,$EventID,'YES');
            //Owner als Teilnehmer eintragen
            if($ReturnParticipant == 0) return $EventID;
            else if($ReturnParticipant == 7) return 7;
            else if($ReturnParticipant == 25) return 25;
            else if($ReturnParticipant == 201) return 201;
            else if($ReturnParticipant == 202) return 202;
        }
        else
        {
            return 7;
        }
    }//Index

    private function computeMaxParticipants($EventID,$MaxParticipants)
    {
        $AktPart = $this->getNumberOfParticipants($EventID);
        if($AktPart > $MaxParticipants) return $AktPart;
        else if($AktPart <= $MaxParticipants) return $MaxParticipants;
    }


    /**
     * Ändert Werte an einem Event ab
     * Returncodes: 0; 2; 20
     * @param $UserID
     * @param $EventID
     * @param $Starttime
     * @param $EndTime
     * @param $MeetingPoint
     * @param $Description
     * @param $MaxParticipants
     * @param $Transport
     * @return int
     */
    public function changeEvent($UserID,$EventID,$Starttime, $EndTime, $MeetingPoint, $Description, $MaxParticipants, $Transport)
    {
        if($this->isEventOwner($UserID,$EventID)){
            $PDO = $this->PDO;
            $MaxParticipants = $this->computeMaxParticipants($EventID,$MaxParticipants);
            $Description = str_replace("%20"," ",$Description);
            $MeetingPoint = str_replace("%20"," ",$MeetingPoint);
            $Starttime = str_replace("%20"," ",$Starttime);
            $EndTime = str_replace("%20"," ",$EndTime);
            $query = "UPDATE event SET Starttime = :Starttime, Endtime = :Endtime, MeetingPoint = :MeetingPoint, Description = :Description, MaxParticipants = :MaxParticipants, Transport = :Transport WHERE $EventID = :EventID";
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":Starttime",$Starttime);
            $stmt->bindParam(":Endtime",$EndTime);
            $stmt->bindParam(":MeetingPoint",$MeetingPoint);
            $stmt->bindParam(":Description",$Description);
            $stmt->bindParam(":MaxParticipants",$MaxParticipants);
            $stmt->bindParam(":Transport",$Transport);
            $stmt->bindParam(":EventID",$EventID);
            if($stmt->execute()){
                return 0;
            }else{
                return 2;
            }
        }
        return 20;
    }


    //Methode darf nur vom GroupOwner ausgef�hrt werden
    /**
     * L�schte ein Event mit allen Teilnehmern
     * Returncodes: 0,20,27,28,52
     * @param $EventID
     * @param $OwnerID
     * @return int
     */
    public function deleteEvent($EventID, $OwnerID)
    {
        if($this->isEventOwner($OwnerID,$EventID) == true)
        {
            $query = "DELETE  FROM event WHERE EventID = :EventID AND Owner = :OwnerID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
            $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
            $Comments = new \Events\comment();
            if($stmt->execute()){
                $query = "DELETE FROM eventmembers WHERE EventID = :EventID";
                $stmt = $this->PDO->prepare($query);
                $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
                if($stmt->execute()){
                    $temp = $Comments->deleteCommentsForEvent($EventID,$OwnerID);
                    if($temp == 0){
                        return 0;
                    }else if($temp == 52){
                        return 52;
                    }else if($temp == 20){
                        return 20;
                    }
                }
                else return 28;
            }
            else{

                return 27;
            }
        }
        else {
            return 20;
        }
    }//Index

    #region Change-Methoden

    /**
     * Setzt einen Wert in der Datenbank
     * Returncodes: 0,6,30
     * @param $EventID
     * @param $Param
     * @param $Value
     * @param $UserID
     * @return int
     */
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
            if($stmt->execute()) return 0;
            else return 6;
        }
        else return 20;
    }//Index

    /**
     * Setzt den Status bei Eventteilnahme
     * Returncodes: 0,6
     * @param $EventID
     * @param $Status
     * @param $UserID
     * @return int
     */
    public function setPariticipantStatus($EventID,$Status,$UserID){
        $query = "UPDATE `eventmembers` SET Status = :Status WHERE UserID =:UserID AND EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":Status",$Status,$PDO::PARAM_STR);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute())return 0;
        else return 6;
    }
#endregion


    /**
     * F�gt dem Event einen Teilnehmer hinzu
     * Returncodes: 0,7,25,201,202
     * @param $UserID
     * @param $EventID
     * @param $Status
     * @return int
     */
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
                if($rows2 == $Value1) return 201;

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
                        if($stmt4->execute()) {
                            return 0;
                        }else{
                            return 25;
                        }
                    }
                    else
                    {
                        return 202;
                    }
                }
                else return 7;

            }
            else return 7;
        }
        else return 7;
    }//Index

    /**
     * L�scht einen Teilnehmer aus einem Event
     * Returncodes: 0,28
     * @param $EventID
     * @param $UserID
     * @return int|string
     */
    public function deleteParticipant( $EventID, $UserID) // Admin darf jeden l�schen
    {
        if($this->getNumberOfParticipants($EventID) == 1) return $this->deleteEvent($EventID,$UserID);
        $query = "DELETE FROM eventmembers WHERE EventID = :EventID AND UserID = :UserID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 28;
    }//Index

    /**
     * F�gt dem Event eine Gruppe hinzu
     * Returncodes: 0,26,203
     * @param $EventID
     * @param $GroupID
     * @return string
     */
    public function addGroup($EventID,$GroupID)
    {
        $PDO = $this->PDO;
        $query = "INSERT into groupevents (`GroupID`,`EventID`) VALUES (:GroupID,:EventID)";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) {
            if($stmt->rowCount() == 0) return 203;
            return 0;
        }
        else return 26;
    }//Index

    /**
     * Entfernt eine Gruppe aus einem Event
     * Returncodes: 0,29
     * @param $EventID
     * @param $GroupID
     * @return string
     */
    public function removeGroup($EventID,$GroupID)
    {
        $PDO = $this->PDO;
        $query = "DELETE FROM groupevents WHERE EventID=:EventID AND GroupID=:GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 29;
    }

    /**Gibt alle zu einer Gruppe gehörigen Events aus
     * Returncodes 7;22;
     * @param $EventID
     * @return array|int
     */
    public function getGroupsForEvent($EventID)
    {
        $PDO = $this->PDO;
        $query ="SELECT GroupID FROM groupevents WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) {
            $GroupIDS = $stmt->fetchAll($PDO::FETCH_COLUMN);
            if(count($GroupIDS) == 0) return 22;
            $temp2 = array();
            $temp = array();
            foreach($GroupIDS as $GroupID) {
                $temp['GroupID'] = $GroupID;
                array_push($temp2, $temp);
            }
            return $temp2;
        }
        else return 7;
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

    /**
     * Gibt alle Teilnhemer mit Details(bspw. Nutzername) für ein Event aus
     * Returncodes: 7; 23
     * @param $EventID
     * @return array|int
     */
    public function getEventMembersWithInformation($EventID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM `eventmembers` WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 0) {
                return 23;
            }else{
                $UserIDs = $stmt->fetchAll($PDO::FETCH_COLUMN);
                $Users = new \Users\User();
                $temp2= array();
                foreach($UserIDs as $UserID){
                    $Nickname = $Users->getNickname($UserID);
                    $ParticipationState = $this->getParticipantStatus($EventID,$UserID);
                    $temp1 = array('UserID' => $UserID, 'Nickname' => $Nickname, 'ParticipationState' => $ParticipationState);
                    array_push($temp2,$temp1);
                }
                return array("Users" =>$temp2);
            }
        }
        else return 7;
    }

    public function getEventMember($EventID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM `eventmembers` WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 0) {
                return 23;
            }else{
                return array("Users" =>$stmt->fetchAll($PDO::FETCH_ASSOC));
            }
        }
        else return 7;
    }

    public function isParticipant($UserID,$EventID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM eventmembers WHERE EventID = :EventID AND UserID = :UserID";
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
        $query = "SELECT EventID,Owner AS OwnerID,Name,Location,Starttime,Endtime,MeetingPoint,Description,Status,MaxParticipants,Transport FROM event WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 0) return 21;

            $data=$stmt->fetchAll($PDO::FETCH_ASSOC);
            $data = $data[0];
            $EventMembers = $this->getEventMembersWithInformation($EventID); //7
            if($EventMembers == 7) return 7;
            else if($EventMembers == 23){
                $temp = array();
            }else{
                $temp = $EventMembers['Users'];
            }
            $data['Participants'] = $temp;
            return $data;
        }
        else return 2;
    }//Index

    public function getEventName($EventID)
    {
        //(Name, Teilnehmerzahl, Status(Protected, Open) der Gruppe, Zugewiesene Gruppen)
        $PDO = $this->PDO;
        $query = "SELECT Name FROM `event` WHERE EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()){
            return $stmt->fetchAll($PDO::FETCH_COLUMN)[0];
        }
        else return 2;
    }

    public function getParticipantStatus($EventID,$UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT Status FROM `eventmembers` WHERE UserID = :UserID AND EventID = :EventID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID", $UserID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute())
        {
            return $stmt->fetchAll($PDO::FETCH_COLUMN)[0];
        }
        else return 2;
    }


}

