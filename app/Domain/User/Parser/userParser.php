<?php
    namespace User;

    use \BaseParser;
    class Parser extends BaseParser
    {
        public static function parseRegister($user)
        {
            if(key_exists('provider', $user)) {
                if($user['provider'] === 'GOOGLE') {
                    $user['google_id'] = $user['id'];
                }
            }

            if(empty($user['role'])) {
                $user['role'] = 'client';
            }

            return parent::parse([
                'rememberToken' => 'remember_token'
            ], $user);
        }

        public static function parseCreate($user)
        {
            if(empty($user['password'])) {
                $user['password'] = app('hash')->make('12345');
            }
    
            if(empty($user['role'])) {
                $user['role'] = 'admin';
            }

            return $user;
        }
    }