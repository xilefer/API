<?php
/**
 * Created by PhpStorm.
 * User: GeiselhartF
 * Date: 14.11.2015
 * Time: 13:20
 */

namespace Events;


use Users\User;

class comment
{

    private $PDO;

    public function __construct()
    {
        try{
            $this->PDO = new \PDO("mysql:host=localhost;dbname=applicationdb","root","");
        }
        catch(\PDOException $e){
            echo $e->getMessage();
            exit;
        }
    }

    private function generateCommentID()
    {
        $query = "SELECT `CommentID` FROM `eventcomment` WHERE CommentID = :CommentID";
        $PDO = $this->PDO;
        do{
            $ID = rand(1,99999999999);
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":CommentID",$ID,$PDO::PARAM_INT);
            $stmt->execute();
        }
        while($stmt->rowCount() != 0);
        return $ID;
    }

    /**
     * Erstellt einen neuen Kommentar in einem Event
     * Returncodes: 0; 50
     * @param $EventID
     * @param $Comment
     * @param $UserID
     * @return int
     */
    public function newComment($EventID,$Comment,$UserID)
    {
        $PDO = $this->PDO;
        $query = "INSERT INTO `eventcomment` (`CommentID`, `EventID`, `UserID`,`UserName`, `Text`) VALUES (:CommentID, :EventID, :UserID, :UserName, :Text)";
        $CommentID = $this->generateCommentID();
        $Comment = str_replace("%20",' ',$Comment);
        $stmt = $PDO->prepare($query);
        $Users = new \Users\User();
        $UserName = $Users->getNickname($UserID);
        $stmt->bindParam(":CommentID",$CommentID,$PDO::PARAM_INT);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserID",$UserID,$PDO::PARAM_INT);
        $stmt->bindParam(":UserName",$UserName,$PDO::PARAM_STR);
        $stmt->bindParam(":Text",$Comment,$PDO::PARAM_STR);
        if($stmt->execute()) return 0;
        else return 50;
    }

    /**
     * Gibt alle Kommentare eines Events zurück
     * Returncodes: 7; 51
     * @param $EventID
     * @return array|int
     */
    public function getCommentsForEvent($EventID)
    {
        $query ="SELECT UserName,UserID,Text,CreationDate FROM `eventcomment` WHERE EventID = :EventID";
        $PDO = $this->PDO;
        $stmt = $PDO->prepare($query);
        $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
        if($stmt->execute()) {
            if($stmt->rowCount() == 0) return 51;
            $Comments = $stmt->fetchAll($PDO::FETCH_ASSOC);
            $temp2 = array();
            foreach($Comments as $Comment){
                array_push($temp2,$Comment);
            }
            return array("Comments" => $temp2);
        }
        else return 7;
    }

    /**
     * Löscht alle Kommentare eines Events
     * Returncodes: 0; 20; 52
     * @param $EventID
     * @param $UserID
     * @return int
     */
    public function deleteCommentsForEvent($EventID, $UserID)
    {
        $Event = new \Events\Event();
        if($Event->isEventOwner($UserID,$EventID) == true){
            $query = "DELETE FROM `eventcomment` WHERE EventID = :EventID";
            $PDO = $this->PDO;
            $stmt = $PDO->prepare($query);
            $stmt->bindParam(":EventID",$EventID,$PDO::PARAM_INT);
            if($stmt->execute()) return 0;
            else return 52;
        }
        else return 20;
    }

}