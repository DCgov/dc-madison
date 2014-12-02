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

Artisan::add(new CreateUser);
Artisan::add(new UserRole);
Artisan::add(new SponsorCommand());
Artisan::add(new ActivityExport);
Artisan::add(new DatabaseBackup);
Artisan::add(new CreateRole);
Artisan::add(new dbUpdateGroups);
Artisan::add(new DatabaseClear);
