<?php
namespace GFL\ReadDocx;
use Illuminate\Support\ServiceProvider;
class ReadDocxServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }
    public function register(){
        $this->app->bind('ReadDocx', function(){
            return $this->app->make('GFL\ReadDocx\ReadDocx');
        });
    }
}