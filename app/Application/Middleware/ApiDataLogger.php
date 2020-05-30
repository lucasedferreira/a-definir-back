<?php
namespace Middleware;

// use Illuminate\Support\Facades\File;

use Closure;
class ApiDataLogger
{
    private $startTime;
    
    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ( env('API_DATALOGGER', true) ) {
            $shopLog = '';

            if(!is_null($request->shopID)){
                $shopLog = ' shopID: ' . $request->shopID; 
            }

            \Log::info($request->method() . $shopLog . ' -- Request: ' . json_encode($request->all()) . ' -- Response: ' . $response->content());
        }

        return $response;
    }
}