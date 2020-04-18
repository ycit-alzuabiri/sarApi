<?php

namespace App;


use Laravel\Passport\PersonalAccessClient;
class PassportPersonalAccessClient extends PersonalAccessClient
{
    //
    protected $connection = 'mysql2';
    protected $table = 'oauth_personal_access_clients';
}
