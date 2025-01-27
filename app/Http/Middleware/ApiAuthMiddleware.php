<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
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
          /**Comprobar si el usuario está identificado */
          $token = $request->header('Authorization');
          $JwtAuth = new \JwtAuth();
          $checkToken = $JwtAuth->checkToken($token);

          if($checkToken){
              
              return $next($request);

          } else {

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado',
            );
            return response()-> json($data, $data['code']);
            
          }
    }
}
 