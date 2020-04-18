<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class invoicedetails extends Model
{
    //
      protected $table ='financial_invoices_details';
       protected $primaryKey ='ID_IDENT';
    public    $timestamps    = false;

       public function studentbill()
    {
        return $this->belongsTo('App\studentbill','BILL_ID');
    }

        public function student()
    {
        return $this->belongsTo('App\students','STUDENT_IDENT');
    }
     	 
}
