<?php
declare(strict_types=1);

use Youvision\ThinkTheme\Theme;

if (!function_exists('theme')) {
    /**
     * 获取当前主题实例。
     */
    function theme(): Theme
    {
        return \think\Container::getInstance()->make(Theme::class);
    }
}