<?php

namespace Modules\Authentication\Http\Controllers;

use JWTAuth;
use Validator;
use Config;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{
	//use Helpers;
	public function authenticate(Request $request){
		try {
			$credentials = $request->only(['email', 'password']);
	        $validator = Validator::make($credentials, [
	            'email' => 'required',
	            'password' => 'required',
	        ]);

	        if($validator->fails()) {
	        	//$validator->errors()->all()
	            throw new \Exception("Validation Error",422);
	        }
            // verify the credentials and create a token for the user
            //$moduleComp = 
            if (! $token = JWTAuth::attempt($credentials)) {
                return response(json_encode(['error' => 'invalid_credentials']), 401)
                                    ->header("Access-Control-Allow-Origin"," *");
            }
        } catch (JWTException $e) {
            // something went wrong
            return response(json_encode(['error' => 'could_not_create_token']),500)
                            //->header("Access-Control-Allow-Origin"," *");

        } catch(\Exception $e){
        	return response(json_encode(['error' => $e->getMessage()]), $e->getCode())
                            ->header("Access-Control-Allow-Origin"," *");

        }
        // if no errors are encountered we can return a JWT
        return response(json_encode(compact('token')))
                        ->header("Access-Control-Allow-Origin"," *");
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