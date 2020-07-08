<?php
    if(!function_exists('random_string_generator')){
        function random_string_generator(){
            return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', 10)), 0, 20);
        }
    }

    if(!function_exists('_dd')){
        function _dd(...$args)
        {
            $content = '<span>';
            ob_start();
            dump(...$args);
            $content .= ob_get_contents();
            ob_end_clean();
            $content .= '</span>';
            $content = str_replace(['<div', '</div>'], ['<span', '</span>'], $content);
            response()->make($content, 500, ['Content-Type' => 'text/html'])->send();
            die(1);
        }
    }