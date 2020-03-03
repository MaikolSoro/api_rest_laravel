<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
class UserController extends Controller
{
    //


    public function register(Request $request){

    	/**
    	 *
    	 * Recoger los datos del usuario por post
    	 * Validar datos
    	 * Cifrar la contraseña
    	 *  Comprobar si el usuario existe ya (duplicado)
		 *	Crear el usuario
    	 */

    	$json = $request -> input('json', null);
    	$params = json_decode($json);
    	$params_array = json_decode($json, true);
    	// var_dump( $params-> name ) ;
    	// die();

        if(!empty($params) && !empty(params_array)){

         /*=========================================
          =            Limpiar los datos            =
          =========================================*/
          
          $params_array = array_map('trim', $params_array);
          
          /*=====  End of Limpiar los datos  ======*/
          
            

         $validate = \Validator::make($params_array,[
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            'email' => 'required|email|unique:users',
            'password' => 'required'

         ]);     
            if($validate->fails()){

                $data = array(
                    'status' =>  'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors'  => $validate-> errors()
                
                );
                return response()->json($validate->errors(),400);
            }else {

                /* Cifrar la contraseña */
                $pwd = hash('sha256', $params->password);
                
                /* creamos el usuario */
                
                $user = new User();

                $user-> name = $params_array['name'];
                $user-> surname = $params_array['surname'];
                $user-> email = $params_array['email'];
                $user-> password = $pwd;
                $user-> role = 'ROLE_USER';

               /* Guardar el usuario */
               $user->save();
                
               
                $data = array (
                    'status'=>  'success',
                    'code'=> 200,
                    'message'=> 'El usuario no se ha creado correctamente',
                    'user'=> $user
                );
            }
        }else{
              $data = array (
                    'status' =>  'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son validos',
                    'errors'  => $validate-> errors()
              );
        }

    	return response()->json($data, $data['code']);

    }

    /**
     *
     * Método para login, se le envia el email, password
     *
     */
    
    public function login(Request $request) {
       $jwtAuth = new \jwtAuth();

        $json = $request->input('json', null);
        $params=  json_decode($json);
        $params_array = json_decode($json, true);
        
        $validate = \Validator::make($params_array,[
            'email' => '<required></required>',
            'password' => 'required'

         ]);     
            if($validate->fails()){

                $signup  = array (
                    'status' =>  'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha podido loguearse',
                    'errors'  => $validate-> errors()
                );
                return response()->json($validate->errors(),400);
            } else {

                  $pwd = hash('sha256', $params->password);
                  $signup = $jwtAuth->signup($params->email,$pwd);
                  if(!empty($params->getToken)){
                    $signup = $jwtAuth-> signup($params-> email, $pwd, true);
                  }
            }

       return response()->json(($signup),200);
    }

  
     /**
      *
      * Metodo de update, actualizo el token
      *
      */
     
    
    public function update(Request $request) {
        $token = $request->header('Authorization');
        $JwtAuth = new \JwtAuth();
        $checkToken = $JwtAuth->checkToken($token);

        if($checkToken){
          echo "<h1>Login correcto</h1>";
        }else{
            echo "<h1>Login incorrecto</h1>";
        }
        die();
      }

}

