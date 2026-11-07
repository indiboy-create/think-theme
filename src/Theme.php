<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme;

use think\App;
use think\Request;

/**
 * 当前请求的主题。
 */
class Theme
{
    protected static ?array $defaults = null;

    protected array $config;

    protected ?Device $detector = null;

    protected string $device = Device::COMPUTER;

    protected string $name = '';

    public function __construct(protected App $app, array $config = [])
    {
        $this->config = array_merge(static::defaults(), $config);
    }

    /**
     * 根据请求解析主题。
     */
    public function resolve(Request $request): static
    {
        return $this->detect($request->header('user-agent'), $this->forced($request));
    }

    /**
     * 解析设备类型与主题目录名。
     *
     * @param string|null $userAgent 用户代理
     * @param string|null $forced    强制指定的主题目录名
     */
    public function detect(?string $userAgent, ?string $forced = null): static
    {
        if ($forced !== null && $forced !== '') {
            $this->device = $forced;
            $this->name   = $forced;

            return $this;
        }

        $this->device = $this->detector()->detect($userAgent);
        $this->name   = (string) ($this->config['devices'][$this->device] ?? $this->device);

        return $this;
    }

    /**
     * 设备类型：wechat、tablet、phone、computer。
     */
    public function device(): string
    {
        return $this->device;
    }

    /**
     * 主题目录名。
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * 是否处于微信环境。
     */
    public function isWechat(): bool
    {
        return $this->device === Device::WECHAT;
    }

    /**
     * 当前主题的模板目录（带末尾分隔符）。
     */
    public function path(): string
    {
        $base = $this->basePath();

        if ($this->name === '') {
            return $base;
        }

        $candidates = [$this->name];

        $default = (string) ($this->config['default'] ?? '');
        if ($default !== '' && $default !== $this->name) {
            $candidates[] = $default;
        }

        foreach ($candidates as $candidate) {
            $path = $base . $candidate . DIRECTORY_SEPARATOR;

            if (is_dir($path)) {
                return $path;
            }
        }

        return $base;
    }

    /**
     * 当前设备的主题目录是否存在（不含默认主题回退）。
     */
    public function hasDeviceView(): bool
    {
        return $this->name !== '' && is_dir($this->basePath() . $this->name);
    }

    /**
     * 主题根目录（带末尾分隔符）。
     */
    protected function basePath(): string
    {
        $path = (string) ($this->config['path'] ?? '');

        if ($path !== '') {
            return rtrim($this->resolvePath($path), '/\\') . DIRECTORY_SEPARATOR;
        }

        $view = (string) ($this->config['view_dir_name'] ?? '');

        if ($view === '') {
            $view = (string) $this->app->config->get('view.view_dir_name', 'view');
        }

        $view = trim($view, '/\\') ?: 'view';

        $appPath = $this->app->getAppPath();

        if (is_dir($appPath . $view)) {
            return $appPath . $view . DIRECTORY_SEPARATOR;
        }

        $base = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR;
        $app  = $this->app->http->getName();

        if ($app) {
            $base .= str_replace('.', DIRECTORY_SEPARATOR, $app) . DIRECTORY_SEPARATOR;
        }

        return $base;
    }

    /**
     * 获取请求中强制指定的主题目录名。
     */
    protected function forced(Request $request): ?string
    {
        $values = [];

        $query = (string) ($this->config['query'] ?? '');
        if ($query !== '') {
            $values[] = $request->param($query);
        }

        $header = (string) ($this->config['header'] ?? '');
        if ($header !== '') {
            $values[] = $request->header($header);
        }

        $cookie = (string) ($this->config['cookie'] ?? '');
        if ($cookie !== '') {
            $values[] = $request->cookie($cookie);
        }

        $allow = array_map('strval', (array) ($this->config['allow'] ?? []));

        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
                continue;
            }

            if ($allow !== [] && !in_array($value, $allow, true)) {
                continue;
            }

            return $value;
        }

        return null;
    }

    protected function resolvePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return $this->app->getRootPath();
        }

        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $path)) {
            return $path;
        }

        return rtrim($this->app->getRootPath(), '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }

    protected function detector(): Device
    {
        return $this->detector ??= new Device($this->config);
    }

    protected static function defaults(): array
    {
        return static::$defaults ??= require __DIR__ . '/../config/theme.php';
    }
}