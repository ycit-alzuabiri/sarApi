<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class invoicecancel extends Model
{
    //
     protected $table      ='financial_cancel_invoices';
     protected $primaryKey ='IDENT';
     public    $timestamps    = false;
     protected $guarded = [];

    

}
