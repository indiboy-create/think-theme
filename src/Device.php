<?php
declare(strict_types=1);

namespace Youvision\ThinkTheme;

use Detection\MobileDetect;
use Throwable;

/**
 * 设备类型检测。
 *
 * 返回 wechat、tablet、phone、computer 之一，微信优先。
 */
class Device
{
    public const WECHAT   = 'wechat';
    public const TABLET   = 'tablet';
    public const PHONE    = 'phone';
    public const COMPUTER = 'computer';

    protected array $config;

    protected ?MobileDetect $detect = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 检测设备类型。
     */
    public function detect(?string $userAgent): string
    {
        $userAgent = trim((string) $userAgent);

        if ($userAgent === '') {
            return self::COMPUTER;
        }

        if ($this->isWechat($userAgent)) {
            return self::WECHAT;
        }

        try {
            $detect = $this->detector();
            $detect->setUserAgent($userAgent);

            if ($detect->isTablet()) {
                return self::TABLET;
            }

            if ($detect->isMobile()) {
                return self::PHONE;
            }
        } catch (Throwable) {
            return self::COMPUTER;
        }

        return self::COMPUTER;
    }

    /**
     * 判断是否在微信内浏览（含微信内置浏览器、微信客户端、企业微信）。
     */
    public function isWechat(string $userAgent): bool
    {
        $keywords = (array) ($this->config['wechat_keywords'] ?? ['MicroMessenger']);

        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);

            if ($keyword !== '' && stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function detector(): MobileDetect
    {
        return $this->detect ??= new MobileDetect();
    }
}