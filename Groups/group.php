<?php
namespace Groups;

use Events\Event;

class group
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

    public function isGroupAdmin($UserID,$GroupID)
    {
        $query="SELECT Owner FROM `group` WHERE GroupID = :GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute())
        {
            $result=$stmt->fetchColumn();
            if($result == $UserID) return TRUE;
        }
        else return False;
    }

    private function countMembers($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT * FROM `groupmember` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->rowCount();
        else return -1;
    }

    private function isPasswordProtected($GroupID){
        $PDO = $this->PDO;
        $query = "SELECT Accessibility FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data = $stmt->fetchColumn(0);
            if($data == "PASSWORD") return TRUE;
            else return FALSE;
        }
        else return 'Error';
    }

    public function createGroupID()
    {
        $query = "SELECT GroupID FROM group WHERE GroupID=:GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        do{
            $rand = rand(0,99999999999);
            $stmt->bindParam(':GroupID',$rand,$PDO::PARAM_INT);
            $stmt->execute();
        }while($stmt->rowCount() != 0);
        return $rand;
    }

    /**
     * Erstellt eine neue passwortgeschützte Gruppe
     * Returncodes: 0,3,31,34
     * @param $Name
     * @param $OwnerID
     * @param $MaxMembers
     * @param $Password
     * @return int
     */
    public function newGroupProtected($Name,$OwnerID,$MaxMembers,$Password)
    {
        $GroupID = $this->createGroupID();
        $CreationTime = date('Y-n-d G:i:s');
        $PDO = $this->PDO;
        $query = "INSERT INTO `group` (`GroupID`,`GroupName`,`Owner`,`MaxMembers`,`CreationDate`,`ModificationDate`,`Accessibility`,`GroupPassword`) VALUES (:GroupID,:GroupName,:OwnerID,:MaxMembers,:CreationTime,:ModificationTime,'PASSWORD',:Password)";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(':GroupID', $GroupID, $PDO::PARAM_INT);
        $stmt->bindParam(":GroupName",$Name,$PDO::PARAM_STR);
        $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
        $stmt->bindParam(":MaxMembers",$MaxMembers,$PDO::PARAM_INT);
        $stmt->bindParam(":CreationTime",$CreationTime,$PDO::PARAM_STR);
        $stmt->bindParam(":ModificationTime",$CreationTime,$PDO::PARAM_STR);
        $stmt->bindParam(":Password",$Password,$PDO::PARAM_STR);
        if($stmt->execute())
        {
            //Owner als Mitglied setzen
            $AddMemberReturn = $this->addMemberProtected($GroupID,$Password,$OwnerID);
            if($AddMemberReturn == 0) return $GroupID;
            else if ($AddMemberReturn == 31) return 31;
            else if($AddMemberReturn == 34) return 34;
        }
        else return 3;
    }


    /**
     * Erstellt eine Neue Gruppe
     * Returncodes; 0,3,7,33
     * @param $Name
     * @param $OwnerID
     * @param $MaxMembers
     * @param $Accessibility
     * @return int|string
     */
    public function newGroup($Name,$OwnerID,$MaxMembers)
    {
        $GroupID = $this->createGroupID();
        $CreationTime = date('Y-n-d G:i:s');
        $PDO = $this->PDO;
        $query = "INSERT INTO `group` (`GroupID`,`GroupName`,`Owner`,`MaxMembers`,`CreationDate`,`ModificationDate`,`Accessibility`,`GroupPassword`)VALUES (:GroupID,:GroupName,:OwnerID,:MaxMembers,:CreationTime,:ModificationTime,:Accessibility,'')";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(':GroupID', $GroupID, $PDO::PARAM_INT);
        $stmt->bindParam(":GroupName",$Name,$PDO::PARAM_STR);
        $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
        $stmt->bindParam(":MaxMembers",$MaxMembers,$PDO::PARAM_INT);
        $stmt->bindParam(":CreationTime",$CreationTime,$PDO::PARAM_STR);
        $stmt->bindParam(":ModificationTime",$CreationTime,$PDO::PARAM_STR);
        $Accessibility = "OPEN";
        $stmt->bindParam(":Accessibility",$Accessibility,$PDO::PARAM_STR);

        if($stmt->execute())
        {
            //Owner als Mitglied setzen
            $AddMemberReturn = $this->addMember($GroupID,$OwnerID);
            if($AddMemberReturn == 0) return $GroupID;
            else if($AddMemberReturn == 34) return 34;
            else if($AddMemberReturn == 'Group is Protected, please use /Groups/Protected')
            {
                return 3;
            }
        }
        else return 33;
    }//Index

    /**
     * Löscht eine Gruppe und deren Teilnehmer
     * Returncodes: 0,30,36,37,39
     * @param $GroupID
     * @param $UserID
     * @return int|string
     */
    public function deleteGroup($GroupID,$UserID)
    {
        if($this->isGroupAdmin($UserID,$GroupID) == TRUE)
        {
            $PDO = $this->PDO;
            $query = "DELETE FROM `group` WHERE GroupID = :GroupID";
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
            if($stmt->execute()){
                $query = "DELETE FROM `groupmember` WHERE GroupID = :GroupID";
                $stmt = $PDO->prepare($query);
                $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
                if($stmt->execute()) {
                    //Anschließend GroupEvents löschen
                    $query = "DELETE FROM `groupevents` WHERE GroupID = :GroupID";
                    $stmt = $PDO->prepare($query);
                    $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
                    if($stmt->execute()){
                        return 0;
                    }
                    else return 39;
                }
                else return 37;
            }
            else return 36;
        }
        else return 30;

    }//Index

    #region Change-Methoden //Index

    /**
     * Setzt einen Wert in der Datenbank
     * Returncodes: 0,6,30
     * @param $GroupID
     * @param $Table
     * @param $Value
     * @param $UserID
     * @return int
     */
    public function setValue($GroupID,$Table,$Value,$UserID)//Index
    {
        if($this->isGroupAdmin($UserID,$GroupID)){
            $query = "UPDATE `group` SET $Table = :Value WHERE GroupID=:GroupID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            //$stmt->bindParam(":Param",$Table,$PDO::PARAM_STR);
            $stmt->bindParam(":Value",$Value,$PDO::PARAM_STR);
            $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
            if($stmt->execute()) return 0;
            else return 6;
        }
        else return 30;
    }

    public function changeName($GroupID,$Name)
    {
        $query = "UPDATE group SET Name=:Name WHERE GroupID = :GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":Name",$Name,$PDO::PARAM_STR);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function changeOwner($GroupID,$OwnerID)
    {
        $query = "UPDATE group SET Owner=:OwnerID WHERE GroupID = :GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":OwnerID",$OwnerID,$PDO::PARAM_INT);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function changeMaxMambers($GroupID,$MaxMembers)
    {
        $query = "UPDATE group SET MaxMembers=:MaxMembers WHERE GroupID = :GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":MaxMembers",$MaxMembers,$PDO::PARAM_INT);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    public function changeAccessibilty($GroupID,$Accessibility)
    {
        $query = "UPDATE group SET Accessibility=:Accessibility WHERE GroupID = :GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":Accessibility",$Accessibility,$PDO::PARAM_STR);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }

    #endregion

    /**
     * Fügt einer passwortgeschützten Gruppe einen Benutzer hinzu
     * Returncodes: 0,31,34
     * @param $GroupID
     * @param $Password
     * @param $UserID
     * @return int
     */
    public function addMemberProtected($GroupID,$Password,$UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT GroupPassword FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID);
        if($stmt->execute()){
            $GroupPassword = $stmt->fetchColumn(0);
            if($GroupPassword == $Password) {
                $query = "INSERT INTO `groupmember` (`GroupID`,`UserID`) VALUES (:GroupID,:UserID)";
                $stmt1 = $PDO->prepare($query);
                $stmt1->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
                $stmt1->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
                if($stmt1->execute()){
                    return 0;
                }
                else return 34;
            }
            else return 31;
        }
        else return 34;
    }

    /**
     * Fügt einer Group einen Benutzer hinzu
     * Returncodes: 0,34
     * @param $GroupID
     * @param $UserID
     * @return int|string
     */
    public function addMember($GroupID,$UserID)
    {
        if($this->isPasswordProtected($GroupID)) return 666;
        $PDO = $this->PDO;
        $query = "INSERT INTO groupmember(`GroupID`,`UserID`) VALUES (:GroupID,:UserID)";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 34;
    }//Index

    /**
     * Löscht Mitglied aus einer Gruppe
     * Returncodes: 0,36,37
     * @param $GroupID
     * @param $UserID
     * @return int
     */
    public function deleteMember($GroupID,$UserID) // Admin darf alle löschen!
    {
        //Wenn Onwer gelöscht wird dann neuen Owner setzten, wenn letzter Teilnehmer gelöscht wird, dann Gruppe löschen
        if($this->isGroupAdmin($UserID,$GroupID)){
            //Neuen Admin setzten
            if($this->countMembers($GroupID) < 2)
            {
                //Keine weiteren Teilnehmer ausser dem Owner -> Group Löschen
                $deleteReturn = $this->deleteGroup($GroupID,$UserID);
                if($deleteReturn == 0) return 0;
                else return 36;
            }
            else{
                $ReplaceReturn = $this->replaceAdminWithParticipant($GroupID,$UserID);
                if($ReplaceReturn != 0) return 1;
            }
        }
        if($this->countMembers($GroupID) < 2){
            //Nur noch einen Teilnehmer
            //Bedeutet das letzter Teilnehmer kein Admin der Gruppe ist -> Daten Inkonsistent
            $deleteReturn = $this->deleteGroup($GroupID,$UserID);
            if($deleteReturn == 0) return 0;
            else return 36;
        }
        $PDO = $this->PDO;
        $query = "DELETE FROM groupmember WHERE GroupID =:GroupID AND UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 37;
    }

    //Gibt alle GroupID`s zurück in denen der User ist
    public function getGroupsForUser($UserID)
    {
        $PDO = $this->PDO;
        $query ="SELECT GroupID FROM `groupmember` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $return = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            if(count($return) == 0) return 32;
            return $return;
        }
        else return 12;
    }//Index
    //Gibtt alle GroupID`s zurück die dem User gehören
    public function getGroupsWhereUserIsOwner($UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT GroupID FROM group WHERE OwnerID = :OwnerID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":OnwerID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll($PDO::FETCH_COLUMN,0);
        else return 'Error';
    }

    private function replaceAdminWithParticipant($GroupID,$DeletedUserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM groupmember WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            print_r($data);
            if(count($data) == 1)
            {
            $this->deleteGroup($GroupID,$DeletedUserID);
            return 0;
            }
            foreach($data as $temp){
                if($temp != $DeletedUserID){
                    $query = "UPDATE `group` SET Owner = :OwnerID WHERE GroupID = :GroupID";
                    $stmt = $PDO->prepare($query);
                    $stmt->bindParam(":OwnerID",$temp,$PDO::PARAM_INT);
                    $stmt->bindParam(":GroupID",$GroupID);
                    if($stmt->execute()) return 0;
                    else return 1;
                }
            }
            return 0;
        }
        else return 1;
    }

    public function deleteUserFromGroup($UserID)
    {
        //Gruppenowner
        $PDO = $this->PDO;
        $query = "SELECT GroupID FROM `group` WHERE Owner = :Owner";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":Owner",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            foreach($data as $temp)
            {
                $this->replaceAdminWithParticipant($temp,$UserID);
            }
            //return 1;
        }
        else return 1;
        //Als Teilnehmer aus der Gruppe löschen
        $query = "DELETE FROM `groupmember` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 1;
    }

    private function getEventsForGroup($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT EventID FROM `groupevents` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll($PDO::FETCH_COLUMN);
        else return 0;
    }

    public function getGroupKind($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT Accessibility FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll($PDO::FETCH_COLUMN)[0];
        else return 0;
    }

    /**
     * Gibt für einen Benutzer alle Events seiner Gruppen zurück für welche er nicht als Teilnehmer eingetragen ist.
     * Gibt ein JSON-Array zurück
     * @param $UserID
     * @return array
     */
    public function getEventsForUserWhereUserIsNotParticipating($UserID,$LastDate)
    {
        $return = array();
        $Groups = $this->getGroupsForUser($UserID);
        foreach($Groups as $GroupID)
        {
            $Events = new \Events\Event();
            $EventsForGroup = $this->getEventsForGroup($GroupID);
            foreach($EventsForGroup as $EventID)
            {
                $PDO = $this->PDO;
                $query = "SELECT `ModificationDate` FROM `event` WHERE EventID = :EventID";
                $stmt = $PDO->prepare($query);
                $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
                if($stmt->execute())
                {
                    if($this->isRelevant($LastDate,$stmt->fetchColumn(0)[0]))
                    {
                        if($Events->isParticipant($UserID,$EventID))
                        {
                            //Ist nicht relevant
                        }
                        else
                        {
                            $EventName = $Events->getEventName($EventID);
                            $EventParticipants = $Events->getNumberOfParticipants($EventID);
                            $GroupStatus = $this->getGroupKind($GroupID);
                            $GroupsForEvent = $Events->getGroupsForEvent($EventID);
                            $EventProperties = array("0" => $EventName, "1" => $EventParticipants, "2"=>$GroupStatus,"3"=>$GroupsForEvent);
                            $return = array_merge($return,$EventProperties);
                        }
                    }
                }
            }
        }
        return $return;
    }


    /**
     * Für User alle Events mit Teilnahme und Status erhalten (Name, Status(yes no maybe), Teilnehmerzahl, Status(Protected, Open) der Gruppe, Zugewiesene Gruppen)
     * @param $UserID
     * @param $LastDate
     */
    public function getEventsForUserWhereUserIsParticipating($UserID,$LastDate)
    {
        $PDO = $this->PDO;
        $query = "SELECT EventID FROM `eventmembers` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID", $UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $EventIDS = $stmt->fetchAll(($PDO::FETCH_COLUMN));
            $temp = array();
            foreach($EventIDS as $EventID)
            {
                $DateQuery = "SELECT `ModificationDate` FROM `event` WHERE EventID = :EventID";
                $stmtDate = $PDO->prepare($DateQuery);
                $stmtDate->bindParam(":EventID",$EventID);
                if($stmtDate->execute())
                {
                    $SelectedDate = $stmtDate->fetchAll($PDO::FETCH_COLUMN);
                    if($this->isRelevant($LastDate,$SelectedDate[0])) {
                        $Events = new \Events\Event();
                        $EventName = $Events->getEventName($EventID);
                        $ParticipantStatus = $Events->getParticipantStatus($EventID, $UserID);
                        $Pariticipants = $Events->getNumberOfParticipants($EventID);
                        $GroupsForEvent = $Events->getGroupsForEvent($EventID);
                        if ($GroupsForEvent != 22) {
                            $GroupStatus = array();
                            foreach ($GroupsForEvent as $GroupID) {
                                $GroupKind = $this->getGroupKind($GroupID);
                                $GroupKind = array("0" => $GroupKind);
                                $GroupStatus = array_merge($GroupStatus, $GroupKind);
                            }
                            $return =  array("0" => $EventName, "1" => $ParticipantStatus, "2" => $Pariticipants, "3" => $GroupStatus, "4" => $GroupsForEvent);
                            array_push($temp,$return);
                        }
                        else{
                            $GroupStatus = array("0" =>0);
                            $GroupsForEvent = array("0" => 0);
                            $return = array("0" => $EventName, "1" => $ParticipantStatus, "2" => $Pariticipants, "3" => $GroupStatus, "4" => $GroupsForEvent);
                            array_push($temp,$return);
                        }
                    }
                }
            }
            return $temp;
        }
        else return 2;
    }

    /**
     * Sucht nach GruppenNamen mit dem Übergebenen Fitler, gibt ein Assoziatives Array mit Name und ID der Gruppe zurück
     * @param $Filter
     * @return array
     */
    public function searchForGroup($Filter)
    {
        //Nach Gruppenname suchen mit Filter (Name, Teilnehmerzahl, Status (Protected, Open))
        $PDO = $this->PDO;
        $query = "SELECT GroupID FROM `group` WHERE `GroupName` LIKE :Filter";
        $stmt = $PDO->prepare($query);
        $temp = "%";
        $temp .= $Filter;
        $temp .='%';
        $stmt->bindParam(":Filter",$temp,$PDO::PARAM_STR);
        if($stmt->execute()){
            if($stmt->rowCount() == 0) return 301;
            $return = array();
            $GroupIDS = $stmt->fetchAll($PDO::FETCH_COLUMN);
            foreach($GroupIDS as $GroupID)
            {
                $GroupMembers = $this->countMembers($GroupID);
                $GroupName = $this->getGroupName($GroupID);
                $GroupKind = $this->getGroupKind($GroupID);
                $return = array_merge($return, array("0"=>$GroupName,"1"=>$GroupMembers,"3"=>$GroupKind));
            }
            return $return;
        }
        else{} //echo "Falsch";
    }
    private function getGroupName($GroupID)
    {
        $query = "SELECT GroupName FROM `group` WHERE GroupID =:GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID", $GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchColumn();
        else return 301;
    }

    private function countParticipants($GroupID)
    {
        $query = "SELECT * FROM `groupmember` WHERE GroupID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID", $GroupID, $PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->rowCount();
        else return -1;
    }

    /**
     * Prüft ob Lastdate kleiner als selectedDate ist
     * @param $LastDate
     * @param $SelectedTime
     * @return bool
     */
    private function isRelevant($LastDate,$SelectedDate)
    {
        $LastDate = strtotime($LastDate);
        $SelectedTime = strtotime($SelectedDate);
        if($LastDate < $SelectedTime)
        {
            return true;
        }
        else return false;
    }

    /***
     * Rückgabe: Name, Status, MaxTeilnehmer, Aktuelle Teilnehmer, TeilnehmerIDs
     */
    public function getGroupProperties($GroupID)
    {
        $Users = new \Users\User();
        $PDO = $this->PDO;
        $query = "SELECT GroupName , Accessibility ,MaxMembers FROM `group` WHERE `GroupID` = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()){
            if($stmt->rowCount() == 0) return 302;
            $returnarray = $stmt->fetchAll($PDO::FETCH_ASSOC);
            $Name = $returnarray[0]["GroupName"];
            $Status = $returnarray[0]["Accessibility"];
            $MaxMembers = $returnarray[0]["MaxMembers"];
            //Jetzt noch die Nutzer zählen und in einem Array ausgeben
            $query = "SELECT UserID FROM `groupmember` WHERE GroupID = :GroupID";
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
            if($stmt->execute()){
                $AktTeilnehmer = $stmt->rowCount();
                if($AktTeilnehmer == 0){
                    //Keine Teilnehmer gefunden
                }
                $UserIDS = $stmt->fetchall($PDO::FETCH_COLUMN);
                $UserNames = array();
                foreach($UserIDS as $UserID)
                {
                    //Hier ist noch ein Fehler drin
                    $UserName = $Users->getUser($UserID);
                    array_push($UserNames,$UserName);
                }
            }
            else{
                return 303;
            }
        }
        else{
            return 302;
        }
        $return = array("GroupName" => $Name, "Status" => $Status,"AktuelleTeilnehmer" => $AktTeilnehmer, "MaximaleTeilnehmer" => $MaxMembers, "Teilnehmer" => $UserNames);
        return $return;
    }
}