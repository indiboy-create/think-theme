<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme\Middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;
use Youvision\ThinkTheme\Theme as ThemeManager;

class Theme
{
    public function __construct(protected App $app)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $config = (array) $this->app->config->get('theme', []);

        if (!($config['enable'] ?? true)) {
            return $next($request);
        }

        $theme = $this->app->make(ThemeManager::class);
        $theme->resolve($request);

        $this->apply($theme);

        return $next($request);
    }

    /**
     * 将模板路径切换到当前主题目录。
     */
    protected function apply(ThemeManager $theme): void
    {
        $view   = $this->app->make('view');
        $engine = $view->engine();

        if (is_object($engine) && method_exists($engine, 'config')) {
            $engine->config(['view_path' => $theme->path()]);
        }
    }
}