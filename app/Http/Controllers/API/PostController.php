<?php

namespace App\Http\Controllers\API;

use http\Env\Response;
use Illuminate\Http\Request;
use Validator;
use Collection;
use App\Http\Controllers\Controller;
use App\Post;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\Post as PostResource;
use App\financialfees;
use App\invoice;
use App\invoicecancel;
use App\invoicedetails;
use App\studentbill;
use App\students;
use Auth;
use App\API_PYMENT;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PostController extends   BaseController
{

    /**
     * 
     * Function to get error massege from database payment function 
     */

     public function get_error($error_no){

        switch ($error_no) {

            case '0': return ' فشلت العملية لسبب غير معروف';      break;
            case '1': return ' نجحت العملية';      break;
         
            case '100': return ' احد المتغيرات المرسلة فارغ او بدون قيمة';      break;
            case '101': return 'احد المتغيرات قيمته غير مناسبة قد يكون تاريخ غير مناسب';      break;
            case '110': return 'السجل المطلوب غير موجود';      break;

            case '120': return 'العملية منفذة من قبل (لقد تم التسديد من قبل) ';      break;
            case '121': return 'التاريخ المحدد للعملية قد انتهى';      break;
            case '122': return 'رقم سند السداد موجود مسبقاً';      break;
           
            case '123': return 'قيمة الحافظة اكبر من المبلغ المطلوب تسديده ';      break;
            case '124': return 'إجمالي قيمة تفاصيل الحافظة اكبر من المبلغ المطلوب تسديده';      break;
            case '125': return 'قيمة الحافظة لا تساوي اجمالي تفاصيلها';      break;

            case '126': return ' لايوجد سعر صرف للعملة المطلوبة';      break;
            case '127': return 'هناك تغير في سعر الصرف عن ما هو مسجل بالحافظة';      break;
            case '128': return 'المبلغ المسدد اكبر من قيمة الحافظة';      break;
      

        }

     }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /* ============================ Function TO Display all Paied Invoices ======================*/

    public function check_blance()
    {

        $invoice = invoice::where('PAYMENT_FLAG',1)->get();

        foreach ($invoice as $value) {

          
            $invoicecost=$value->REAL_FEE_AMOUNT*$value->EXCHANGE_PRICE;

            $REAL_FEE_AMOUNT = invoicedetails::where('INVOICE_IDENT', $value->INVOICE_IDENT)->where('PAYMENT_FLAG', 1)->sum('REAL_FEE_AMOUNT');
            $EXCHANGE_PRICE = invoicedetails::where('INVOICE_IDENT', $value->INVOICE_IDENT)->where('PAYMENT_FLAG', 1)->sum('EXCHANGE_PRICE');
            $invoice_ditails_cost= $REAL_FEE_AMOUNT*$EXCHANGE_PRICE;

            if ($invoice_ditails_cost !=  $invoicecost) {
                invoicedetails::where('INVOICE_IDENT', $value->INVOICE_IDENT)->update(['PAYMENT_FLAG'=>'1']);
            }
       
        }
    }

    public function Token_Check(){

        $Token_id=Auth::user()->token()->id;
        //
        if(DB::table('oauth_access_tokens')->where('id',$Token_id)->count()>0) {
//dd(DB::table('oauth_access_tokens')->where('id', $Token_id)->whereDate('expires_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->count());
            if (DB::table('oauth_access_tokens')->where('id', $Token_id)->whereDate('expires_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))->count() > 0) {
                $Token = DB::table('oauth_access_tokens')->where('id', $Token_id);
                DB::table('oauth_access_tokens')->where('id', $Token_id)->delete();
                return false;
            } else {
                return true;
            }
        }
        else {

            return false;

        }

    }


    public function showinvoices(Request $request)
    {
        
        if( $this->Token_Check()) {


            $input = $request->all();

            $validator = Validator::make($input, [
                'START_DATE' => 'required',
                'END_DATE' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendError('#502', " ");
            } else {
                $startdate = strtotime($input['START_DATE']);
                $enddate = strtotime($input['END_DATE']);


                $startdate = date('Y-m-d', $startdate);
                $enddate = date('Y-m-d', $enddate);
			
			
			
			//	dd(invoice::whereBetween('RECORDED_ON', [$startdate."00:00:00",$enddate."23:59:59"])->get()->count());
				
                if (invoice::whereBetween('RECORDED_ON', [$startdate, $enddate])->get()->count()) {
                    $invoice = invoice::whereBetween('RECORDED_ON', [$startdate, $enddate])->get();

                    $send_invoice = collect();

                    foreach ($invoice as $value) {
                        $send_invoice->push($this->get_state($value));
                    }

                    return $this->sendResponse($send_invoice->toArray(), "#103");
                } else {
                    return $this->sendError('#507', " ");
                }
                //   PostResource::collection($posts);
            }
        }else{
            return $this->sendError('#204', " ");

        }
    }
    public function show_index(Request $request)
{
    //
    if ($this->Token_Check()) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'START_DATE' => 'required',
            'END_DATE' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('#502', $validator->errors());
        } else {
            $startdate = strtotime($input['START_DATE']);
            $enddate = strtotime($input['END_DATE']);


            $startdate = date('Y-m-d', $startdate);
            $enddate = date('Y-m-d', $enddate);

            if (invoice::where('PAYMENT_FLAG', 1)->whereBetween('ACTUAL_PAYMENT_DATE', [$startdate, $enddate])->get()->count()) {
                $invoice = invoice::where('PAYMENT_FLAG', 1)->whereBetween('ACTUAL_PAYMENT_DATE', [$startdate, $enddate])->get();

                $send_invoice = collect();

                foreach ($invoice as $value) {
                    $send_invoice->push($this->get_invoice($value));
                }

                return $this->sendResponse($send_invoice->toArray(), "#103");
            } else {
                return $this->sendError('#507', " ");
            }
            //   PostResource::collection($posts);
        }
    }

else{
    return $this->sendError('#204', " ");
        }
    }

    public function show_invoice_details(Request $request)
    {
        if( $this->Token_Check())
        {

        $input = $request->all();
        $validator = Validator::make($input, [
            'INVOICE_IDENT' => 'required',
            'TYPE' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError('#502', '');
        } else {
            /*==================== Cheack if invoice is found ======================= */

            if (invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->count()>0) {
                $id = $input['INVOICE_IDENT'];
                $invoice = invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->get()->last();

                $invoice_type = $input['TYPE'];
                /*==================== Cheack if invoice is unpaied  ======================= */

                if ($invoice_type == 0) {
                  
                if(invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->whereDate('DEADLINE', '>=', $this->gettoday())->count()>0) 
                { 
                    if (invoicedetails::where('INVOICE_IDENT', $id)->count()) // IF ivoice have details
                    {
                        if (invoicedetails::where('INVOICE_IDENT', $id)->where('PAYMENT_FLAG', 0)->count()) {
                            $invoicedetail = invoicedetails::where('INVOICE_IDENT', $id)->where('PAYMENT_FLAG', 0)->get();
                            $send_invoice_detail = collect();
                            
                            foreach ($invoicedetail as  $value) {
                                $studentbill = studentbill::where('BILL_ID', $value->BILL_ID)->first();

                                $fee = $studentbill->fees;

                                $send_invoice_detail->push([
                                    'invoice_id' => $value->INVOICE_IDENT,
                                    'fee_name' => $fee->FEES_NAME,
                                    'fee_cost' =>  ($value->REAL_FEE_AMOUNT)*($value->EXCHANGE_PRICE),
                                    'level' => $studentbill->S_LEVEL,
                                    'main_category_id' => $fee->FMF_IDENT,
                                    'sub_category_id' => $fee->FEES_CODE,
                                    'fee_state' => $value->PAYMENT_FLAG,
                                    'fee_date' => $value->RECORDED_ON
                                ]);
                            }
                            return  $this->sendResponse($send_invoice_detail->toArray(), "#102");
                        } else {
                            
                            return $this->sendError('#513', " ");
                        }
                    } else {
                        return $this->sendError('#505',  " ");
                    }
                }
                    else{
                        return $this->sendError('#516', " ");
                    }
                } elseif ($invoice_type == 1) {
                    if (invoicedetails::where('INVOICE_IDENT', $id)->count()) // IF ivoice have details
                    {
                        if (invoicedetails::where('INVOICE_IDENT', $id)->where('PAYMENT_FLAG', 1)->count()) {
                            $invoicedetail = invoicedetails::where('INVOICE_IDENT', $id)->where('PAYMENT_FLAG', 1)->get();
                            $send_invoice_detail = collect();

                            foreach ($invoicedetail as  $value) {
                                $studentbill = studentbill::where('BILL_ID', $value->BILL_ID)->first();

                                $feename = $studentbill->fees->FEES_NAME;
                                $send = array(
                                    'invoice_id' => $value->INVOICE_IDENT,
                                    'fee_name' => $feename,
                                    'fee_cost' =>  ($value->REAL_FEE_AMOUNT)*($value->EXCHANGE_PRICE),
                                    'fee_state' => $value->PAYMENT_FLAG,
                                    'fee_date' => $value->RECORDED_ON
                                );
                                $send_invoice_detail->push($send);
                            }
                            return  $this->sendResponse($send_invoice_detail->toArray(), "#102");
                        } else {
                            return $this->sendError('#513', " ");
                        }
                    } else {
                        return $this->sendError('#505', " ");
                    }
                } elseif ($invoice_type == 2) {
                    if (invoicedetails::where('INVOICE_IDENT', $id)->count()) // IF ivoice have details
                    {
                        if (invoicedetails::where('INVOICE_IDENT', $id)->where('PAYMENT_FLAG', 2)->count()) {
                            $invoicedetail = invoicedetails::where('INVOICE_IDENT', $id)->where('PAYMENT_FLAG', 2)->get();
                            $send_invoice_detail = collect();

                            foreach ($invoicedetail as  $value) {
                                $studentbill = studentbill::where('BILL_ID', $value->BILL_ID)->first();
                                $feename = $studentbill->fees->FEES_NAME;
                                $send = array(
                                    'invoice_id' => $value->INVOICE_IDENT,
                                    'fee_name' => $feename,
                                    'fee_cost' =>  ($value->REAL_FEE_AMOUNT)*($value->EXCHANGE_PRICE),
                                    'fee_state' => $value->PAYMENT_FLAG,
                                    'fee_date' => $value->RECORDED_ON
                                );
                                $send_invoice_detail->push($send);
                            }
                            return  $this->sendResponse($send_invoice_detail->toArray(), "#102");
                        } else {
                            return $this->sendError('#513', " ");
                        }
                    } else {
                        return $this->sendError('#505', " ");
                    }
                } else {
                    if ($invoice->PAYMENT_FLAG == 1) //Cheack if invoice is paied 
                        return $this->sendError('#508', " ");
                    else if ($invoice->PAYMENT_FLAG == 2) //Cheack if invoice is Cancel 
                    {
                        return $this->sendError('#509', " ");
                    }
                }
            } else {
                return $this->sendError('#504', " ");
            }
        }

        }else{ 
            return $this->sendError('#204', " ");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function gettoday(){
        $date = date('Y-m-j');
        $newdate = strtotime ( $date )  ;
        $newdate = date ( 'Y-m-j' , $newdate );

        return $newdate;
    }



    public function store(Request $request)
    {
       
        if( $this->Token_Check())
        {
        //
        $input = $request->all();
        $validator = Validator::make($input, [
            'INVOICE_IDENT' => 'required',
            'BONDS_ID' => 'required',
            'BONDS_DATE' => 'required',
            'PAYMENT_BY' => 'required',
            'PAYMENT' => 'required'

        ],[

            'required'=>'هذا الحقل مطلوب'
        ]);





        if ($validator->fails()) {
            return $this->sendError('#502'," ");
        } else {

            $user = Auth::user();

            $userid=$user->id;


            //    Check if invoice found and ready to paid 
            if (invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->count()>0)
            {
                if(invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->whereDate('DEADLINE', '>=', $this->gettoday())->count()>0) 
                {
                    $invoice = invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->get()->last();
             
                    if ($invoice->PAYMENT_FLAG == 0 ) // Check if invoice not paid
                    {


                        $INVOICE_COST=($invoice->REAL_FEE_AMOUNT)*($invoice->EXCHANGE_PRICE);
                        if ($input['PAYMENT'] == $INVOICE_COST)/* cheak the payement is equal invoice cost */ {

                            $sum_invoice_details =  $this->get_real_payment($invoice->INVOICE_IDENT);
                            // invoicedetails::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->get()->sum('COST');
                          
                            if ($sum_invoice_details == $INVOICE_COST)/*cheak the Amount of invoice_detials with cost of invoic */ {

                                if (!(invoice::where('BONDS_ID', $input['BONDS_ID'])->count())) {
                                    /* Edite invoice table */

                                        $invoicedate = date('Y-m-d', strtotime($invoice->RECORDED_ON));
                                        $function_payment_parameter=$this->get_invoice($invoice);
                                        $time_input = strtotime($input['BONDS_DATE']);
                                        $date_input = date('Y-m-d', $time_input);

                                        
                                        if(!($invoicedate> $date_input) )// Check if Bound_Date less than Invoice created Date
                                        {   

                                            $payment=new API_PYMENT;
                                            $payment->FACULTY_IDENT=$function_payment_parameter['faculty_id'];
                                            $payment->PROGRAM_IDENT=$function_payment_parameter['program_id'];
                                            $payment->STUDENT_IDENT=$function_payment_parameter['student_id'];
                                            $payment->INVOICE_IDENT=$function_payment_parameter['invoice_id'];

                                            $payment->BANK_ID=$userid;
                                            $payment->REAL_FEE_AMOUNT=$input['PAYMENT'];
                                            $payment->BONDS_ID=$input['BONDS_ID'];
                                            $payment->BONDS_DATE= $date_input;

                                            $payment->PAYMENT_BY=$input['PAYMENT_BY'];
                                            $payment->ACTUAL_PAYMENT_DATE=date("Y-m-d h:i:s");;
                                            $payment->PAYMENT_FLAG=0;
                                            $payment->RECORDED_ON= $invoice->RECORDED_ON;
                                            $payment->RECORDED_BY= $invoice->RECORDED_BY;
                                            $payment->save();
                                          //  dd($payment->API_PAYMENT_ID);
                                      
                                            $resutlt= collect( DB::select('SELECT financial_function_api_send_payment(?) as msg', 
                                                [
                                                    $payment->API_PAYMENT_ID
                                                    
                                                ]))->first()->msg;

                                                
                                                    if($resutlt==1)
                                                    {
                                                        
                                                        $paiedinvoice=  invoice::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->get()->last();
                                                        $payment_invoice=API_PYMENT::findOrFail($payment->API_PAYMENT_ID);
                                                        $payment_invoice->PAYMENT_FLAG=1;
                                                        $payment_invoice->save();
                                                        $send_invoice = $this->get_invoice($paiedinvoice);
                                                        return $this->sendResponse($send_invoice, "#105");
                                                    }
                                                    else
                                                    {
                                                        
                                                        $payment_invoice=API_PYMENT::findOrFail($payment->API_PAYMENT_ID);
                                                        $payment_invoice->PAYMENT_FLAG=23;
                                                        $payment_invoice->save();
                                                        

                                                      //  API_PYMENT::where('INVOICE_IDENT',$function_payment_parameter['invoice_id'])->delete();
                                                        return $this->sendError('#551', "Server process Internal Error ");   
                                                    }
                                                   

                                                }else{
                                                    
                                                    return $this->sendError('#517', " ");

                                                }
                                 
                                } else {
                                    return $this->sendError('#510', " ");
                                }
                            } else {
                                return $this->sendError('#506', " ");
                            }
                        } else {

                            return $this->sendError('#503', " ");
                        }


                    } else {
                        if ($invoice->PAYMENT_FLAG == 1)
                            return $this->sendError('#508', " ");
                        else if( $invoice->PAYMENT_FLAG == 2)
                            return $this->sendError('#509', " ");

                    }
                }
                else{
                    return $this->sendError('#516', " ");
                }
            } else {

                return $this->sendError('#504', " ");
            }
        }

        }else{
            return $this->sendError('#204', " ");
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function restore($id)
    {
        if( $this->Token_Check())
        {
        $invoice = invoice::findOrFail($id);
        $invoice->PAYMENT = 0;
        $invoice->BONDS_ID = NULL;
        $invoice->BONDS_DATE = NULL;
        $invoice->PAYMENT_FLAG = 0;
        $invoice->PAYMENT_BY = NULL;
        $invoice->ACTUAL_PAYMENT_DATE = NULL;
        $invoice->save();

        $invoicedetails = invoicedetails::where('INVOICE_IDENT', $id)->get();
        foreach ($invoicedetails as $value) {


            $value->PAYMENT_FLAG = 0;
            $value->save();

            if (studentbill::where('BILL_ID', $value->BILL_ID)->count()) {
                $studentbill = studentbill::where('BILL_ID', $value->BILL_ID)->first();
                if ($studentbill->CREDIT != 0)
                    $studentbill->CREDIT = $studentbill->CREDIT - $value->COST;
                $studentbill->save();
            }
        }
        }else{
            return $this->sendError('#204', " ");
        }
    }
    public function showinvoice(Request $request)
    {
     
        if( $this->Token_Check())
        {
           
        $input = $request->all();
        $validator = Validator::make($input, [
            'INVOICE_IDENT' => 'required',
            'TYPE' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError('#502', $validator->errors());
        } else {
            $id = $input["INVOICE_IDENT"];
            $request_type = $input["TYPE"];

        
          
            if (invoice::where('INVOICE_IDENT', $id)->count()>0) {
                if(invoice::where('INVOICE_IDENT', $id)->whereDate('DEADLINE', '>=', $this->gettoday())->count()>0) {
                    $invoice = invoice::where('INVOICE_IDENT', $id)->first();
                 
                    if ($request_type == 0) {
                        if ($invoice->PAYMENT_FLAG == 0) // Check if invoice not paid
                        {  
                           
                            $send = $this->get_invoice($invoice);
                            
                            return $this->sendResponse($send, "#101");
                        } else {
                            if ($invoice->PAYMENT_FLAG == 1) 
                                return $this->sendError('#508', " ");
                            else if ($invoice->PAYMENT_FLAG == 2) {
                                return $this->sendError('#509', " ");
                            }
                        }
                    } elseif ($request_type == 1) {
                        if ($invoice->PAYMENT_FLAG == 1) // Check if invoice not paid
                        {
                            $send = $this->get_invoice($invoice);
                            return $this->sendResponse($send, "#101");
                        } else {
                            if ($invoice->PAYMENT_FLAG == 0)
                                return $this->sendError('#511', " ");
                            else if ($invoice->PAYMENT_FLAG == 2) {
                                return $this->sendError('#509', " ");
                            }
                        }
                    } elseif ($request_type == 2) {

                        if ($invoice->PAYMENT_FLAG == 2) // Check if invoice not paid
                        {
                            $send = $this->get_invoice($invoice);
                            return $this->sendResponse($send, "#101");
                        } else {
                            if ($invoice->PAYMENT_FLAG == 0)
                                return $this->sendError('#511', " ");
                            else if ($invoice->PAYMENT_FLAG == 1) {
                                return $this->sendError('#508', " ");
                            }
                        }
                    }
                }
                else{
                    return $this->sendError('#516', " ");
                }
            } else {

                return $this->sendError('#504', " ");
            }
        }
        }else{
            return $this->sendError('#204', " ");
        }
    }

    public function get_state($invoice)
    {



        $send = [
            'invoice_id' => $invoice->INVOICE_IDENT,


            'invoice_state' => $invoice->PAYMENT_FLAG
         /*   'user_id' => $invoice->PAYMENT_BY,
            'bound_id' => $invoice->BONDS_ID,
            'university_id' => $universityId,
            'faculty_id' => $student->FACULTY_IDENT,
            'program_id' => $student->PROGRAM_IDENT,
            'year' => $student->AC_YEAR,
            'bound_date' => $invoice->BONDS_DATE,
            'payment_date' => $invoice->ACTUAL_PAYMENT_DATE*/
        ];

        return $send;
    }
/** 
 * 
 * THis Function to get Real
 *  Pyments from
 *  Invoice_Ditails 
 * 
 * 
 *  */



public function get_real_paid_cost($id){
    $Fees_Cost=invoicedetails::where('INVOICE_IDENT',$id)->sum('REAL_FEE_AMOUNT');
    $Fees_exchange=invoicedetails::where('INVOICE_IDENT',$id)->first()->EXCHANGE_PRICE;

    if(invoice::findOrFail($id)->PAYMENT_FLAG==1)
    {
    return $Fees_Cost * $Fees_exchange;
    }else
    {
        return 0;
    }

}
    public function get_real_payment($id){
        
        $Fees_Cost=invoicedetails::where('INVOICE_IDENT',$id)->sum('REAL_FEE_AMOUNT');
      //  $Fees_exchange=invoicedetails::where('INVOICE_IDENT',$id)sum('EXCHANGE_PRICE');

        $Fees_Cost=collect(DB::select('SELECT SUM(REAL_FEE_AMOUNT*EXCHANGE_PRICE) as price FROM financial_invoices_details WHERE INVOICE_IDENT = ?', [$id]))->first()->price;

  // dd($Fees_Cost);
        return $Fees_Cost;
     

    }
    public function get_invoice($invoice)
    {
        if( $this->Token_Check())
        {
          //  dd(students::where('STUDENT_IDENT', $invoice->STUDENT_IDENT));
        $student = students::where('STUDENT_IDENT', $invoice->STUDENT_IDENT)->firstOrFail(); //->S_FIRST_NAME;
      
        $studentName = $student->S_FIRST_NAME . ' ' . $student->S_LAST_NAME;
        //$studentname = $studentname . ' ' . students::where('STUDENT_IDENT', $invoice->STUDENT_IDENT)->first()->S_LAST_NAME;

       // $universityId = DB::table('university')->value('UNIV_IDENT');

        $send = [
            'invoice_id' => $invoice->INVOICE_IDENT,
            'student_name' => $studentName,
            'student_id' => $invoice->STUDENT_IDENT,
            'invoice_cost' => ($invoice->REAL_FEE_AMOUNT)*($invoice->EXCHANGE_PRICE),
            'invoice_payment' => $this->get_real_paid_cost($invoice->INVOICE_IDENT),
            'invoice_state' => $invoice->PAYMENT_FLAG,
            'user_id' => $invoice->PAYMENT_BY,
            'bound_id' => $invoice->BONDS_ID,
           // 'university_id' => $universityId,
            'faculty_id' => $student->FACULTY_IDENT,
            'program_id' => $student->PROGRAM_IDENT,
            'year' => $student->AC_YEAR,
            'bound_date' => $invoice->BONDS_DATE,
            'payment_date' => $invoice->ACTUAL_PAYMENT_DATE
        ];

        return $send;
        }else{
            return $this->sendError('#204', " ");
        }

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    { }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $post = Post::findOrFail($id);
        $post->delete();
        return new PostResource($post);
    }

    public function cancel(Request $request)
    {
        if( $this->Token_Check())
        {
        $input = $request->all();

        $validator = Validator::make($input, [
            'INVOICE_IDENT' => 'required'

        ]);

        if ($validator->fails()) {
            return $this->sendError('#502', " ");
        } else {

            if (API_PYMENT::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->where('PAYMENT_FLAG', '!=', 0)->count() > 0) {

                $user = Auth::user();
                $userid = $user->id;
               // dd(API_PYMENT::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->where('BANK_ID', $userid)->count());
                if (API_PYMENT::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->where('BANK_ID', $userid)->count() > 0) {
                    $invoice = API_PYMENT::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->first();

                    $invoice->PAYMENT_FLAG = 2;
                    $invoice->save();

                  /*  $invoice_cancel = new invoicecancel();

                    $invoice_cancel->fill($invoice->toArray());
                    $invoice_cancel->save();

                    $invoice_details = invoicedetails::where('INVOICE_IDENT', $input['INVOICE_IDENT'])->get();

                    foreach ($invoice_details as $value) {
                        /* Edite invoice_details table 
                        $value->PAYMENT_FLAG = 2;
                        $value->save();

                        /* Edite student_bill table */
                     /*   $student_bill_id = $value->BILL_ID;
                        $studentbill = studentbill::where('BILL_ID', $student_bill_id)->first();
                        $studentbill->CREDIT = $studentbill->CREDIT - $value->COST;
                        $studentbill->save();*/
                  //
                  return $this->sendResponse($this->get_state($invoice), "#104");
                } else {
                    return $this->sendError('#515', " ");
                }
            } else {
                return $this->sendError('#514', " ");
            }
        }
        }else{
            return $this->sendError('#204', " ");
        }

    }

    public function show_cancel_invoices(Request $request)
    {
        if( $this->Token_Check())
        {

        $invoice = invoicecancel::where('PAY_METHOD_ID', 0)->get();
        $send_invoice = collect();

        foreach ($invoice as  $value) {
            $send_invoice->push($this->get_invoice($value));
        }

        return  $this->sendResponse($send_invoice->toArray(), "#103");

        }else{
            return $this->sendError('#204', " ");
        }
        /*
        $input=$request->all();
        $validator = Validator::make($input, [
            'INVOICE_IDENT'=>'required'

            ]);
        if($validator->fails()){
            return $this->sendError('#502', $validator->errors());       
        }
        else
        {
             /*==================== Cheack if invoice is found ======================= */

        /*  if(invoice::where('INVOICE_IDENT',$input['INVOICE_IDENT'])->count())
            {
                $id=$input['INVOICE_IDENT'];
                $invoice = invoice::where('INVOICE_IDENT',$input['INVOICE_IDENT'])->get()->last();

                /*==================== Cheack if invoice is unpaied  ======================= */

        /*   if($invoice->PAYMENT_FLAG==2)
                {
                    if(invoicedetails::where('INVOICE_IDENT',$id)->count())// IF ivoice have details
                        {
                            $invoicedetail = invoicedetails::where('INVOICE_IDENT',$id)->where('PAYMENT_FLAG',2)->get();
                            $send_invoice_detail=collect();
                            foreach ($invoicedetail as  $value)
                                {
                                    $studentbill=studentbill::where('BILL_ID',$value->BILL_ID)->first();
                                    $feename=$studentbill->fees->FEES_NAME;
                                    $send=array('invoice_id'=>$value->INVOICE_IDENT,
    
                                    // 'student_bill_id'=>$value->BILL_ID,
                                    'fee_name'=>$feename,
                                    'fee_cost'=>$value->COST,
                                    'fee_state'=>$value->PAYMENT_FLAG,
                                    'fee_date'=>$value->RECORDED_ON);
                                    $send_invoice_detail->push($send);
                                }
                            return  $this->sendResponse($send_invoice_detail->toArray(),"#102");

                        }
                    else
                     {
                          return $this->sendError('#505', " تفاصيل الحافظة غير موجودة ");    
                     }
                }
                else{
                    if($invoice->PAYMENT_FLAG==1)//Cheack if invoice is paied 
                        return $this->sendError('#508', "الحافظة مسسدة من سابقا "); 
                    else if($invoice->PAYMENT_FLAG==2)//Cheack if invoice is Cancel 
                         {
                             return $this->sendError('#509', "الحافظة ملغية "); 
                         }   

                    }

            }
            else
            {
                 return $this->sendError('#504', " الحافظة غير مجودة ");    
            }
        }*/
    }
    public function show_cancel()
    {
        //
        if( $this->Token_Check())
        {
        if (invoice::where('PAYMENT_FLAG', 2)->where('PAY_METHOD_ID', 0)->get()->count()) {
            $invoice = invoice::where('PAYMENT_FLAG', 2)->get();
            $send_invoice = collect();

            foreach ($invoice as  $value) {

                $send_invoice->push($this->get_invoice($value));
            }

            return  $this->sendResponse($send_invoice->toArray(), "#103");
        } else {
            return $this->sendError('#507', " ");
        }
        //   PostResource::collection($posts);
        }else{
            return $this->sendError('#204', " ");
        }
    }

    public function check_invoice()
    {
        //
        if( $this->Token_Check())
        {
        if (invoice::where('PAYMENT_FLAG', 2)->get()->count()) {
            $invoice = invoice::where('PAYMENT_FLAG', 2)->get();
            $send_invoice = collect();

            foreach ($invoice as  $value) {
                $send_invoice->push($this->get_invoice($value));
            }

            return  $this->sendResponse($send_invoice->toArray(), "#103");
        } else {
            return $this->sendError('#507', " ");
        }
        }else{
 
            return $this->sendError('#204', " ");
        }
        //   PostResource::collection($posts);
    }
}