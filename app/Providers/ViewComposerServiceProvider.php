<?php

namespace Amghost\Providers;

use Illuminate\Support\ServiceProvider;
use Amghost\Http\ViewComposers\ServerListComposer;
use Amghost\Http\ViewComposers\Server\ServerDataComposer;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot()
    {
        $this->app->make('view')->composer('server.*', ServerDataComposer::class);

        // Add data to make the sidebar work when viewing a server.
        $this->app->make('view')->composer(['server.*'], ServerListComposer::class);
    }
}
