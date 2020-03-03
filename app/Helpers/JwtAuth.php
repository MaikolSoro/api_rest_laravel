<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\user;

class JwtAuth {

 public $key;	

	 function __construct()
	 {
	 	$this-> key = 'esto_es_una_clave_super_secreta-9999'
	 }
	/**
	 *
	 * Buscar si existe el usuario con sus credenciales
	   Comprobar si son correctas(objeto)
	   Generar el token con los datos del usuario idenficado
	   Develer los datos  decodificados  o el token, en función de un parámetro
	 *
	 */

	public function signup($email, $password, $getToken = null){

		$user = User::where([
			'email' => $email,
			'password' => $password
		])->first();
		$signup = false;

		if (is_object($user)) {
			$signup = true;
		}

		/* Generar el token */

		if ($signup) {

			$token = array (
				'sub' =>   $user-> id,
				'email' => $user-> email,
				'name' =>  $user-> name,
				'surname' => $user-> surname,
				'iat'     => time(),
				'exp'     =>  time() + (7*24 * 60 * 60)
			);

			$jwt = JWT::encode($token, this->$key, 'HS256');
			$decoded = JWT::decoded($jwt, $this->key, ['HS256']);

				if(is_null($getToken)){
					$data = $jwt;

				}else{
					$data = decoded;
				}
		} else {

			$data = array(
				'status' => 'error',
				'message' => 'Login incorrecto'
			);
		}

	}
	
	public function checkToken($jwt,$getIdentity = false){
		$auth = false;
		try {
			$jwt = str_replace('"', '', $jwt);
			$decoded = JWT::decoded($jwt, $this->key, ['HS256']);

		} catch (\UnexpectedValueException $e) {
			$auth = false;
		}catch(\DomainException $e){
			$auth = false;
		}

		if(!empty($decoded) && is_object($decoded) && isset($decode->sub)){
			$auth = true;
		}else{
			$auth = false;
		}
		if($getIdentity){
			return $decoded;
		}

		return response()->json($signup,200);

	}

	public function update(Request $request){
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