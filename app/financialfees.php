<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class financialfees extends Model
{
    //
    protected $table     ='financial_fees';
    protected $primaryKey ='FEES_ID';
    public    $timestamps    = false;

    public function studentbill()
    {
        return $this->hasMany('App\studentbill');

        
    }
}
