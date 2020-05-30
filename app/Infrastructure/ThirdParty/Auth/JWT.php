<?php
use Firebase\JWT\JWT as FirebaseJWT;
class JWT
{
    public static function generateUserJWT($userID)
    {
        $payload = [
            'iss' => "lumen-jwt",
            'sub' => $userID,
            'iat' => time(),
            'exp' => time() + 60*60
        ];
        
        return FirebaseJWT::encode($payload, env('JWT_SECRET'));
    }

    public static function decode($token)
    {
        return FirebaseJWT::decode($token, env('JWT_SECRET'), ['HS256']);
    }

    public static function refreshToken($token)
    {
        $decoded = self::extractTokenInfo($token);

        if(($decoded['iat'] >= strtotime('-2 days'))){
            return \JWT::generateUserJWT($decoded['sub']);
        } else {
            throw new \Firebase\JWT\ExpiredException;
        }
    }

    public static function extractTokenInfo($token)
    {
        list($header, $payload, $signature) = explode(".", $token);
        $decoded = json_decode(base64_decode($payload), true);

        return $decoded;
    }
}