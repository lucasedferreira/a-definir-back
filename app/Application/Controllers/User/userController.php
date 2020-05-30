<?php

namespace Controllers\User;

use Illuminate\Http\Request;

use Laravel\Lumen\Routing\Controller as BaseController;
class MainController extends BaseController
{
    public function register(Request $request)
    {
        $user = $request->all();
        return \User\Service::register($user);
    }

    public function create(Request $request)
    {
        $user = $request->all(); 
        $user = \User\Service::create($user);
        return $user;
    }

    public function delete($userID)
    {
        \User\Repository::delete($userID);
    }    

    public function update(Request $request, $userID)
    {
        $user = $request->all();
        \User\Repository::update($userID, $user);
    }
    public function checkIfPasswordIsOld(Request $request, $userID)
    {
        $result = User\Service::checkIfPasswordIsOld($userID);
        return response()->json($result, 200);
    }

    public function updatePasswordByOld(Request $request, $userID)
    {
        User\Service::updatePasswordByOld($userID, $request->oldPassword, $request->newPassword, $request->newPasswordConfirm);
    }

    public function getUserByJWT(Request $request)
    {
        return $request->user->toJson();
    }

    public function deleteToken(Request $request)
    {
        \PasswordReset\Repository::deleteByToken($request->token);
    }

    public function checkRecoveryToken(Request $request)
    {
        if(!\PasswordReset::checkByToken($request->token)){
            return response()->json([
                'message' => 'Invalid Token'
            ], 400);
        }
    }

    public function recoverPassword(Request $request)
    {
        $token       = $request->token;
        $inputEmail  = $request->email;
        $newPassword = $request->newPassword;
        $newPasswordConfirm = $request->newPasswordConfirm;

        try {
            \User\Service::recoverPassword($token, $inputEmail, $newPassword, $newPasswordConfirm);
        } catch (Exception $e){
            return response()->json([
                'message' => $e
            ], 400);
        }
    }

    public function forgotPassword(Request $request)
    {
        $this->validate($request, [
            'email'     => 'required|email'
        ]);

        $email = env('APP_ENV') == 'local' ? env('TEST_EMAIL') : $request->email;

        \User\Service::forgotPassword($email);
    }

    public function refreshToken(Request $request)
    {
        $token = $request->token;

        try{
            $decoded = \JWT::decode($token);
        
            return response()->json([
                'token' => $token,
                'message' => 'token is fine'
            ], 200);
        }catch (\Firebase\JWT\ExpiredException $e) {
            try{
                $newToken = \JWT::refreshToken($token);

                return response()->json([
                    'token' => $newToken
                ], 200);
            }catch (\Firebase\JWT\ExpiredException $e) {
                return response()->json([
                    'message' => 'Token too old'
                ], 400);
            }
        } catch (Exception $err){
            return response()->json([
                'message' => 'Bad token.'
            ], 500);
        }
    }

    public function authenticate(Request $request) {
        $login = $request->all();
        $result = \User\Service::login($login);
        $responseCode = key_exists('token', $result) ? 200 : 400;
        return response()->json($result, $responseCode);
    }

    public function checkToken(Request $request)
    {
        $token = $request->token;

        if($token == 'null') {
            return response()->json([
                'message' => 'Token not provided.'
            ], 401);
        }

        try {
            $credentials = \JWT::decode($token);
        } catch(\Firebase\JWT\ExpiredException $e) {
            // pass
        } catch (Exception $err){
            return response()->json([
                'message' => 'Bad token.'
            ], 500);
        }
    }
}