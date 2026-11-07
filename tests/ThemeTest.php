<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme\Tests;

use PHPUnit\Framework\TestCase;
use think\App;
use Youvision\ThinkTheme\Theme;

class ThemeTest extends TestCase
{
    protected const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected const IPAD = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'think-theme-' . uniqid('', true);

        foreach (['phone', 'tablet', 'computer', 'wechat', 'default'] as $dir) {
            mkdir($this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $dir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testDetectPhone(): void
    {
        $theme = $this->theme();

        $theme->detect(self::IPHONE);

        $this->assertSame('phone', $theme->device());
        $this->assertSame('phone', $theme->name());
        $this->assertTrue($theme->hasDeviceView());
        $this->assertSame(
            $this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'phone' . DIRECTORY_SEPARATOR,
            $theme->path()
        );
    }

    public function testWechatDetect(): void
    {
        $theme = $this->theme();

        $theme->detect(self::IPHONE . ' MicroMessenger/8.0.42');

        $this->assertSame('wechat', $theme->device());
        $this->assertSame('wechat', $theme->name());
        $this->assertTrue($theme->isWechat());
    }

    public function testForcedThemeOverridesUserAgent(): void
    {
        $theme = $this->theme();

        $theme->detect(self::IPHONE, 'computer');

        $this->assertSame('computer', $theme->device());
        $this->assertSame('computer', $theme->name());
    }

    public function testMissingDeviceViewFallsBackToDefault(): void
    {
        $this->removeDirectory($this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'tablet');

        $theme = $this->theme();
        $theme->detect(self::IPAD);

        $this->assertSame('tablet', $theme->name());
        $this->assertFalse($theme->hasDeviceView());
        $this->assertSame(
            $this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR,
            $theme->path()
        );
    }

    public function testCustomDevicesMapping(): void
    {
        $theme = $this->theme([
            'devices' => [
                'wechat'   => 'wechat',
                'tablet'   => 'pc',
                'phone'    => 'h5',
                'computer' => 'pc',
            ],
        ]);

        $theme->detect(self::IPHONE);

        $this->assertSame('h5', $theme->name());
        $this->assertFalse($theme->hasDeviceView());
        $this->assertSame(
            $this->root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR,
            $theme->path()
        );
    }

    public function testCustomThemePath(): void
    {
        $custom = $this->root . DIRECTORY_SEPARATOR . 'theme';
        mkdir($custom . DIRECTORY_SEPARATOR . 'phone', 0777, true);

        $theme = $this->theme(['path' => $custom]);
        $theme->detect(self::IPHONE);

        $this->assertSame($custom . DIRECTORY_SEPARATOR . 'phone' . DIRECTORY_SEPARATOR, $theme->path());
    }

    protected function theme(array $config = []): Theme
    {
        $app = new App($this->root);

        $app->config->set(['view_dir_name' => 'view'], 'view');

        return new Theme($app, $config);
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