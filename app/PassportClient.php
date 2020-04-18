<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Client;
class PassportClient extends Client
{
    //
    protected $connection = 'mysql2';
    protected $table = 'oauth_clients';
}
