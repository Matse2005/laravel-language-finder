<?php

namespace Matsevh\LanguageFinder;

use Illuminate\Support\ServiceProvider;
use Matsevh\LanguageFinder\Console\Commands\FindLanguageStrings;

class LanguageFinderServiceProvider extends ServiceProvider
{
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__ . '/../config/language-finder.php' => config_path('language-finder.php'),
      ], 'config');

      $this->commands([
        FindLanguageStrings::class,
      ]);
    }
  }

  public function register()
  {
    $this->mergeConfigFrom(__DIR__ . '/../config/language-finder.php', 'language-finder');
  }
}
