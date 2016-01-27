<?php
namespace Groups;

use enum\returncodes;
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



    public function reachedMaxMembers($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT `MaxMembers` FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID);
        if($stmt->execute()){
            if($stmt->fetchColumn() == $this->countMembers($GroupID)) return true;
            return false;
        }
    }


    /**
     * Pr�ft ob die neue maximale Teilnehmerzahl gesetzt werden kann
     * Returncodes: 'Error'
     * @param $GroupID
     * @param $MaxMembers
     * @return int|string
     */
    private function computeMaxMembers($GroupID, $MaxMembers)
    {
        $Members = $this->countMembers($GroupID);
        if($Members == -1) return 'Error';
        else if($Members > $MaxMembers) return $Members;
        else return $MaxMembers;
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
        else return 7;
    }

    /**
     * �ndert Parameter an Gruppen
     * Returncodes 0; 7; 35
     * @param $UserID
     * @param $GroupID
     * @param $Name
     * @param $MaxMembers
     * @return int
     */
    public function changeGroup($UserID,$GroupID,$Name,$MaxMembers)
    {
        $Temp = $this->isPasswordProtected($GroupID);
        if($Temp == true) return 35;
        if($Temp == 7) return 7;
        $MaxMembers = $this->computeMaxMembers($GroupID,$MaxMembers);
        $Name = str_replace('%20',' ',$Name);
        if($this->isGroupAdmin($UserID,$GroupID)){
            $PDO = $this->PDO;
            $query = "UPDATE `group` SET GroupName = :Name, MaxMembers = :MaxMembers WHERE GroupID = :GroupID";
            $stmt = $PDO->prepare($query);
            //echo $Name;
            //echo $MaxMembers;
            echo $GroupID;
            $stmt->bindParam(":Name", $Name);
            $stmt->bindParam("MaxMembers",$MaxMembers);
            $stmt->bindParam("GroupID",$GroupID);
            if($stmt->execute()) {
                return 0;
            }else{
                return 7;
            }
        }else{
            $PDO = $this->PDO;
            $query = "UPDATE `group` SET GroupName = :Name WHERE GroupID = :GroupID";
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":Name",$Name);
            $stmt->bindParam(":GroupID",$GroupID);
            if($stmt->execute()){
                return 0;
            }else{
                return 7;
            }
        }

    }

    /**
     * �ndert Parameter an Passwortgesch�tzter Gruppe
     * Returncodes 0; 3; 7,31,35
     * @param $UserID
     * @param $GroupID
     * @param $Name
     * @param $MaxMembers
     * @param $Password
     * @return int
     */
    public function changeGroupProtected($UserID,$GroupID,$Name,$MaxMembers,$Password,$NewPassword)
    {
        $MaxMembers = $this->computeMaxMembers($GroupID, $MaxMembers);
        $PDO = $this->PDO;
        $query = "SELECT GroupPassword FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID);
        if($stmt->execute()){
            $PW = $stmt->fetchColumn(0);
            if($PW == $Password){
                if($this->isGroupAdmin($UserID,$GroupID)){
                    $query = "UPDATE `group` SET GroupName = :Name, MaxMembers = :MaxMembers, GroupPassword = :GroupPassword WHERE GroupID = :GroupID";
                    $stmt = $PDO->prepare($query);
                    $stmt->bindParam(":Name",$Name);
                    $stmt->bindParam(":MaxMembers",$MaxMembers);
                    $stmt->bindParam(":GroupPassword",$NewPassword);
                    $stmt->bindParam(":GroupID",$GroupID);
                    if($stmt->execute()){
                        return 0;
                    }else{
                        return 3;
                    }
                }
                else
                {
                    $query = "UPDATE `group` SET GroupName = :Name WHERE GroupID = :GroupID";
                    $stmt = $PDO->prepare($query);
                    $stmt->bindParam(":Name",$Name);
                    $stmt->bindParam(":GroupID",$GroupID);
                    if($stmt->execute()) return 0;
                        else{
                            return 3;
                        }
                }
            }
            else
            {
                return 31;
            }
        }
        else{
            return 7;
        }


        $Temp = $this->isPasswordProtected($GroupID);
        if($Temp == 7) return 7;
        if($Temp == false) {
            //Normales ChangeGroup Aufrufen
            return $this->changeGroup($UserID,$GroupID,$Name,$MaxMembers);
        }
        else{
            if($this->isGroupAdmin($UserID,$GroupID)){
                $PDO = $this->PDO;
                $query = "UPDATE `group`SET Name = :Name AND MaxMembers = :MaxMembers WHERE GroupID = :GroupID";
                return 0;
            }else{

                return 0;
            }
        }

    }

    /**
     * �ndert die Accessibility einer Gruppe
     * Returncodes: 0; 3; 7; 31
     * @param $UserID
     * @param $GroupID
     * @param $Password
     * @return int
     */
    public function changeAccessibility($UserID,$GroupID,$Password)
    {
        $query = "SELECT Accessibility FROM `group` WHERE GroupID = :GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID);
        if($stmt->execute()){
            $Accessibility = $stmt->fetchColumn(0);

            if($Accessibility == "PASSWORD"){
                $PDO = $this->PDO;
                $query = "SELECT GroupPassword FROM `group` WHERE GroupID = :GroupID";
                $stmt = $PDO->prepare($query);
                $stmt->bindParam(":GroupID",$GroupID);
                if($stmt->execute()){
                    if($stmt->rowCount() == 0)return 302;
                    if($stmt->fetchColumn(0) == $Password){
                        $query = "UPDATE `group` SET Accessibility = :Accessibility, GroupPassword = :GPW WHERE GroupID = :GroupID";
                        $stmt = $PDO->prepare($query);
                        $Acces = "OPEN";
                        $stmt->bindParam(":Accessibility",$Acces);
                        $Acces ="";
                        $stmt->bindParam(":GPW",$Acces);
                        $stmt->bindParam(":GroupID",$GroupID);
                        if($stmt->execute()){
                            return 0;
                        }else{
                            return 3;
                        }
                    }else return 31;
                }
                return 7;


            }else if($Accessibility == "OPEN"){
                $query = "UPDATE `group` SET Accessibility = :AC, GroupPassword = :GPW WHERE GroupID = :GroupID";
                $stmt = $PDO->prepare($query);
                $Acces = "PASSWORD";
                $stmt->bindParam(":AC",$Acces);
                $stmt->bindParam(":GPW",$Password);
                $stmt->bindParam(":GroupID",$GroupID);
                if($stmt->execute()){
                    return 0;
                }else{
                    return 3;
                }
            }else{

                return 3;
            }
        }else{
            return 7;
        }



    }


    public function createGroupID()
    {
        $query = "SELECT GroupID FROM `group` WHERE GroupID=:GroupID";
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
     * Erstellt eine neue passwortgesch�tzte Gruppe
     * Returncodes: 3; 31; 34; 304
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
            $AddMemberReturn = $this->addMemberProtected($GroupID,$Password,$OwnerID);
            if($AddMemberReturn == 0) return $GroupID;
            else if ($AddMemberReturn == 31) return 31;
            else if($AddMemberReturn == 34) return 34;
            else if($AddMemberReturn == 304) return 304;
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
    }

    /**
     * L�scht eine Gruppe und deren Teilnehmer
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
                    //Anschlie�end GroupEvents l�schen
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
    }

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



    #region AddMember-Methoden
    /**
     * F�gt einer passwortgesch�tzten Gruppe einen Benutzer hinzu
     * Returncodes: 0; 31; 34; 304
     * @param $GroupID
     * @param $Password
     * @param $UserID
     * @return int
     */
    public function addMemberProtected($GroupID,$Password,$UserID)
    {
        if($this->reachedMaxMembers($GroupID)) return 304;
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
     * F�gt einer Gruppe einen Benutzer hinzu
     * Returncodes: 0,34; 304; 666
     * @param $GroupID
     * @param $UserID
     * @return int|string
     */
    public function addMember($GroupID,$UserID)
    {
        if($this->isPasswordProtected($GroupID)) return 666;
        if($this->reachedMaxMembers($GroupID)) return 304;
        $PDO = $this->PDO;
        $query = "INSERT INTO groupmember(`GroupID`,`UserID`) VALUES (:GroupID,:UserID)";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 34;
    }//Index

    #endregion

    /**
     * L�scht Mitglied aus einer Gruppe
     * Returncodes: 0; 1; 36; 37
     * @param $GroupID
     * @param $UserID
     * @return int
     */
    public function deleteMember($GroupID,$UserID)
    {
        //Wenn Onwer gel�scht wird dann neuen Owner setzten, wenn letzter Teilnehmer gel�scht wird, dann Gruppe l�schen
        if($this->isGroupAdmin($UserID,$GroupID)){
            //Neuen Admin setzten
            if($this->countMembers($GroupID) < 2)
            {
                //Keine weiteren Teilnehmer ausser dem Owner -> Group L�schen
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

    /**
     * Gibt alle Gruppen mit Informationen einse Benutzers zur�ck, in denen er ist.
     * Returncodes: 12; 32
     * @param $UserID
     * @return array|int
     */
    public function getGroupsForUserWithInformation($UserID){
        $GroupIDs = $this->getGroupsForUser($UserID);
        if($GroupIDs == 32){
            return 32;
        }
        else if($GroupIDs == 12){
            return 12;
        }
        $ResultArray = array();
        foreach($GroupIDs['Groups'] as $GroupID){
            $GroupID = $GroupID["GroupID"];
            $GroupProperties = $this->getGroupProperties($GroupID);
            if($GroupProperties != 302 && $GroupProperties != 303){
                $GroupProperties["GroupID"] = $GroupID;
                array_push($ResultArray,$GroupProperties);
            }
        }
        return array("Groups" => $ResultArray);
    }

    /**
     * Gibt Alle ID`s der Gruppen f�r einen Benutzer zur�ck, in denen er ist.
     * @param $UserID
     * @return array|int
     */
    public function getGroupsForUser($UserID)
    {
        $PDO = $this->PDO;
        $query ="SELECT GroupID FROM `groupmember` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $return = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            if(count($return) == 0) return 32;
            $temp = array();
            foreach($return as $GroupID){
                array_push($temp,array("GroupID" =>$GroupID));
            }
            return array("Groups" =>$temp);
        }
        else return 12;
    }


    //Gibt alle GroupID`s zur�ck die dem User geh�ren


    public function getGroupsWhereUserIsOwner($UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT GroupID FROM group WHERE Owner = :OwnerID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":OnwerID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchAll($PDO::FETCH_COLUMN,0);
        else return 'Error';
    }

    /**
     * Ersetzt den Admin durch einen anderen Teilnehmer
     * Returncodes: 0; 1
     * @param $GroupID
     * @param $DeletedUserID
     * @return int
     */
    private function replaceAdminWithParticipant($GroupID,$DeletedUserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT UserID FROM groupmember WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
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

    /*
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
        //Als Teilnehmer aus der Gruppe l�schen
        $query = "DELETE FROM `groupmember` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 1;
    } */


    private function getEventsForGroup($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT `EventID` FROM `groupevents` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) {
            return $stmt->fetchAll($PDO::FETCH_COLUMN);
        }
        else return 22;
    }

    /**
     * Gibt die Art der Gruppe (Offen, Passwortgesch�tzt) zur�ck.
     * @param $GroupID
     * @return int
     */
    public function getGroupKind($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT `Accessibility` FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $test = $stmt->fetchAll($PDO::FETCH_COLUMN);
            return $test[0];
        }
        else return 0;
    }

    /**
     * Gibt f�r einen Benutzer alle Events seiner Gruppen zur�ck f�r welche er nicht als Teilnehmer eingetragen ist.
     * @param $UserID
     * @param $LastDate
     * @return array
     */
    public function getEventsForUserWhereUserIsNotParticipating($UserID,$LastDate)
    {
        $return = array();
        $Groups = $this->getGroupsForUser($UserID);
        if($Groups == 12)   return 12;
        else if($Groups == 32)   return 32;
        else {
            foreach ($Groups['Groups'] as $GroupID) {
                $GroupID = $GroupID['GroupID'];
                $Events = new \Events\Event();
                $EventsForGroup = $this->getEventsForGroup($GroupID);
                if($EventsForGroup == 22){
                    return 22;
                }   else{
                foreach ($EventsForGroup as $EventID) {
                    $PDO = $this->PDO;
                    $query = "SELECT `ModificationDate` FROM `event` WHERE EventID = :EventID";
                    $stmt = $PDO->prepare($query);
                    $stmt->bindParam(":EventID", $EventID, $PDO::PARAM_INT);
                    //$ParticipantState = $Events->getParticipantStatus($EventID,$UserID);
                    if ($stmt->execute()) {
                        if ($this->isRelevant($LastDate, $stmt->fetchColumn(0))) {
                            if ($Events->isParticipant($UserID, $EventID)) {
                                //Ist nicht relevant
                            } else {
                                $EventName = $Events->getEventName($EventID);
                                $EventParticipants = $Events->getNumberOfParticipants($EventID);
                                $GroupStatus = $this->getGroupKind($GroupID);
                                $GroupsForEvent = $Events->getGroupsForEvent($EventID); // 22 7
                                if($GroupsForEvent == 22) return 22;
                                else if($GroupsForEvent == 7) return 7;
                                else{
                                    $GroupsWithName = array();
                                    foreach($GroupsForEvent[0] as $ForEventGroupID) {
                                        $GroupName = $this->getGroupName($ForEventGroupID);
                                        $temp = array("GroupID" => $ForEventGroupID, "GroupName" => $GroupName);
                                        array_push($GroupsWithName, $temp);
                                    }
                                    //$EventProperties = array("EventID" => $EventID,"EventName" => $EventName,"Participants" => $EventParticipants, "GroupStatus"=>$GroupStatus,"GroupsForEvent"=>$GroupsWithName);
                                    $EventProperties = array("EventID" => $EventID, "EventName" => $EventName, "Participants" => $EventParticipants, "GroupStatus" => $GroupStatus, "Groups" => $GroupsWithName);

                                    array_push($return, $EventProperties);
                                }
                            }
                        }
                    }
                }
                }
            }
            return array("Events" => $return);
        }
    }


    /**
     * F�r User alle Events mit Teilnahme und Status erhalten (Name, Status(yes no maybe), Teilnehmerzahl, Status(Protected, Open) der Gruppe, Zugewiesene Gruppen)
     * Returncodes: 204; 2;
     * @param $UserID
     * @param $LastDate
     * @return array|int
     */
    public function getEventsForUserWhereUserIsParticipating($UserID,$LastDate)
    {
        $PDO = $this->PDO;
        $query = "SELECT EventID FROM `eventmembers` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID", $UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $EventIDS = $stmt->fetchAll(($PDO::FETCH_COLUMN));
            if(count($EventIDS) == 0) return 204;
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
                        if($GroupsForEvent == 22){
                            $return = array("EventID" => $EventID, "EventName" => $EventName, "Participation" => $ParticipantStatus, "Participants" => $Pariticipants, "GroupStatus" => array(), "Groups" => array());
                            array_push($temp,$return);
                        }
                        else if($GroupsForEvent == 7){
                            $return = array("EventID" => $EventID, "EventName" => $EventName, "Participation" => $ParticipantStatus, "Participants" => $Pariticipants, "GroupStatus" => array(), "Groups" => array());
                            array_push($temp,$return);
                        }
                        else{
                            $GroupStatus = array();
                            $temp1 = array();
                            foreach ($GroupsForEvent as $GroupID) {
                                $GroupID =  $GroupID['GroupID'];
                                $GroupName = $this->getGroupName($GroupID);
                                $GroupKind = $this->getGroupKind($GroupID);
                                $GroupKind = array("Status" => $GroupKind);
                                $tempGroupsEvent = array("GroupID" => $GroupID,"GroupName" => $GroupName);
                                array_push($temp1,$tempGroupsEvent);
                                array_push($GroupStatus, $GroupKind);
                            }
                            $return =  array("EventID" => $EventID, "EventName" => $EventName, "Participation" => $ParticipantStatus, "Participants" => $Pariticipants, "GroupStatus" => $GroupStatus, "Groups" => $temp1);
                            array_push($temp,$return);
                        }
                    }
                }
            }
            return array("Events" =>$temp);
        }
        else return 2;
    }


    private function getOwnerID($GroupID)
    {
        $PDO = $this->PDO;
        $query = "SELECT Owner FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID);
        if($stmt->execute()){
            return $stmt->fetchColumn(0);
        }
        return 0;
    }

    /**
     * Sucht nach GruppenNamen mit dem �bergebenen Fitler, gibt ein Assoziatives Array mit Name und ID der Gruppe zur�ck
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
                $OwnerID = $this->getOwnerID($GroupID);
                $temp = array("GroupID" => $GroupID, "GroupName"=>$GroupName,"OwnerID"=>$OwnerID,"CurrentMembers"=>$GroupMembers,"Status"=>$GroupKind);
                array_push($return,$temp);
            }
            $return = array("Groups" => $return);
            return $return;
        }
        else{
            return 301;
        } //echo "Falsch";
    }

    /**
     * Gibt den Namen der Gruppe zur�ck
     * @param $GroupID
     * @return int|string
     */
    private function getGroupName($GroupID)
    {
        $query = "SELECT GroupName FROM `group` WHERE GroupID =:GroupID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID", $GroupID,$PDO::PARAM_INT);
        if($stmt->execute()) return $stmt->fetchColumn();
        else return 301;
    }

    /**
     * Gibt die aktuelle Zahl der Mitglieder einer Gruppe zur�ck.
     * @param $GroupID
     * @return int
     */
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
     * Pr�ft ob Lastdate kleiner als selectedDate ist
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

    /**
     * Gibt Details �ber eine Gruppe zur�ck.
     * Returncodes: 302; 303
     * @param $GroupID
     * @return array|int
     */
    public function getGroupProperties($GroupID)
    {
        $Users = new \Users\User();
        $PDO = $this->PDO;
        $query = "SELECT GroupName , Accessibility ,MaxMembers FROM `group` WHERE `GroupID` = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        //$stmt->bindParam(":LastDate",$LastTimestamp,$PDO::PARAM_STR);
        if($stmt->execute()){
            if($stmt->rowCount() == 0) return 302;
            $returnarray = $stmt->fetchAll($PDO::FETCH_ASSOC);
            $Name = $returnarray[0]["GroupName"];
            $Status = $returnarray[0]["Accessibility"];
            $MaxMembers = $returnarray[0]["MaxMembers"];
            $OwnerID = $this->getOwnerID($GroupID);
            //Jetzt noch die Nutzer z�hlen und in einem Array ausgeben
            $query = "SELECT UserID FROM `groupmember` WHERE GroupID = :GroupID";
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
            if($stmt->execute()){
                $AktTeilnehmer = $stmt->rowCount();
                if($AktTeilnehmer == 0){
                   $UserNames = array();
                }else{
                    $UserIDS = $stmt->fetchall($PDO::FETCH_COLUMN);
                    $UserNames = array();
                    foreach($UserIDS as $UserID)
                    {
                        $UserName = $Users->getNickname($UserID);
                        if($UserName != 102 && $UserNames != 7){
                            $UserName = array("UserID" => $UserID,"UserName" => $UserName);
                            array_push($UserNames,$UserName);
                        }

                    }
                }

            }
            else{
                return 303;
            }
        }
        else{
            return 302;
        }
        $return = array("GroupName" => $Name, "Status" => $Status,"CurrentMembers" => $AktTeilnehmer, "OwnerID"=>$OwnerID, "MaximalMembers" => $MaxMembers, "Members" => $UserNames);
        return $return;
    }
}