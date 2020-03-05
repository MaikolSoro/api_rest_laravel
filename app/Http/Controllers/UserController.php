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
      * Metodo de update, actualizo el usuario
      * Recoger los datos por post
      * validar los datos
      * Quitar los campos que no quiero actualizar
      * Actualizar el usuario de la BD
      * Devolver el array con resultado
      */
     
    
    public function update(Request $request) {

        /**Comprobar si el usuario está identificado */
        $token = $request->header('Authorization');
        $JwtAuth = new \JwtAuth();
        $checkToken = $JwtAuth->checkToken($token);

         // Recoger los datos por post
         $json = $request->input('json', null);
         $params_array = json_decode($json, true);

        if($checkToken && !empty($params_array)){
          
            // sacar el usuario identificado

            $checkToken = $jwtAuth-> checkToken($token, true);

           // Valido los datos
           $validate = \Validator::make($params_array,[
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            'email' => 'required|email|unique:users,'.$user->sub
         ]);
         
         //Quitar los campos que no quiero actualizar

         unset($params_array['id']);
         unset($params_array['role']);
         unset($params_array['password']);
         unset($params_array['created_at']);
         unset($params_array['remember_token']);

        // Actualizar el usuario de la BD

        $user_update = User::where('id', $user->sub)-> update($params_array);
        $data = array(
             'code' => 200,
             'status' => 'success',
             'user' => $user,
             'changes' => $params_array
        );

        } else   {
         $data = array(
             'code' => 400,
             'status' => 'error',
             'message' => 'El usuario no está identificado.'
         );
        }
        return response()-> json($data, $data['code']);
      }
      
      /* Metodo para subir arhivos */

      /**
      *
      * Recoger datos de la peticiòn
      *Guardar la imagen
      */
      public function upload(Request $request){

        $image = $request->file('file0');

        if($imagen){
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        } else {
            
            /* Devolver el resultado */
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        }
        return response()->json($data, $data['code']);
        
      }

}

