<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme\Tests;

use PHPUnit\Framework\TestCase;
use Youvision\ThinkTheme\Device;

class DeviceTest extends TestCase
{
    protected const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected const IPAD = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected const ANDROID = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

    protected const WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function testWechatTakesPriorityOverPhone(): void
    {
        $ua = self::IPHONE . ' MicroMessenger/8.0.42(0x18002a2b) NetType/WIFI Language/zh_CN';

        $this->assertSame(Device::WECHAT, $this->device()->detect($ua));
    }

    public function testWindowsWechat(): void
    {
        $ua = $this->desktopUa() . ' MicroMessenger/7.0.20 WindowsWechat';

        $this->assertSame(Device::WECHAT, $this->device()->detect($ua));
    }

    public function testWechatWork(): void
    {
        $ua = self::ANDROID . ' wxwork/4.1.0 MicroMessenger/7.0.1';

        $this->assertSame(Device::WECHAT, $this->device()->detect($ua));
    }

    public function testPhone(): void
    {
        $this->assertSame(Device::PHONE, $this->device()->detect(self::IPHONE));
        $this->assertSame(Device::PHONE, $this->device()->detect(self::ANDROID));
    }

    public function testTablet(): void
    {
        $this->assertSame(Device::TABLET, $this->device()->detect(self::IPAD));
    }

    public function testComputer(): void
    {
        $this->assertSame(Device::COMPUTER, $this->device()->detect(self::WINDOWS));
    }

    public function testEmptyUserAgent(): void
    {
        $this->assertSame(Device::COMPUTER, $this->device()->detect(null));
        $this->assertSame(Device::COMPUTER, $this->device()->detect(''));
    }

    public function testCustomWechatKeywords(): void
    {
        $device = new Device(['wechat_keywords' => ['MyWechat']]);

        $this->assertSame(Device::WECHAT, $device->detect('MyWechat/1.0'));
        $this->assertSame(Device::PHONE, $device->detect(self::IPHONE));
    }

    protected function device(): Device
    {
        return new Device([
            'wechat_keywords' => ['MicroMessenger', 'WindowsWechat', 'wxwork'],
        ]);
    }

    protected function desktopUa(): string
    {
        return self::WINDOWS;
    }
}