<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Config;
use App\Http\Controllers\Controller;

class Connection extends Controller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
      
        env('DB_CONNECTION','mysql2');
     
                
               //  dd(session()->get('UNIV_API_USER_NAME'));
              
        return $next($request);
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

    if (file_exists($path)) {
        file_put_contents($path, str_replace(
            "$key=".$old, "$key=".$value, file_get_contents($path)
        ));
    }
}
}
