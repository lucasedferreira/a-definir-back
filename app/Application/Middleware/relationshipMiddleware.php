<?php
namespace Middleware;

use Closure;
class Relationship
{
    public function handle($request, Closure $next, $method, $domain, $father, $children)
    {   
        $father   = $this->parseProperty($request, $father);
        $children = $this->parseProperty($request, $children);

        try {
            $result = call_user_func(array($domain . '\Validations', $method), $father, $children);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return $next($request);
    }

    public function parseProperty($object, $property)
    {
        $val = $object;
        foreach(explode('->', $property) as $item) {
            $val = $val->$item;
        }

        return $val;
    }
}