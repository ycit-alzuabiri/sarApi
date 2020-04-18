<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\User;
use App\Http\Controllers\Controller as Controller;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;
use App\Main;
use Config;
use Artisan;

class AuthController extends BaseController
{
    //

       public $successStatus = 100;



public function change_connection($HOST,$DATABASE,$PORT,$USER,$PASS){

            DB::purge('mysql2');
            Config::set('database.connections.mysql2.host', $HOST) ;
            Config::set('database.connections.mysql2.port', $PORT) ;
            Config::set('database.connections.mysql2.database', $DATABASE) ;
            Config::set('database.connections.mysql2.username', $USER) ;
            Config::set('database.connections.mysql2.password', $PASS) ;
             
             DB::reconnect('mysql2');
          
}

 public function api_signup(Request $request)
    {
       
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);


        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

     
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        

        $this->change_connection('127.0.0.1','sar_router_db','3306','root','');
        $univ=  DB::connection('mysql2')->table('universities')->where('IS_IT_ENABLE',1)->get();
     
        foreach ($univ as $key => $value) {
            $this->change_connection($value->UNIV_HOST,$value->UNIV_DB_NAME,'3306',$value->UNIV_DB_USER_NAME,$value->UNIV_DB_USER_PASS);
            
            $max=User::get()->last()->id;
            $max=$max+1;
            $user =new User();
            $user->id=$max;
            $user->name=$input['name'];
            $user->email=$input['email'];
            $user->password=$input['password'];
            $user->save();

        }

        return $this->sendResponse('', 'User register successfully.');
    


      /*  
      $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
        */


        
    }


 public function login(){


}

public function logins(){ 
    
  
$email=env('USER');//session()->get('email');
$password=env('PASS');

    if(Auth::attempt(['email' => $email, 'password' => $password])){ 


      //  Artisan::call('passport:purge');

        $user = Auth::user();

       $token=$user->createToken('MailApi');
       $success['token'] =  $token->accessToken;
      // dd(DB::connection('mysql2')->table('oauth_access_tokens')->where('id',$token->token->id)->count()) ;
        $tokens=[$token->token->id,''];
      DB::connection('mysql2')->table('oauth_access_tokens')->whereNotIn('id',$tokens)
      ->delete();
       DB::connection('mysql2')->table('oauth_access_tokens')->where('id',$token->token->id)
           ->update(['expires_at'=>Carbon::now()->addDay(1)]);
       return $this->sendResponse($success, $this->successStatus);
   
 } else{ 
         return $this->sendError('#501' ,'Unauthorised Error.' );  
  
  }  
  
}
}
