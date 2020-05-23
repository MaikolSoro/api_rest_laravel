<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;
class CategoryController extends Controller
{
    public function _construc() {
        $this-> middleware('api.auth', ['except' => ['index', 'show']]);
    }
    public function index() {
        $categories = Category::all();

        return response() -> json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id) {
        var_dump($id); die();

        $category = Category::find($id);

        if(is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'categories' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        // recoger los datos por  post
        $json = $request-> input('json', null);
        $params_array = json_decode($json, true);
        if(!empty($params_array)) {
            
                // validar los datos
                $validate = \Validator::make($params_array,[
                    'name' => 'required'
                ]);
                // Guardar la categoria
                if($validate-> fails()) {
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No se ha guardado la categoria.'
        
                    ];
                } else {
                    $category = new Category();
                    $category->name = $params_array['name'];
                    $category-> save();
        
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'category' => $category
        
                    ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'

            ];
        }
        // Devolver el resultado

        return response()->json($data, $data['code']);
    }

    /**
    *
    * Actualizar la categoría
    *
    */
    public function update($id, Request $request) {

        // Recoger los datos por post
        $json = $request-> input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)) {

            // validar los datos
            $validate = \Validate::make($params_array,[
                'name' => 'required'
            ]);

            //Quitar lo que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['created_at']);
            //Actualizar el registro(categoria)
            $category = Category:: where('id', $id) ->update($params_array);
            $data = [
                'code'=> 200,
                'status' => 'success',
                'category' => $params_array
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'

            ];
        }
         //Devolver respuesta
        return response() ->json($data, $data['code']);

    }
}
