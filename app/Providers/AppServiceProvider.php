<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use App\Services\EnhancedAIService;
use App\Services\SmartAIService;
use App\Services\AssistantActionPlanner;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (config('kwdc.demo')) {
            config(['mail.default' => 'array', 'sms.default' => 'log', 'services.ai.provider' => 'free']);
            config([
                'logging.channels.stderr.formatter' => \Monolog\Formatter\LineFormatter::class,
                'logging.channels.stderr.formatter_with' => ['format' => '[%datetime%] %level_name%: %message%'.PHP_EOL],
            ]);
        }
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        // Set default string length for MySQL
        Schema::defaultStringLength(191);
    }

    public function register()
    {
        // Register AI services with dependency injection
        $this->app->singleton(EnhancedAIService::class, function ($app) {
            return new EnhancedAIService();
        });

        $this->app->singleton(SmartAIService::class, function ($app) {
            return new SmartAIService(
                $app->make(EnhancedAIService::class),
                $app->make(AssistantActionPlanner::class)
            );
        });
    }
}
