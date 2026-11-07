<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme;

use think\Service as BaseService;
use Youvision\ThinkTheme\Middleware\Theme as ThemeMiddleware;

class Service extends BaseService
{
    public function register(): void
    {
        $config = array_merge(
            require __DIR__ . '/../config/theme.php',
            (array) $this->app->config->get('theme', [])
        );

        $this->app->config->set($config, 'theme');
        $this->app->instance(Theme::class, new Theme($this->app, $config));
    }

    public function boot(): void
    {
        if ($this->app->config->get('theme.middleware', true)) {
            $this->app->middleware->add(ThemeMiddleware::class);
        }
    }
}