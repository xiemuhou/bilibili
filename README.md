# Bilibili 追番与追剧页面

为 WordPress Argon 主题提供 B 站追番和追剧页面模板，数据通过站点根目录下的 PHP 接口从 B 站获取。

教程与演示：

- [安装教程](https://blog.xmhweb.cn/551/)
- [追番页面](https://blog.xmhweb.cn/anime/)

## 文件说明

```text
page-anime.php              WordPress 追番页面模板
page-movie.php              WordPress 追剧页面模板
json/GetAnimeData.php       追番数据接口
json/GetAnimeTotal.php      追番数量轻量接口
json/GetMovieData.php       追剧数据接口
json/classAnime.php         追番数据获取与处理
json/classMovie.php         追剧数据获取与处理
json/bilibiliAcconut.php    B 站 UID 与 Cookie 配置
```

## 部署方法

1. 将 `json` 目录上传到 WordPress 站点根目录。
2. 将 `page-anime.php` 和 `page-movie.php` 上传到当前使用的 Argon 主题目录。
3. 在 `json/bilibiliAcconut.php` 中配置 B 站 UID 和有效 Cookie。
4. 在 WordPress 后台创建页面并选择对应的页面模板。

请勿将包含真实 Cookie 的 `bilibiliAcconut.php` 提交到公开仓库。`SESSDATA` 等字段属于账号登录凭据。

## 追番数量统计

追番总数优先来自轻量接口：

```text
/json/GetAnimeTotal.php
```

接口返回示例：

```json
{
  "total": 104
}
```

旧版模板在 PHP 生成页面时使用 `file_get_contents()` 请求自身接口。该请求可能因 PHP-FPM 回环等待、CDN、缓存或网络超时失败，导致标题显示为“当前已追部”。

当前模板改为先输出数量占位符：

```html
当前已追<span id="anime-total">--</span>部
```

页面加载后会单独请求轻量统计接口，只读取 B 站第一页的 `total`，无需等待完整追番列表抓取完成。完整列表接口成功后也会再次更新数量，作为后备：

```javascript
$("#anime-total").text(data.total || 0);
```

这样既不依赖服务器请求自身站点，也不需要等待全部番剧数据处理完成。

## 验证与排查

先直接访问接口，确认返回 JSON 且 `total` 大于 0：

```text
https://你的域名/json/GetAnimeTotal.php
```

如果接口有数量但页面仍显示 `--`：

1. 查看网页源代码，确认能搜索到 `anime-total`。
2. 确认 `page-anime.php` 已上传到当前启用的主题目录。
3. 清理 WordPress、服务器和 CDN 页面缓存。
4. 在浏览器开发者工具中检查 `/json/GetAnimeTotal.php` 请求是否成功。

如果网页源代码中没有 `anime-total`，说明线上仍在使用旧版模板或旧缓存。

## 封面一直显示加载占位图

Argon 主题已经内置 jQuery LazyLoad。页面模板不能再次加载 `lazyload@2.0.0-rc.2`，否则两个版本会覆盖同一个 `$.fn.lazyload`，导致封面停留在 `loading.svg`。

当前追番和追剧模板使用独立的 `IntersectionObserver`，不会覆盖 Argon 的 LazyLoad。图片进入可视区域前继续显示项目自带的加载图：

```html
<img
  referrerpolicy="no-referrer"
  src="/json/images/loading.svg"
  data-cover-src="封面地址"
  alt=""
>
```

图片接近可视区域后，脚本把 `data-cover-src` 中的真实封面地址写入 `src`；加载失败时保留 `loading.svg`。

如果更新后仍显示占位图，请确认网页源代码中不再包含：

```text
lazyload@2.0.0-rc.2
```

随后清理 WordPress、CDN 和浏览器缓存，并确保“番剧”菜单项添加了 `no-pjax` CSS 类。
