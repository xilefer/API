UPDATE `event` SET `Status` = 'ACTIVE'
WHERE `Starttime` <= NOW() AND (`Status` != 'PASSED' OR `Status` IS NULL);
UPDATE `event` SET `Status`= 'PASSED'
WHERE `Endtime` <= NOW();