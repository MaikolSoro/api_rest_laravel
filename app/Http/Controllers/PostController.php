<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;
class PostController extends Controller
{
     public function _construc() {
        $this-> middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {
        $posts = Post::all()-> Load('category'); // sacos todos los datos

        return response() -> json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id) {
        $post = Post::find($id) -> load('category');

        if(is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    /*=============================================
                 Guardar el post            
     =============================================*/
    public function store(Request $request) {
        // Recoger los datos por  post
        $json = $request-> input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)) {
                // Conseguir usuario identificado
                $jwtAuth = new JwtAuth();
                $token = $request-> header('Authorization', null);
                $user = $jwtAuth->checkToken($token, true);

                // Validar los datos
                $validate = \Validator::make($params_array,[
                    'title' => 'required',
                    'content' => 'required',
                    'category_id' => 'required',
                    'image' => 'required'
                ]);
                // Guardar el articulo
                if($validate-> fails()) {
                    $data = [
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'No se ha guardado el articulo, faltan datos'
        
                    ];
                } else {
                    // Guardar el articulo
                    $post = new Post();
                    $post->user_id = $user->sub;
                    $post->category_id = $params->category_id;
                    $post->title = $params->title;
                    $post->content = $params-> content;
                    $post->image = $params->image;
                    $post-> save();
        
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post
        
                    ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Envia los datos correctamente.'

            ];
        }
        // Devolver el resultado

        return response()->json($data, $data['code']);
    }

    /*=============================================
                 Actualizar el post            
     =============================================*/

     public function update($id, Request $request) {

        // Recoger los datos por post
        $json = $request-> input('json', null);
        $params_array = json_decode($json, true);

        // Datos para devolver
        $data = [
            'code' => 404,
            'status' => 'error',
            'message' => 'Envia los datos correctamente.'

        ];

        if(!empty($params_array)) {

            // validar los datos
            $validate = \Validate::make($params_array,[
                    'title' => 'required',
                    'content' => 'required',
                    'category_id' => 'required',
            ]);

            if($validate->fails()) {
                $data['errors'] = $validate->error();
                return response() ->json($data, $data['code']);
            }
            //Eliminar lo que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

            //Actualizar el registro(categoria)
            $post = Post:: where('id', $id) ->updateOrCreate($params_array);
            $data = [
                'code'=> 200,
                'status' => 'success',
                'post' => $post,
                'changes' => $params_array
            ];
        } 
         //Devolver respuesta
        return response() ->json($data, $data['code']);

    }

    /*=============================================
                 Eliminar un post            
     =============================================*/
    public function delete($id, Request $request) {

        // comprobar si existe el registro
        $post = Post::find($id);
        if(!empty($post)){
            //Borrarlo
            $post -> delete();
            // Devolver algo

            $data = [
                'code'=> 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code'=> 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }
       
        return response() ->json($data, $data['code']);
    }
}
