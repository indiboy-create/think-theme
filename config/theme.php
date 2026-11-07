<?php
// +----------------------------------------------------------------------
// | think-theme 配置
// +----------------------------------------------------------------------

return [
    // 是否启用设备主题切换。关闭后保持 ThinkPHP 原有的视图目录不变。
    'enable' => true,

    // 主题根目录。
    // 留空时自动识别视图目录：
    //   单应用：{项目根目录}/app/view
    //   多应用：{项目根目录}/app/{应用名}/view（该目录存在时）
    // 也可以填写绝对路径，或相对于项目根目录的路径，例如 'app/view'。
    'path' => '',

    // 默认主题目录。
    // 当前设备对应的主题目录不存在时回退到该目录；留空则直接使用原始视图目录。
    'default' => 'default',

    // 设备类型 => 主题目录名。
    // 设备类型由 Mobile-Detect 识别，微信环境由 User-Agent 关键字优先判定。
    'devices' => [
        'wechat'   => 'wechat',
        'tablet'   => 'tablet',
        'phone'    => 'phone',
        'computer' => 'computer',
    ],

    // 微信环境判定关键字（不区分大小写），命中任意一个即视为微信内浏览。
    // MicroMessenger：微信内置浏览器
    // WindowsWechat：微信 Windows 客户端
    // wxwork：企业微信
    'wechat_keywords' => [
        'MicroMessenger',
        'WindowsWechat',
        'wxwork',
    ],

    // 强制主题的请求参数名，例如 /index/index?theme=phone。留空关闭。
    'query' => 'theme',

    // 强制主题的 Cookie 名。留空关闭。
    'cookie' => 'think_theme',

    // 强制主题的请求头名。留空关闭。
    'header' => 'X-Think-Theme',

    // 强制主题白名单。为空表示不限制；建议生产环境填写允许的主题目录名。
    'allow' => [],

    // 是否自动注册全局中间件。
    // 设为 false 时需手动将 \Youvision\ThinkTheme\Middleware\Theme
    // 注册到 app/middleware.php。
    'middleware' => true,
];