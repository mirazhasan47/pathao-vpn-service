<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;


class AppApiMiddleware
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

       /*$response = [
        'result' => 'System under construction. Sorry for that.',
    ];
    return response()->json($response);
    exit();*/

    if($request->header('token') == '' || is_null($request->header('token')))
    {
        $response = [
            'result' => 'Unauthorized',
        ];

        return response()->json($response);
    }
    else
    {
        $token = $request->header('token');
        $result = DB::table('customers')->select('id')->where('app_token', $token)->get();

        if(count($result) > 0){
            return $next($request);
        }else{
            $response = [
                'result' => 'Unauthorized',
            ];
            return response()->json($response);
        }

    }
}
}
