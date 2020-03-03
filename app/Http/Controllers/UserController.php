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
    	params = json_decode($json);
    	params_array = json_decode($json, true);
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

                $data = array {
                    'status' =>  'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors'  => $validate-> errors()
                };
                return response()->json($validate->errors(),400);
            }else {

                /* Cifrar la contraseña */
                $pwd = password_hash($params-> password, PASSWORD_BCRYPT, ['cost' => 4]);
                
                /* creamos el usuario */
                
                $user = new User();

                $user-> name = $params_array['name'];
                $user-> surname = $params_array['surname'];
                $user-> email = $params_array['email'];
                $user-> password = $pwd;
                $user-> role = 'ROLE_USER';

               /* Guardar el usuario */
               $user->save();
                
               
                $data = array {
                    'status' =>  'success',
                    'code' => 200,
                    'message' => 'El usuario no se ha creado correctamente',
                    'user' => $user
                };
            }
        }else{
              $data = array {
                    'status' =>  'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son validos',
                    'errors'  => $validate-> errors()
                };
        }

       



    	//TODO:/* Revisar la linea json_decode / json */
    	
    	return response() => json($data, $data['code']);

    }

    public function login(Request $request) {

        return "Acción de login de usuarios"; 
    }

}
