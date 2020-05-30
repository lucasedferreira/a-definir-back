<?php

namespace Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        if(env('APP_ENV') == 'testing'){
            $outputFormatter = new \Symfony\Component\Console\Formatter\OutputFormatter(false, [
                'error' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', 'blue')
            ]);

            $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_VERBOSE, null, $outputFormatter);

            (new ConsoleApplication)->renderException($exception, $output);
            // exit(1);
        }else{
            return parent::render($request, $exception);
        }
        // dd($fe);
    }
}
