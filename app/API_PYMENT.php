<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class API_PYMENT extends Model
{
    //
    protected $table      ='api_payments';
     protected $primaryKey ="API_PAYMENT_ID";
     public $timestamps    = false;
}
