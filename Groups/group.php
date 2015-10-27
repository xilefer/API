<?php
namespace Groups;

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

    private function isPasswordProtected($GroupID){
        $PDO = $this->PDO;
        $query = "SELCET Accessibility FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        if($stmt->execute()){
            $data = $stmt->fetchColumn(0);
            if($data = "PASSWORD") return TRUE;
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

    public function newGroupProtected($Name,$OwnerID,$MaxMembers,$Password)
    {
        echo "Protected Group";
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
            $this->addMember($GroupID,$OwnerID);
            return 'Successful';
        }
        else return 'Error';
    }

    public function newGroup($Name,$OwnerID,$MaxMembers,$Accessibility)
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
        $stmt->bindParam(":Accessibility",$Accessibility,$PDO::PARAM_STR);

        if($stmt->execute())
        {
            //Owner als Mitglied setzen
            $this->addMember($GroupID,$OwnerID);
            return 'Successful';
        }
        else return 'Error';
    }//Index

    public function deleteGroup($GroupID,$UserID)
    {
        if($this->isGroupAdmin($UserID,$GroupID) == TRUE)
        {
            $PDO = $this->PDO;
            $query = "DELETE FROM `group` WHERE GroupID = :GroupID";
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
            if($stmt->execute()){
                $query = "DELETE FROM groupmember WHERE GroupID = :GroupID";
                $stmt = $PDO->prepare($query);
                $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
                if($stmt->execute()) return 'Successful';
                else return 'Error';
            }
            else return 'Error';
        }
        else return 'User is no Admin';

    }//Index

    #region Change-Methoden //Index

    public function setValue($GroupID,$Table,$Value,$UserID)//Index
    {
        if($this->isGroupAdmin($UserID,$GroupID)){
            $query = "UPDATE group SET :Param = :Value WHERE GroupID=:GroupID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":Param",$Table,$PDO::PARAM_STR);
            $stmt->bindParam(":Value",$Value,$PDO::PARAM_STR);
            $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
            if($stmt->execute()) return 'Successful';
            else return 'Error';
        }
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

    public function addMemberProtected($GroupID,$Password,$UserID)
    {
        $PDO = $this->PDO;
        $query = "SELECT GroupPassword FROM `group` WHERE GroupID = :GroupID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID);
        if($stmt->execute()){
            $GroupPassword = $stmt->fetchColumn(0);
            if($GroupPassword == $Password) {
                echo "Correct 'Password";
                $this->addMember($GroupID,$UserID);
            }
            else return 'Wrong Password';
        }
        else return 'Error';
    }

    public function addMember($GroupID,$UserID)
    {
        if($this->isPasswordProtected($GroupID)) return "Group is Protected, please use /Groups/Protected";
        $PDO = $this->PDO;
        $query = "INSERT INTO groupmember(`GroupID`,`UserID`) VALUES (:GroupID,:UserID)";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }//Index

    public function deleteMember($GroupID,$UserID) // Admin darf alle löschen!
    {
        $PDO = $this->PDO;
        $query = "DELETE FROM groupmember WHERE GroupID =:GroupID AND UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":GroupID",$GroupID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 'Successful';
        else return 'Error';
    }//Index

    //Gibt alle GroupID`s zurück in denen der User ist
    public function getGroupsForUser($UserID)
    {
        $PDO = $this->PDO;
        $query ="SELECT GroupID FROM groupmember WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()){
        $return = $stmt->fetchAll($PDO::FETCH_COLUMN,0);
            $return['ReturnCode'] = 0;
            return $return;
        }
        else return 'Error';
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
        else return '1';
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
            return 1;
        }
        //Als Teilnehmer aus der Gruppe löschen
        $query = "DELETE FROM `groupmember` WHERE UserID = :UserID";
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        if($stmt->execute()) return 0;
        else return 1;
    }
}