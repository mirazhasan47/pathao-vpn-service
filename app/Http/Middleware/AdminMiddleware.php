<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\DB;

use Closure;

class AdminMiddleware
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

        /*if(session()->has('id') && session()->has('name')){
            return $next($request);
        }else{
            return redirect('/login');
        }*/

        if(session('acc_no')=="1000")
        {
            if(session()->has('id') && session()->has('name'))
            {
                if(session('id')==1)
                {
                    return $next($request);
                }
                else
                {
                    $menu = request()->segment(1);
                    $id = session('id');
                    $user = DB::table('users')->select('designation')->where('id', $id)->first();
                    if (!empty($user->designation)) 
                    {

                        		$datatt['type']="loginCheck"; 
                                $datatt['testdata']= json_encode($id."-".$menu."-".$user->designation);
                                DB::table('test2')->insert($datatt);

                        $data = DB::table('role_permission')->select('menu')->where('permission', 0)->where('menu', $menu)->where('role_id', $user->designation)->get();
                        if(count($data)>0)
                        {                        
                            return redirect('/permission-denied'); 
                        }
                        else
                        {
                            return $next($request);                        
                        }
                    }
                    else
                    {
                        return redirect('/permission-denied');
                    }                
                }

            }
            else
            {
                return redirect('/login');
            }
        }
        else
        {
            if(session()->has('id') && session()->has('name')){
                return $next($request);
            }else{
                return redirect('/login');
            }
        }

        
        
    }
}
