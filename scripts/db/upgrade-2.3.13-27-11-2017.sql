ALTER TABLE `privatemessages`
  ADD INDEX `timestamp` (`timestamp`),
  ADD INDEX `isread` (`isread`);