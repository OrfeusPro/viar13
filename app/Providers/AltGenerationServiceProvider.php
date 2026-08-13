<?php

namespace App\Providers;

use App\Http\View\Composers\AltSuggestionsMenuBadgeComposer;
use App\Observers\AutoAltObserver;
use App\Services\AltGeneration\AltAttributeResolver;
use App\Services\AltGeneration\AltGenerator;
use App\Services\AltGeneration\LocaleResolver;
use App\Services\AltGeneration\OpenAiVisionClient;
use App\Services\AltGeneration\PageContextResolver;
use App\Services\AltGeneration\SkippedAltGenerationLogger;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Registers the Alt Generation service layer.
 */
class AltGenerationServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(OpenAiVisionClient::class, function ($app) {
            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app->make(ConfigRepository::class);
            $timeout = (int) $config->get('alt_generation.openai.timeout', 60);

            return new OpenAiVisionClient(
                new Client(['timeout' => $timeout]),
                (string) $config->get('alt_generation.openai.api_key', ''),
                $timeout,
                $config,
                $app->make(LoggerInterface::class)
            );
        });

        $this->app->singleton(AltGenerator::class, function ($app) {
            return new AltGenerator(
                $app->make(PageContextResolver::class),
                $app->make(OpenAiVisionClient::class),
                $app->make(ConfigRepository::class),
                $app->make(CacheRepository::class),
                $app->make(LoggerInterface::class)
            );
        });

        $this->app->singleton(AltAttributeResolver::class, function ($app) {
            return new AltAttributeResolver(
                $app->make(ConfigRepository::class),
                $app->make(LocaleResolver::class)
            );
        });

        $this->app->singleton(LocaleResolver::class);
        $this->app->singleton(SkippedAltGenerationLogger::class);
    }

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Blade::directive('altAttrs', function ($expression) {
            return "<?php echo app(\\App\\Services\\AltGeneration\\AltAttributeResolver::class)->attributesFor({$expression}); ?>";
        });

        View::composer('voyager::dashboard.sidebar', AltSuggestionsMenuBadgeComposer::class);

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $this->app->make(ConfigRepository::class);
        if (!(bool) $config->get('alt_generation.auto_observer', false)) {
            return;
        }

        $targets = (array) $config->get('alt_generation.targets', []);

        foreach ($targets as $modelClass => $target) {
            if (!is_string($modelClass) || !class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            if (is_array($target) && isset($target['enabled']) && $target['enabled'] === false) {
                continue;
            }

            $modelClass::observe(AutoAltObserver::class);
        }
    }
}
