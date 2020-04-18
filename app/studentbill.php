<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class studentbill extends Model
{
    //
     protected $table='financial_students_bills';
     protected $primaryKey="BILL_ID";
     public $timestamps = false;

   public function invoicedetail()
    {
        return $this->hasMany('App\invoicedetails');
    }

       public function student()
    {
        return $this->belongsTo('App\students','STUDENT_IDENT');
    }
    
    public function fees()
    {
        return $this->belongsTo('App\financialfees','FEES_ID');
    }
    
}
