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
                $user = $this->getIdentity($request);

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

                $user = $this->getIdentity($request);
            //Actualizar el registro(categoria)
            $post = Post::where('id', $id)->where('user_id', $user->sub)-> first();

            if(!empty($post) && is_object($post)) {

                $post->update($params_array);
                $data = [
                    'code'=> 200,
                    'status' => 'success',
                    'post' => $post,
                    'changes' => $params_array
                ];
            }
            // $where = [
            //     'id' => $id,
            //     'user_id' => $user->sub
            // ];
            // $post = Post:: updateOrCreate($where, $params_array);
           
        } 
         //Devolver respuesta
        return response() ->json($data, $data['code']);

    }

    /*=============================================
                 Eliminar un post            
     =============================================*/
    public function delete($id, Request $request) {

      $user = $this->getIdentity($request);

        // comprobar si existe el registro
        $post = Post::where('id', $id)->where('user_id', $user->sub)-> first();

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

    private function getIdentity($request){

        $jwtAuth = new JwtAuth();
        $token = $request-> header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request) {
        // Recoger la imagen de la petición
        $image= $request-> file('file0');
        // Validar imagen
        $validate =\Validator::make($request-> all(),[
            'file0' =>'required|image|mimes:jpg,jpeg,png, gif'
        ]);
        // Guardar la imagen
            if(!$image || $validate-> failes()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error al subir la imagen'
                ];
            } else {
                $image_name = time().$image->getClientOriginalName();
                \Storage::disk('images')->put($image_name, \File::get($image));

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'image' => $image_name
                ];
            }
        // Devolver datos
        return response() ->json($data, $data['code']);
    }
}
