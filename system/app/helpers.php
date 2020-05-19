<?php

use App\Contracts\HasModelConfig;
use App\Models\Catalog;
use App\Models\Config;
use App\Models\JulyModel;
use App\Models\Node;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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

// if (! function_exists('config')) {
//     /**
//      * Get / set the specified configuration value.
//      *
//      * If an array is passed as the key, we will assume you want to set an array of values.
//      *
//      * @param  array|string|null  $key
//      * @param  mixed  $default
//      * @return mixed|\Illuminate\Config\Repository
//      */
//     function config($key = null, $default = null)
//     {
//         if (is_null($key)) {
//             return app('config');
//         }

//         if (is_array($key)) {
//             return app('config')->set($key);
//         }

//         if (is_string($key) && stripos($key, 'db.') === 0) {
//             if (!is_null($value = Config::get(substr($key, 3)))) {
//                 return $value;
//             }
//         }

//         return app('config')->get($key, $default);
//     }
// }

if (! function_exists('theme_path')) {
    /**
     * Get the path to the theme folder.
     *
     * @param  string  $path
     * @return string
     */
    function theme_path($path = '')
    {
        return public_path('themes/'.ltrim($path, '\\/'));
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

        // $map = ['127.0.0.1' => 'localhost'];

        // $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        // $host = $map[$host] ?? $host;

        // $env_host = strtolower(preg_replace('~^https?://~i', '', env('APP_URL', 'http://localhost')));
        // $env_host = $map[$env_host] ?? $env_host;

        // $pregDevUrl = '/^(127\.0\.0\.1|localhost)$|\.(test|dev)$/i';
        // if (!preg_match($pregDevUrl, $env_host) && $host === $env_host) {
        //     return 'production';
        // }

        // return 'local';
    }
}

if (! function_exists('under_route')) {
    function under_route($current, $parent)
    {
        return $current == $parent || strpos($current, $parent.'/') === 0;
    }
}

if (! function_exists('langcode')) {
    function langcode($type = null)
    {
        if (is_null($type)) {
            return [
                'content_value' => langcode('content_value'),
                'interface_value' => langcode('interface_value'),
                'admin_page' => langcode('admin_page'),
                'site_page' => langcode('site_page'),
            ];
        }

        switch ($type) {
            // 可用语言列表
            case 'available':
            case 'all':
            case 'list':
                $langs = [];
                $list = language_list('zh');
                $langcodes = config('jc.languages') ?? ['en','zh'];
                foreach ($langcodes as $code) {
                    $langs[$code] = $list[$code] ?? $code;
                }
                return $langs;

            // 界面语言
            case 'interface_value':
                return config('interface_value_langcode') ?: config('jc.interface_lang');

            case 'interface_value.default':
                return config('jc.interface_lang');

            // 内容语言
            case 'content_value':
                return config('content_value_langcode') ?: config('jc.content_lang');

            case 'content_value.default':
                return config('jc.content_lang');

            // 后台页面语言
            case 'admin_page':
                return config('jc.admin_page_lang');

            // 站点页面语言
            case 'site_page':
                return config('jc.site_page_lang');

            case 'current_page':
            case 'request':
                return config('request_langcode');

            default:
                return config('fallback_lacale');
        }
    }
}

if (! function_exists('langname')) {
    function langname($langcode, $interface_lang = null)
    {
        $interface_lang = $interface_lang ?: langcode('admin_page');
        $list = language_list($interface_lang);
        return $list[$langcode] ?? $langcode;
    }
}

if (! function_exists('extract_config')) {
    /**
     * @param array $config
     * @param array $structure
     * @param array $langcode
     */
    function extract_config(array $config, array $structure, array $langcode = [])
    {
        $langcode = array_merge(langcode(), $langcode);
        $original_langcode = $config['langcode'];
        unset($config['langcode']);

        $options = [];
        foreach ($config as $key => $value) {
            $meta = $structure[$key] ?? [];
            $type = $meta['type'] ?? null;
            if (($type === 'content_value' || $type === 'interface_value') && is_array($value)) {
                $value = $value[$langcode[$type]] ?? $value[$original_langcode[$type]] ?? null;
            }

            $options[$key] = $value;
        }

        // 填充默认值
        foreach ($structure as $key => $meta) {
            if (isset($meta['default']) && is_null($options[$key] ?? null)) {
                $options[$key] = $meta['default'];
            }
        }

        return $options;
    }
}

if (! function_exists('build_config')) {
    /**
     * @param array $data
     * @param array $structure
     */
    function build_config(array $data, array $structure)
    {
        $langcode = langcode();
        $config = [
            'langcode' => [
                'content_value' => $langcode['content_value'],
                'interface_value' => $langcode['interface_value'],
            ],
        ];

        foreach ($data as $key => $value) {
            if ($meta = $structure[$key] ?? null) {
                if ($cast = $meta['cast'] ?? null) {
                    $value = cast_value($value, $cast);
                }
                $type = $meta['type'] ?? null;
                if ($type === 'content_value' || $type === 'interface_value') {
                    $value = [
                        $langcode[$type] => $value,
                    ];
                }
                $config[$key] = $value;
            }
        }

        return $config;
    }
}

if (! function_exists('cast_value')) {
    function cast_value($value, $cast)
    {
        switch ($cast) {
            case 'string':
                return trim($value);

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
                $value = (array) $value;
                return array_filter($value);

            default:
                return $value;
        }
    }
}

if (! function_exists('mix_config')) {
    function mix_config($instances, array $langcode = [])
    {
        if ($instances instanceof JulyModel) {
            return $instances->mixConfig($langcode);
        }

        if ($instances instanceof Collection) {
            $results = [];
            foreach ($instances as $instance) {
                $results[] = mix_config($instance, $langcode);
            }
            return $results;
        }

        if ($instances instanceof Arrayable) {
            return $instances->toArray();
        }

        return $instances;
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
        return route($name, $parameters, false);
    }
}

if (! function_exists('short_url')) {
    /**
     * 生成一个短 url （不带域名）
     *
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @return string
     */
    function short_url($path)
    {
        return '/'.ltrim($path, '/');
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

        $lang = langcode();
        $data = array_merge([
            'content_value_langcode' => $lang['content_value'],
            'interface_value_langcode' => $lang['interface_value'],
            'admin_page_langcode' => $lang['admin_page'],
            'site_page_langcode' => $lang['site_page'],
        ], $data);

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('twig')) {
    function twig($path = 'default/template', $debug = false)
    {
        $loader = new \Twig\Loader\FilesystemLoader($path, theme_path());
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

if (! function_exists('html_escape')) {
    function html_escape($html)
    {
        return str_replace('`', '\\`', $html);
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
    function build_google_sitemap(array $urls = null)
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

        $langcode = config('jc.site_page_lang');

        if (! $urls) {
            $urls = [];
            foreach (Catalog::default()->get_nodes() as $node) {
                $node = $node->retrieveValues($langcode);
                if ($url = $node['url'] ?? null) {
                    $urls[$url] = true;
                }
            }
            $urls = array_keys($urls);
        }

        // 生成 xml 内容
        foreach ($urls as $url) {
            if ($url === '/404.html') continue;

            $xml .= '<url><loc>'.$home.$url.'</loc>';

            $content = Node::retrieveHtml($url, $langcode);
            if (empty($content)) {
                $xml .= '</url>';
                continue;
            }

            foreach (extract_image_links($content) as $src) {
                $xml .= '<image:image><image:loc>'.$home.$src."</image:loc></image:image>";
            }

            $xml .= '</url>';

            $pdfList = array_merge($pdfList, extract_pdf_links($content));
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
    function language_list($langcode)
    {
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
