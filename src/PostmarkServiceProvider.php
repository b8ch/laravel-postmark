<?php

namespace Coconuts\Mail;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;
use DB;

class PostmarkServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'postmark');

        $this->publishes([
            __DIR__.'/../config/postmark.php' => config_path('postmark.php'),
        ], 'config');

        if ($this->app['config']['mail.driver'] !== 'postmark') {
            return;
        }

        $this->mergeConfigFrom(__DIR__.'/../config/postmark.php', 'postmark');
        
        dd(\Session::get('ss_active_calendar'));
       
        $userKey = DB::table('calendars')->where('user_uuid',  \Session::get('ss_active_calendar'))->first();

        if(!empty($userKey)):
          
            $pmKey = $userKey->postmark_key;
        else:
            $pmKey = config('postmark.secret', config('services.postmark.secret'));
        endif;

        $this->app['swift.transport']->extend('postmark', function () {
            return new PostmarkTransport(
                $this->guzzle(config('postmark.guzzle', [])),
                $pmKey
            );
        });
    }

    /**
     * Get a fresh Guzzle HTTP client instance.
     *
     * @param  array  $config
     * @return \GuzzleHttp\Client
     */
    protected function guzzle($config)
    {
        return new HttpClient($config);
    }
}
