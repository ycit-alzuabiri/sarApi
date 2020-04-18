<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class students extends Model
{
    //
    protected $table='students';

  public function studentbill()
   
  {
  
    return $this->hasMany('App\studentbill');
    
  }

  public function invoicedetail()
  
  {
  
    return $this->hasMany('App\invoicedetails');
    
  }

}
