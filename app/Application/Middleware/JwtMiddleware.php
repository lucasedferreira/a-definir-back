<?php

namespace Middleware;

use Closure;
use Exception;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        if(env('APP_ENV') == 'local' && !array_key_exists('authorization', $request->headers->all())){
            $request->user = \User\Repository::getRandomAdmin();
        }else{
            if(array_key_exists('authorization', $request->headers->all())){
                $token = substr($request->headers->all()['authorization'][0], 7);
            }else{
                return response()->json([
                    'error' => 'Token not provided.'
                ], 401);
            }

            if($token == 'null') {
                return response()->json([
                    'error' => 'Token not provided.'
                ], 401);
            }

            try {
                $credentials = \JWT::decode($token);
            } catch(\Firebase\JWT\ExpiredException $e) {
                return response()->json([
                    'error' => 'Provided token is expired.'
                ], 401);
            } catch (Exception $err){
                return response()->json([
                    'error' => 'Bad token.'
                ], 500);
            }

            $request->user = \User\Repository::getByID($credentials->sub);
        }

        return $next($request);
    }
}