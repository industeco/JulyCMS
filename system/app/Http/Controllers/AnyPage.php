<?php

namespace App\Http\Controllers;

use App\EntityValue\EntityPathAlias;
use App\Entity\TranslatableEntityBase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnyPage extends Controller
{
    /**
     * 返回任意页面
     *
     * @param  \Illuminate\Http\Request $request
     * @return
     */
    public function __invoke(Request $request)
    {
        // 过滤未定义的管理类请求
        // 管理类请求应该通过明确定义的路由访问，不应该来到这里
        if (request('is_management')) {
            abort(404);
        }

        // 检测 301/302 跳转
        if ($redirection = $this->getRedirectiton($request)) {
            return Redirect::to($redirection['to'], $redirection['code'] ?? 302);
        }

        $url = $request->decodedPath();

        // 检测是否直接返回错误码
        if ($code = $this->shouldAbort($url)) {
            abort($code);
        }

        // 尝试获取 html
        if ($html = $this->getHtml($url)) {
            return $html;
        }

        abort(404);
    }

    /**
     * 检测是否需要跳转
     *
     * @param  \Illuminate\Http\Request $request
     * @return array|null
     */
    protected function getRedirectiton(Request $request)
    {
        // 自定义 301/302 跳转
        $redirection = config('site.redirections', [])[$request->getRequestUri()] ?? null;
        if ($redirection && $redirection['to']) {
            $host = $request->getSchemeAndHttpHost();
            if (! Str::startsWith($redirection['to'], $host)) {
                $redirection['to'] = $host.'/'.ltrim($redirection['to'], '\\/');
            }

            return $redirection;
        }

        // 大写字母跳转
        $path = $request->getPathInfo();
        if (preg_match('/[A-Z]/', $path)) {
            if (null !== $qs = $request->getQueryString()) {
                $qs = '?'.$qs;
            }

            return ['to' => strtolower($path).$qs];
        }

        return null;
    }

    /**
     * 检测是否直接返回错误码
     *
     * @param  string $url
     * @return int|null $code 错误码
     */
    protected function shouldAbort(string $url)
    {
        $basename = basename($url);
        if ($basename === 'sitemap.xml') {
            return 404;
        }

        // 如 404.html / 403.html 等
        if (preg_match('/^(4\d{2})(?:\.html?)?/', $basename, $matches)) {
            return (int) $matches[1];
        }

        // 如果 url 中未包含语言，则不进行语言检测
        if ($langcode = langcode('request')) {
            // 检测语言：
            //  - 如果多语言开关未打开，则不允许按语言访问
            //  - 否则，检查所请求语言是否允许访问
            if (!config('lang.multiple') || !lang($langcode)->isAccessible()) {
                return 404;
            }
        }

        return null;
    }

    /**
     * 尝试获取 html
     *
     * @param  string $url
     * @return string|null
     */
    protected function getHtml(string $url)
    {
        $langcode = langcode('request') ?? langcode('frontend');

        if ($entity = $this->getEntity($url, $langcode)) {
            if ($entity instanceof TranslatableEntityBase) {
                $entity->translateTo($langcode);
            }

            return $entity->fetchHtml();
        }

        return null;
    }

    /**
     * 获取网址对应的实体
     *
     * @param  string $url
     * @param  string $langcode
     * @return \App\Entity\EntityBase|null
     */
    protected function getEntity(string $url, string $langcode)
    {
        if ($url === '/') {
            $url .= 'index.html';
        }

        // 查找 Entity
        if ($entity = EntityPathAlias::findEntity($url, $langcode)) {
            return $entity;
        }

        // 如果未找到实体，尝试补全 url 后再查找
        if (! preg_match('/\.html?$/', $url)) {
            $url .= '/index.html';
            if ($entity = EntityPathAlias::findEntity($url)) {
                return $entity;
            }
        }

        return null;
    }
}
