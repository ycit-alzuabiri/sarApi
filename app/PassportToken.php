<?php

namespace App;



use Laravel\Passport\Token;

class PassportToken extends Token
{
    //
    protected $connection='mysql2';
    protected $table = 'oauth_access_tokens';
}
