# think-theme

ThinkPHP 8 设备主题切换组件。基于 [Mobile-Detect](https://github.com/serbanghita/Mobile-Detect) 识别访问设备，自动将视图定位到 `computer`、`phone`、`tablet`、`wechat` 等不同的模板目录。

## 特性

- 自动识别 `computer`、`phone`、`tablet` 设备类型
- 微信环境优先判定，输出 `wechat`（含微信内置浏览器、微信 Windows 客户端、企业微信）
- 设备类型与模板目录名可自由映射
- 设备模板目录不存在时自动回退到默认主题
- 支持通过 URL 参数、Cookie、请求头强制指定主题，便于预览和调试
- 单应用、多应用模式均可使用
- 配置项完整中文注释

## 环境要求

- PHP >= 8.1
- ThinkPHP >= 8.0
- `mobiledetect/mobiledetectlib` ^3.74 或 ^4.0（自动安装）

## 安装

```bash
composer require youvision/think-theme
```

服务与配置会自动注册。如需修改配置，可发布后编辑项目根目录的 `config/theme.php`：

```bash
php think vendor:publish
```

如果当前 ThinkPHP 版本没有 `vendor:publish` 命令，手动复制包内的 `config/theme.php` 到项目 `config/theme.php` 即可。

## 模板目录

默认将模板放在视图目录下的设备子目录：

```text
app/view/
├── computer/
│   └── index/
│       └── index.html
├── phone/
│   └── index/
│       └── index.html
├── tablet/
│   └── index/
│       └── index.html
├── wechat/
│   └── index/
│       └── index.html
└── default/
    └── index/
        └── index.html
```

多应用模式下对应 `app/{应用名}/view/{设备}/...`。

控制器无需任何改动，继续使用：

```php
return view();
// 或
return view('index/index');
```

组件会自动把 `view_path` 切换到当前设备对应的目录。

## 配置说明

配置文件：`config/theme.php`

| 配置项 | 默认值 | 说明 |
| --- | --- | --- |
| `enable` | `true` | 是否启用设备主题切换 |
| `path` | `''` | 主题根目录，留空自动识别视图目录；支持绝对路径或相对项目根目录的路径 |
| `default` | `'default'` | 默认主题目录，设备目录不存在时回退 |
| `devices` | 见下方 | 设备类型到主题目录名的映射 |
| `wechat_keywords` | `['MicroMessenger', 'WindowsWechat', 'wxwork']` | 微信环境判定关键字，不区分大小写 |
| `query` | `'theme'` | 强制主题的 URL 参数名，留空关闭 |
| `cookie` | `'think_theme'` | 强制主题的 Cookie 名，留空关闭 |
| `header` | `'X-Think-Theme'` | 强制主题的请求头名，留空关闭 |
| `allow` | `[]` | 强制主题白名单，为空不限制 |
| `middleware` | `true` | 是否自动注册全局中间件 |

默认设备映射：

```php
'devices' => [
    'wechat'   => 'wechat',
    'tablet'   => 'tablet',
    'phone'    => 'phone',
    'computer' => 'computer',
],
```

如果希望 `phone` 和 `tablet` 共用一套模板，可改为：

```php
'devices' => [
    'wechat'   => 'wechat',
    'tablet'   => 'mobile',
    'phone'    => 'mobile',
    'computer' => 'computer',
],
```

## 强制主题

按优先级从高到低：URL 参数、请求头、Cookie。

```text
/index/index?theme=phone
```

生产环境建议配置白名单：

```php
'allow' => ['computer', 'phone', 'tablet', 'wechat'],
```

## 获取当前主题

在控制器、服务或模板中使用 `theme()`：

```php
theme()->device();      // wechat、tablet、phone、computer
theme()->name();        // 主题目录名
theme()->path();        // 当前模板目录绝对路径
theme()->isWechat();    // 是否微信环境
theme()->hasDeviceView(); // 当前设备模板目录是否存在
```

模板中：

```html
{if condition="theme()->isWechat()"}
<p>微信内浏览</p>
{/if}
```

## 手动注册中间件

将 `middleware` 配置设为 `false` 后，在 `app/middleware.php` 中手动注册：

```php
return [
    \Youvision\ThinkTheme\Middleware\Theme::class,
];
```

## 测试

```bash
composer install
vendor/bin/phpunit
```

## 许可证

[MIT](LICENSE)