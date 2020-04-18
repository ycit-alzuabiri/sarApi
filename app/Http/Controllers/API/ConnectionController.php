<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Controllers\API\BaseController as BaseController;
use DB;
use Validator;
use App\Main;
use Config;
class ConnectionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        //
        
        $this->getrootconnection();
     
        $input = $request->all();
        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required',
            'univ_id' => 'required',

        ]);

        if ($validator->fails()) {
            return $this->sendError('#502', $validator->errors());
        } 

        else {
            $id=$request->input('univ_id');
           // dd(Main::where('UNID',$id)->where('IS_IT_ENABLE',1)->count());
        if(Main::where('UNID',$id)->where('IS_IT_ENABLE',1)->count()>0)
        {
            
            
            $univ=Main::where('UNID',$id)->where('IS_IT_ENABLE',1)->first();
            
            $this->changeEnv('USER',$request->input('email'));
            $this->changeEnv('PASS',$request->input('password'));
         
            $env_update = $this->changeEnv2([
                'DB_DATABASE'   => $univ->UNIV_DB_NAME,
                'DB_PASSWORD'   => "'".$univ->UNIV_API_USER_PASS."'",
                'DB_USERNAME'       =>$univ->UNIV_API_USER_NAME
            ]);        
    
            if($env_update){
                return redirect('api\logins')->with('email',$request->input('email'));
            } else {
                return $this->sendError('#518 ', "Connection Error");
            }
 
            return redirect('api\logins')->with('email',$request->input('email'));
        }else
        {
            return $this->sendError('#517 ', "رقم الجامعة غير موجود");
        }
             
    }
         
    }


    protected function changeEnv2($data = array()){
        if(count($data) > 0){

            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');

            // Split string on every " " and write into array
            $env = preg_split('/\s+/', $env);;

            // Loop through given data
            foreach((array)$data as $key => $value){

                // Loop through .env-data
                foreach($env as $env_key => $env_value){

                    // Turn the value into an array and stop after the first split
                    // So it's not possible to split e.g. the App-Key by accident
                    $entry = explode("=", $env_value, 2);

                    // Check, if new key fits the actual .env-key
                    if($entry[0] == $key){
                        // If yes, overwrite it with the new one
                        $env[$env_key] = $key . "=" . $value;
                    } else {
                        // If not, keep the old one
                        $env[$env_key] = $env_value;
                    }
                }
            }

            // Turn the array back to an String
            $env = implode("\n", $env);

            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);
            
            return true;
        } else {
            return false;
        }
    }

    public static function changeEnv($key,$value)
    {
        $path = base_path('.env');
    
        if(is_bool(env($key)))
        {
            $old = env($key)? 'true' : 'false';
        }
        elseif(env($key)===null){
            $old = 'null';
        }
        else{
            $old = env($key);
        }
    
       
     /*  if (file_exists($path)) {
            file_put_contents($path, str_replace(
                "$key=".$old, "$key=".$value."\n", file_get_contents($path)
            ));
        }*/
        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
            ));
        }
    }
    public function getrootconnection(){

        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sar_router_db']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);

        try{
            DB::purge('mysql');
            DB::reconnect('mysql');
        }catch (\Exception $ex){
            //return response()->json(['result'=>'connection db error'], $this->errorStatus);
            return 'connection db error';
        }
     }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
