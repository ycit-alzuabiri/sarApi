<?php

namespace App;


use Laravel\Passport\AuthCode;
class PassportAuthCode extends AuthCode
{
    //
    protected $connection = 'mysql2';
    protected $table = 'oauth_auth_codes';
}
