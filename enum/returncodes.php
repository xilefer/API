<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.10.2015
 * Time: 14:56
 */

namespace enum;

class returncodes extends Enum{

    //General Codes
    const Success = 0;
    const General_UserError = 1;
    const General_EventError = 2;
    const General_GroupError = 3;
    const General_LocationError = 4;
    const General_WrongNumberOfParameter = 5;
    const General_CantSetValue = 6;
    const General_QueryError = 7;
    const General_WrongDateFormat = 8;

    //UserErrorCodes;
    const Error_Emailnotsent = 10;
    const Error_Emailalreadyexits = 11;
    const Error_UserDoesnotexist = 12;
    const Error_WrongUsernameorPassword = 13;
    const Error_AuthenticationRequired = 14;
    const Error_WrongNumberofParameters = 15;
    const Error_InvalidTablename = 16;
    const Error_Propertycouldnotbeset = 17;
    const Error_Usercouldnotbedeleted = 18;
    const Error_CannotsetValuesofotherUsers= 19;
    const Error_WrongUsernameorLoginToken = 100;
    const Error_BadPermission = 101;
    const Error_UserNotFound = 102;


    //EventErrorCodes:
    const Error_UserNotEventOwner = 20;
    const Error_NoEventWithSuchID = 21;
    const Error_NoGroupsForThisEvent = 22;
    const Error_NoParticipantsForThisEvent = 23; //Kann eingentlich nicht auftreten da Ersteller automatisch immer als Teilnehmer des Events eingetragen wird
    const Error_CantCreateEvent =24;
    const Error_CantAddParticipant =25;
    const Error_CantAddGroupToThisEvent = 26;
    const Error_CantDeleteEvent = 27;
    const Error_CantDeleteParticipant = 28;
    const Error_CantDeleteGroupFromEvent = 29;
    const Error_CannotDeleteUserFromEvent = 200;
    const Error_ReachedMaxParticipants = 201;
    const Error_ParticipantAlreadyExisting = 202;
    const Error_GroupAlreadyAdded = 203;

    //GroupErrorCodes:
    const Error_UserNotGroupOwner = 30;
    const Error_WrongGroupPassword = 31;
    const Error_UserHasNoGroups = 32;
    const Error_CouldntCreateGroup = 33;
    const Error_CouldntAddMember = 34;
    const Error_GroupIsPasswordProtected = 35;
    const Error_CantDeleteGroup = 36;
    const Error_CantDeleteMember = 37;
    const Error_CannotDeleteUserFromGroup = 38;
    const Error_CantDeleteGroupEvents = 39;
    const Error_NoGroupWithSuchName = 301;
    const Error_CantFindGroup = 302;
    const Error_NoMembersForGroup = 303;
    const Error_ReachedMaxMembers = 304;

    //LocationErrorCodes;
    const Error_UserNotLocationOwner = 40;
    const Error_NoLocationWithSuchID = 41;
    const Error_NoLocationsFound = 42;
    const Error_CantCreateLocation = 43;
    const Error_CantDeleteLocation = 44;

    //CommentsErrorCodes:
    const Error_CantCreateComment = 50;
    const Error_NoCommentsForEvent = 51;
    const Error_CantDeleteCommentsForEvent = 52;

}
