<?php

use App\Models\Catalog;
use App\Models\JulyModel;
use App\Models\Node;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (! function_exists('auth')) {
    /**
     * Get the available auth instance.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    function auth($guard = null)
    {
        if (is_null($guard)) {
            $guard = 'admin';
        }

        return app(AuthFactory::class)->guard($guard);
    }
}

if (! function_exists('admin_path')) {
    /**
     * Get the path to the theme folder.
     *
     * @param  string  $path
     * @return string
     */
    function admin_path($path = '')
    {
        return public_path('themes/admin/'.ltrim($path, '\\/'));
    }
}

if (! function_exists('view_path')) {
    /**
     * Get the path to the theme folder.
     *
     * @param  string  $path
     * @return string
     */
    function view_path($path = '')
    {
        return public_path('themes/admin/template/'.ltrim($path, '\\/'));
    }
}

if (! function_exists('twig_path')) {
    /**
     * Get the path to the theme folder.
     *
     * @param  string  $path
     * @return string
     */
    function twig_path($path = '')
    {
        return public_path('themes/default/template/'.ltrim($path, '\\/'));
    }
}

if (! function_exists('base64_decode_array')) {
    /**
     * 递归解码 base64 编码过的数组
     *
     * @param array $data 待解码数组
     * @param array $except 指定未编码的键
     * @return array
     */
    function base64_decode_array(array $data, array $except = [])
    {
        foreach ($data as $key => $value) {
            if ($except[$key] ?? false) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = base64_decode_array($value);
            } elseif (is_string($value) && strlen($value)) {
                $data[$key] = base64_decode($value);
            }
        }
        return $data;
    }
}

if (! function_exists('real_env')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function real_env()
    {
        $pregDevUrl = '/^(127\.0\.0\.1|localhost)$|\.(test|dev)$/i';

        return preg_match($pregDevUrl, $_SERVER['HTTP_HOST'] ?? '') ? 'local' : 'production';
    }
}

if (! function_exists('under_route')) {
    function under_route($route, $path)
    {
        $route = short_route($route);
        return $path == $route || strpos($path, $route.'/') === 0;
    }
}

if (! function_exists('langcode')) {
    function langcode($type = null)
    {
        if (is_null($type)) {
            return [
                'content' => langcode('content'),
                'page' => langcode('page'),
            ];
        }

        switch ($type) {
            // 可用语言列表
            case 'available':
            case 'all':
            case 'list':
                $langs = [];
                $list = language_list();
                $langcodes = array_keys(config('jc.langcode.list'));
                foreach ($langcodes as $code) {
                    $langs[$code] = $list[$code] ?? $code;
                }
                return $langs;

            // 内容语言
            case 'content':
                return config('request.langcode.content') ?: config('jc.langcode.content');

            // 默认内容语言
            case 'content.default':
                return config('jc.langcode.content');

            // 站点页面语言
            case 'page':
                return config('request.langcode.current_page') ?: config('jc.langcode.page');

            // 当前请求语言
            case 'page.default':
                return config('jc.langcode.page');

            // 后台页面语言
            case 'admin_page':
                return config('jc.langcode.admin_page');

            // 请求语言数组
            case 'request':
                return config('request.langcode');

            // Laravel 设置的默认语言
            default:
                return config('fallback_lacale');
        }
    }
}

if (! function_exists('available_langcodes')) {
    /**
     * 获取可用语言列表
     *
     * @param string $type: 'page', 'content'
     * @return array
     */
    function available_langcodes($type = 'page')
    {
        if (!$type) {
            return [];
        }

        $langcodes = [];
        foreach (config('jc.langcode.permissions') as $key => $value) {
            if ($value[$type] ?? false) {
                $langcodes[] = $key;
            }
        }

        return $langcodes;
    }
}

if (! function_exists('available_languages')) {
    /**
     * 获取可用语言列表
     *
     * @param string $type: 'page', 'content'
     * @return array
     */
    function available_languages($type = 'page')
    {
        $list = language_list();

        $languages = [];
        foreach (config('jc.langcode.permissions') as $key => $value) {
            if (!$type || ($value[$type] ?? false)) {
                $languages[$key] = $list[$key] ?? $key;
            }
        }

        return $languages;
    }
}

if (! function_exists('langname')) {
    function langname($langcode)
    {
        $list = language_list();
        return $list[$langcode] ?? $langcode;
    }
}

if (! function_exists('cast_value')) {
    function cast_value($value, $cast)
    {
        switch ($cast) {
            case 'string':
                return $value.'';

            case 'integer':
            case 'int':
                return intval($value);

            case 'boolean':
            case 'bool':
                return boolval($value);

            case 'array':
                if (is_string($value)) {
                    $value = json_encode($value);
                }
                return (array) $value;

            default:
                return $value;
        }
    }
}

if (! function_exists('short_route')) {
    /**
     * 生成一个短 url （不带域名）
     *
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @return string
     */
    function short_route($name, $parameters = [])
    {
        if (is_array($name)) {
            $parameters = $name[1] ?? [];
            $name = $name[0] ?? null;
        }
        return route($name, $parameters, false);
    }
}

if (! function_exists('view_with_langcode')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function view_with_langcode($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        $data = array_merge([
            'content_langcode' => langcode('content'),
        ], $data);

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('twig')) {
    function twig($path = null, $debug = false)
    {
        $path = $path ?: 'default/template';
        $loader = new \Twig\Loader\FilesystemLoader($path, public_path('themes'));
        if ($debug) {
            $twig = new \Twig\Environment($loader, ['debug' => true]);
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        } else {
            $twig = new \Twig\Environment($loader);
        }

        $twig->addExtension(new \App\TwigExtensions\QueryInTwig());

        return $twig;
    }
}

if (! function_exists('last_modified')) {
    function last_modified($path)
    {
        if (is_file($path)) {
            return app('files')->lastModified($path);
        } elseif (is_dir($path)) {
            $lastModified = 0;
            foreach (app('files')->files($path) as $file) {
                $mTime = last_modified($file->getRealPath());
                if ($lastModified < $mTime) {
                    $lastModified = $mTime;
                }
            }
            return $lastModified;
        }
        return null;
    }
}

if (! function_exists('user_agent')) {
    function user_agent($ua = null)
    {
        $ua = $ua ?: $_SERVER['HTTP_USER_AGENT'];
        $uaGuess = $ua;

        $system = "unknown";
        if (preg_match("/(Windows Phone) \S+/", $ua, $match)
            || preg_match("/(Windows) NT (\d+\.\d+)/", $ua, $match)
            || preg_match("/(Android) (\d+\.\d+)/", $ua, $match)
            || preg_match("/(iPhone);/", $ua, $match)
            || preg_match("/(iPad);/", $ua, $match)
            || preg_match("/(iPod);/", $ua, $match)
            || preg_match("/(Mac OS)/", $ua, $match)
            || preg_match("/\W(Linux)\W/", $ua, $match))
        {
            $system = $match[1];

            if ($system === "Windows" && $match[2]) {
                switch($match[2]){
                    case "5.1":
                        $system .= " XP";
                        break;
                    case "6.0":
                        $system .= " Vista";
                        break;
                    case "6.1":
                        $system .= " 7";
                        break;
                    case "6.2":
                        $system .= " 8";
                        break;
                    case "6.3":
                    case "10.0":
                        $system .= " 10";
                        break;
                    default:
                        break;
                }
            } elseif ($system === "Android" && $match[2]) {
                $system += " "+$match[2];
            }
        }

        $browser = "unknown";
        if (preg_match("/(OPR)\/\S+/", $ua, $match)
            || preg_match("/(Opera)[\/| ]\S+/", $ua, $match)
            || (preg_match("/AppleWebKit\/\S+/", $ua)
            && (preg_match("/(Edge)\/\S+/", $ua, $match)
                || preg_match("/(Chrome)\/\S+/", $ua, $match)
                || preg_match("/(Safari)\/\S+/", $ua, $match)))
            || (preg_match("/rv:[^\)]+\) Gecko\/\d{8}/", $ua) && preg_match("/(Firefox)\/\S+/", $ua, $match)))
        {
            $browser = $match[1];
            if ($browser==="OPR") {
                $browser = "Opera";
            }
        } elseif (preg_match("/MSIE ([^;])+/", $ua, $match) || preg_match("/rv:([^)]+)/", $ua, $match)){
            $browser = "IE " + $match[1];
        }

        if ($system != 'unknown') {
            $uaGuess = "OS:{$system}, browser:{$browser}";
        }

        return $uaGuess;
    }
}

if (! function_exists('build_google_sitemap')) {
    /**
     * 生成谷歌站点地图（xml 文件）
     */
    function build_google_sitemap($langcode)
    {
        // 首页地址
        $home = rtrim(config('app.url'), '\\/');

        // pdf 信息
        $pdfList = [];

        // xml 文件
        $xml = '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        $xml .= ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
        $xml .= ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        $xml .= '<url><loc>'.$home.'/'.'</loc></url>';

        $urls = DB::table('node__url')
                ->where('langcode', $langcode)
                ->get()
                ->pluck('url_value', 'node_id')
                ->all();

        // 生成 xml 内容
        foreach (Catalog::default()->get_nodes() as $node) {
            $url = $urls[$node->id] ?? null;
            if (!$url || $url === '/404.html') continue;

            $html = $node->getHtml($langcode);
            if (is_null($html)) {
                continue;
            }
            $xml .= '<url><loc>'.$home.$url.'</loc>';

            foreach (extract_image_links($html) as $src) {
                $xml .= '<image:image><image:loc>'.$home.$src."</image:loc></image:image>";
            }
            $xml .= '</url>';

            $pdfList = array_merge($pdfList, extract_pdf_links($html));
        }

        foreach(array_unique($pdfList) as $pdf) {
            $xml .= '<url><loc>'.$home.$pdf.'</loc></url>';
        }
        $xml .= '</urlset>';

        return $xml;
    }
}

if (! function_exists('extract_image_links')) {
    /**
     * 从 $html 中提取图片链接
     *
     * @param string $html
     * @return array
     */
    function extract_image_links($html)
    {
        preg_match_all('/src="(\/[^"]*?\.(?:jpg|jpeg|gif|png|webp))"/', $html, $matches, PREG_PATTERN_ORDER);
        return array_unique($matches[1]);
    }
}

if (! function_exists('extract_pdf_links')) {
    /**
     * 从 $html 中提取 PDF 链接
     *
     * @param string $html
     * @return array
     */
    function extract_pdf_links($html)
    {
        preg_match_all('/href="(\/[^"]*?\.pdf)"/',$html, $matches, PREG_PATTERN_ORDER);
        return array_unique($matches[1]);
    }
}

if (! function_exists('extract_page_links')) {
    /**
     * 从 $html 中提取页面链接
     *
     * @param string $html
     * @return array
     */
    function extract_page_links($html)
    {
        preg_match_all('/href="(\/[^"]*?(\.html|\/))"/',$html, $matches, PREG_PATTERN_ORDER);
        return array_unique($matches[1]);
    }
}

if (! function_exists('str_diff')) {
    function str_diff($str1, $str2)
    {
        $str2 = str_replace(str_split($str1), '', $str2);
        return strlen($str2);
    }
}

if (! function_exists('language_list')) {
    function language_list()
    {
        $langcode = langcode('admin_page');

        $list = config('language_list.'.$langcode, []);
        if ($list) {
            return $list;
        }

        $file = base_path('language/'.$langcode.'.php');
        if (is_file($file)) {
            $list = require $file;
            app('config')->set('language_list.'.$langcode, $list);
        }

        return $list;
    }
}

if (! function_exists('get_file_list')) {
    /**
     * 根据白名单获取文件列表
     *
     * @param array $list 白名单
     * @param string|null $root 起始目录
     * @return array 文件名列表，格式：[文件路径 => 新的文件路径|'']
     */
    function get_file_list(array $list, $root = null)
    {
        $files = [];
        $root = rtrim($root ?: public_path(), '/').'/';
        foreach ($list as $original => $target) {
            if ('/*' === substr($original, -2)) {
                $original = substr($original, 0, strlen($original)-1);
                if ($target) {
                    $target = rtrim($target, '/').'/';
                }
                $_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.$original));
                foreach ($_files as $name => $file) {
                    $filePath = str_replace('\\', '/', $file->getRealPath());
                    $relativePath = substr($filePath, strlen($root));
                    if ($file->isDir()) {
                        $relativePath .= '/';
                    }
                    $files[$relativePath] = $target ? $target.substr($filePath, strlen($root.$original)) : '';
                }
            } else {
                $files[$original] = $target;
            }
        }

        return $files;
    }
}

if (! function_exists('package_files')) {
    /**
     * 打包文件
     *
     * @param string $pkg 文件名
     * @param array $files 文件名列表，格式：[文件路径 => 新的文件路径|'']
     * @return file
     */
    function package_files($pkg, array $files)
    {
        $zip = new \ZipArchive();
        $zip->open($pkg, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $path = rtrim(public_path(), '/').'/';
        foreach ($files as $file => $newName) {
            $filePath = $path.$file;
            if (substr($file, -1) === '/' || is_dir($filePath)) {
                $zip->addEmptyDir($file);
            } elseif (is_file($filePath)) {
                $zip->addFile($filePath, $newName ?: $file);
            }
        }
        $zip->close();

        return true;
    }
}

if (! function_exists('format_arguments')) {
    /**
     * 格式化传入参数
     *
     * @param array $args 文件名
     * @return array
     */
    function format_arguments(array $args)
    {
        // 如果只有一个参数，而且是一个数组，则假设该数组才是用户真正想要传入的参数
        if (count($args) === 1 && is_array($args[0])) {
            $args = $args[0];
        }

        // 过滤掉 null 和空字符串
        $args = array_filter($args, function($item) {
            return !(is_null($item) || $item === '');
        });

        return array_values($args);
    }
}
