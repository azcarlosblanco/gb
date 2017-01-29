<?php

namespace App\Http\Controllers\Nova;

use JWTAuth;
use Validator;
use Config;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends NovaController
{
	//use Helpers;
	public function authenticate(Request $request){
		$code = null;
        try {
			$credentials = $request->only(['email', 'password']);
	        $validator = Validator::make($credentials, [
	            'email' => 'required',
	            'password' => 'required',
	        ]);

	        if($validator->fails()) {
                $code = 422;
                $this->novaMessage->setData($$validator->errors());
                throw new \Exception("El formulario contine errores", 422);
	        }
            // verify the credentials and create a token for the user
            //$moduleComp = 
            if (! $token = JWTAuth::attempt($credentials)) {
                $code = 401;
                throw new \Exception("Credenciales Inválidas", 401);
            }
            // if no errors are encountered we can return a JWT
            $code = 200;
            $this->novaMessage->setData(['token'=>$token]);
        } catch (JWTException $e) {
            // something went wrong
            $this->novaMessage->addErrorMessage('Error',"could_not_create_token");
        } catch(\Exception $e){
            $this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
        }

        if($code==null){
            $code = 500;
        }
        return $this->returnJSONMessage($code);
	}

    public function token(){
        $code = null;
        try{
            $token = JWTAuth::getToken();
            if(!$token){
                throw new BadRequestHtttpException('Token not provided');
            }
            $token = JWTAuth::refresh($token);
            $code = 200;
            $this->novaMessage->setData(['token'=>$token]);
        }catch(TokenInvalidException $e){
            $this->novaMessage->addErrorMessage('Error',"token_invalid");
        }catch(\Exception $e){
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }

        if($code==null){
            $code = 500;
        }
        return $this->returnJSONMessage($code);
    }

    public function logout(){
        try{
            JWTAuth::invalidate(JWTAuth::getToken());
            $code = 200;
        }catch(TokenInvalidException $e){
            $code = 401;
            $this->novaMessage->addErrorMessage('Error',"token_invalid");
        }catch(\Exception $e){
            $code = 500;
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
    }

	public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->header("Access-Control-Allow-Origin"," *")
                                    ->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->header("Access-Control-Allow-Origin"," *")
                                ->json(['token_expired'], $e->getStatusCode());

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->header("Access-Control-Allow-Origin"," *")
                                ->json(['token_invalid'], $e->getStatusCode());

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->header("Access-Control-Allow-Origin"," *")
                                ->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->header("Access-Control-Allow-Origin"," *")
                            ->json(compact('user'));
    }


}