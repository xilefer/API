<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 15:58
 */
namespace enum\tables;


class main {

    public function isValidColumn($Table,$Column)
    {
        switch ($Table){
            case \enum\tables\tablenames::Event :
                switch ($Column){
                    case \enum\tables\event::Status:
                        return true;
                    case \enum\tables\event::Description:
                        return true;
                    case \enum\tables\event::Endtime:
                        return true;
                    case \enum\tables\event::EventID:
                        return true;
                    case \enum\tables\event::Location:
                        return true;
                    case \enum\tables\event::MaxParticipants:
                        return true;
                    case \enum\tables\event::MeetingPoint:
                        return true;
                    case \enum\tables\event::Name:
                        return true;
                    case \enum\tables\event::OwnerID:
                        return true;
                    case \enum\tables\event::Participants:
                        return true;
                    case \enum\tables\event::Starttime:
                        return true;
                    case \enum\tables\event::Transport:
                        return true;
                    default: return false;
                }
                break;
            case \enum\tables\tablenames::EventMembers :
                switch($Column){
                    case \enum\tables\user::ActiateToken :
                        return true;
                    case \enum\tables\user::Activated :
                        return true;
                    case \enum\tables\user::Email :
                        return true;
                    case \enum\tables\user::Firstname :
                        return true;
                    case \enum\tables\user::ID :
                        return true;
                    case \enum\tables\user::Lastname :
                        return true;
                    case \enum\tables\user::LoginTime :
                        return true;
                    case \enum\tables\user::LoginToken :
                        return true;
                    case \enum\tables\user::Password :
                        return true;
                    case \enum\tables\user::Username :
                        return true;
                    default: return false;

                }
                break;
            case \enum\tables\tablenames::Group :
                switch($Column){
                    case \enum\tables\group::Accessibility :
                        return true;
                    case \enum\tables\group::CreationDate :
                        return true;
                    case \enum\tables\group::GroupID :
                        return true;
                    case \enum\tables\group::GroupName :
                        return true;
                    case \enum\tables\group::MaxMembers :
                        return true;
                    case \enum\tables\group::ModificationDate :
                        return true;
                    case \enum\tables\group::Owner :
                        return true;
                    default: return false;
                }
                break;
            case \enum\tables\tablenames::GroupEvents :
                switch($Column){
                    case \enum\tables\groupevents::GroupID :
                        return true;
                    case \enum\tables\groupevents::EventID :
                        return true;
                    default: return false;
                }
                break;
            case \enum\tables\tablenames::GroupMembers :
                switch($Column){
                    case \enum\tables\groupmember::GroupID :
                        return true;
                    case \enum\tables\groupmember::UserID :
                        return true;
                    default: return false;
                }
                break;
            case \enum\tables\tablenames::Location :
                switch($Column){
                    case \enum\tables\location::Description :
                        return true;
                    case \enum\tables\location::LocationID :
                        return true;
                    case \enum\tables\location::Name :
                        return true;
                    case \enum\tables\location::OwnerID :
                        return true;
                    default: return false;
                }
                break;
            case \enum\tables\tablenames::User :
                switch($Column){
                    case \enum\tables\user::ActiateToken :
                        return true;
                    case \enum\tables\user::Activated :
                        return true;
                    case \enum\tables\user::Email :
                        return true;
                    case \enum\tables\user::Firstname :
                        return true;
                    case \enum\tables\user::ID :
                        return true;
                    case \enum\tables\user::Lastname :
                        return true;
                    case \enum\tables\user::LoginTime :
                        return true;
                    case \enum\tables\user::LoginToken :
                        return true;
                    case \enum\tables\user::Password :
                        return true;
                    case \enum\tables\user::Username :
                        return true;
                    default: return false;
                }
                break;
        }
    }
}