<?php

namespace App\Providers;

use App\Mail\Transport\GraphApiTransport;
use App\Services\Graph\GraphAccessTokenProvider;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\Provider as MicrosoftProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GuzzleClient::class, fn () => new GuzzleClient([
            'timeout' => 15,
        ]));

        $this->app->singleton(GraphAccessTokenProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(GuzzleClient $client, GraphAccessTokenProvider $tokenProvider): void
    {
        Mail::extend('graph', fn () => new GraphApiTransport(
            $client,
            $tokenProvider,
            (string) config('graph.mail_from'),
        ));

        Mail::extend('graph_soporte', fn () => new GraphApiTransport(
            $client,
            $tokenProvider,
            (string) config('graph.mail_from_soporte'),
        ));

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', MicrosoftProvider::class);
        });
    }
}
