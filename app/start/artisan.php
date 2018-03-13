<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/
Artisan::add(new ImportUsersFromSkeddaCommand);
Artisan::add(new WordpressSyncCommand);
Artisan::add(new AccountingExportCommand);
Artisan::add(new BirthdayPostCommand);
Artisan::add(new UpdateMemberStatusCommand);
Artisan::add(new CronRunCommand);
Artisan::add(new OdooUpdateCommand);
Artisan::add(new OdooGetUnassignedOpenOrderCommand);
//Artisan::add(new CatalyzCashflowSyncCommand);
