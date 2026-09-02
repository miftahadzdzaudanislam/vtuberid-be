<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );
        
        // Route tidak ditemukan
        $exceptions->render(function (
            NotFoundHttpException $e,
            $request
        ) {
            return response()->json([
                'message' => "Route {$request->method()}:{$request->getPathInfo()} not found",
                'error' => 'Not Found',
                'statusCode' => 404,
            ], 404);
        });

        // Route ada, tetapi HTTP method salah
        $exceptions->render(function (
            MethodNotAllowedHttpException $e,
            $request
        ) {
            return response()->json([
                'message' => "Route {$request->method()}:{$request->getPathInfo()} not found",
                'error' => 'Not Found',
                'statusCode' => 404,
            ], 404);
        });
    })->create();
