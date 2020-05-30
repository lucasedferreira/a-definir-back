<?php

namespace Middleware;

use Closure;
class Specific
{
    public function handle($request, Closure $next, $method, $domain, $param, $after = false)
    {
        $after = filter_var($after, FILTER_VALIDATE_BOOLEAN);
        if($after) $response = $next($request);

        $param = $this->parseProperty($request, $param);
        try {
            $result = call_user_func(array($domain . '\Service', $method), $param);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        if($after) return $response;
        return $next($request);
    }

    public function parseProperty($object, $property)
    {
        $val = $object;
        foreach(explode(',', $property) as $item) {
            $val = $val->$item;
        }

        return $val;
    }
}