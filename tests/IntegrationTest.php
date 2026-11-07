<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Request;
use Youvision\ThinkTheme\Middleware\Theme as ThemeMiddleware;
use Youvision\ThinkTheme\Theme;

class IntegrationTest extends TestCase
{
    protected const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected const IPAD = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected const WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    protected string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'think-theme-' . uniqid('', true);

        mkdir($this->root . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'temp', 0777, true);

        foreach (['computer', 'phone', 'tablet', 'wechat', 'default'] as $dir) {
            $path = $this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'index';
            mkdir($path, 0777, true);

            file_put_contents($path . DIRECTORY_SEPARATOR . 'index.html', strtoupper($dir) . ':{$title}');
            file_put_contents($path . DIRECTORY_SEPARATOR . 'index.php', '<?php echo \'' . strtoupper($dir) . ':\' . $title;');
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public static function userAgents(): array
    {
        return [
            'phone'    => [self::IPHONE, 'PHONE'],
            'tablet'   => [self::IPAD, 'TABLET'],
            'computer' => [self::WINDOWS, 'COMPUTER'],
            'wechat'   => [self::IPHONE . ' MicroMessenger/8.0.42', 'WECHAT'],
        ];
    }

    #[DataProvider('userAgents')]
    public function testThinkDriver(string $userAgent, string $expected): void
    {
        $this->assertSame($expected . ':demo', $this->render('Think', $userAgent));
    }

    #[DataProvider('userAgents')]
    public function testPhpDriver(string $userAgent, string $expected): void
    {
        $this->assertSame($expected . ':demo', $this->render('php', $userAgent));
    }

    public function testForcedThemeByQuery(): void
    {
        $this->assertSame('COMPUTER:demo', $this->render('Think', self::IPHONE, ['theme' => 'computer']));
    }

    public function testFallbackToDefaultTheme(): void
    {
        $this->removeDirectory($this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'tablet');

        $this->assertSame('DEFAULT:demo', $this->render('Think', self::IPAD));
    }

    protected function render(string $type, string $userAgent, array $get = []): string
    {
        $app = new App($this->root);

        $config = require __DIR__ . '/../config/theme.php';

        $app->config->set($config, 'theme');
        $app->config->set([
            'type'          => $type,
            'view_dir_name' => 'view',
            'view_suffix'   => $type === 'php' ? 'php' : 'html',
            'view_path'     => '',
        ], 'view');

        $request = (new Request())
            ->withHeader(['user-agent' => $userAgent])
            ->withServer(['request_method' => 'GET'])
            ->withGet($get)
            ->setController('index')
            ->setAction('index');

        $app->instance('request', $request);
        $app->instance(Theme::class, new Theme($app, $config));

        $middleware = new ThemeMiddleware($app);

        $response = $middleware->handle($request, function () use ($app) {
            return \think\Response::create($app->make('view')->fetch('index/index', ['title' => 'demo']));
        });

        return $response->getContent();
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}