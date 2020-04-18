<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    //

     protected $table      ='financial_invoices';
     protected $primaryKey ="INVOICE_IDENT";
     public $timestamps    = false;
}
