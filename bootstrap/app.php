<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware global a ne pas oublier d'ajouter
        $middleware->alias([
            'verify.private.token' => \App\Http\Middleware\VerifyPrivateToken::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Nettoyage automatique des cartes de partage après 3 jours.
        $schedule->command('shares:cleanup --days=3')->dailyAt('03:20');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
