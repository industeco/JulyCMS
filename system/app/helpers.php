<?php

use App\Models\Config;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\View\Factory as ViewFactory;

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
        return public_path('theme/'.ltrim($path, '\\/'));
    }
}

if (! function_exists('media_path')) {
    /**
     * Get the path to the media folder.
     *
     * @param  string  $path
     * @return string
     */
    function media_path($path = '')
    {
        return public_path('media/'.ltrim($path, '\\/'));
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
                return ['zh','en'];

            // 界面语言
            case 'interface':
            case 'interface_value':
            case 'interface_value_lang':
                return config('interface_value_lang') ?: config('app.langcode.interface_value');

            // 内容语言
            case 'content':
            case 'content_value':
            case 'content_value_lang':
                return config('content_value_lang') ?: config('app.langcode.content_value');

            // 后台页面语言
            case 'admin':
            case 'admin_page':
            case 'admin_page_lang':
                return config('request_lang') ?: config('app.langcode.admin_page');

            // 站点页面语言
            case 'site':
            case 'site_page':
            case 'site_page_lang':
            case 'page':
            case 'page_lang':
                return config('request_lang') ?: config('app.langcode.site_page');

            default:
                return config('fallback_lacale');
        }
    }
}

if (! function_exists('langname')) {
    function langname($langcode)
    {
        $list = [
            'zh' => '中文',
            'en' => 'English',
        ];

        return $list[$langcode] ?? $langcode;
    }
}

if (! function_exists('flatten_config')) {
    /**
     * @param array $config
     * @param array $langcode
     */
    function flatten_config(array $config, array $langcode)
    {
        $result = [];
        $original = $config['langcode'] ?? [];

        foreach ($config as $key => $value) {
            if ($key == 'langcode') {
                continue;
            }
            if ($key == 'interface_values' || $key == 'content_values') {
                $lang_key = trim($key, 's');
                $lang = $langcode[$lang_key] ?? null;
                foreach ($value as $k => $v) {
                    $result[$k] = $v[$lang] ?? $v[$original[$lang_key]];
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

if (! function_exists('describe')) {
    function describe($records, array $langcode = null)
    {
        $langcode = $langcode ?: langcode();

        if (!(is_array($records) || $records instanceof Arrayable)) {
            return [];
        }

        if ($records instanceof Arrayable) {
            $records = $records->toArray();
        }

        if (($config = $records['config'] ?? null) && is_array($config)) {
            unset($records['config']);
            return array_merge($records, flatten_config($config, $langcode));
        }

        $results = [];
        foreach ($records as $key => $record) {
            if ($record instanceof Arrayable) {
                $record = $record->toArray();
            }
            if (is_array($record)) {
                if (($config = $record['config'] ?? null) && is_array($config)) {
                    unset($record['config']);
                    $record = array_merge($record, flatten_config($config, $langcode));
                }
                $results[] = $record;
            } else {
                $results[$key] = $record;
            }
        }

        return $results;
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
    function short_url($name, $parameters = [])
    {
        return route($name, $parameters, false);
    }
}

if (! function_exists('view_with_lang')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function view_with_lang($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        $lang = langcode();
        $data = array_merge([
            'content_value_lang' => $lang['content_value'],
            'interface_value_lang' => $lang['interface_value'],
            'admin_page_lang' => $lang['admin_page'],
            'site_page_lang' => $lang['site_page'],
        ], $data);

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('twig')) {
    function twig($debug = false)
    {
        $loader = new \Twig\Loader\FilesystemLoader('default/template', theme_path());
        if ($debug) {
            $twig = new \Twig\Environment($loader, ['debug' => true]);
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        } else {
            $twig = new \Twig\Environment($loader);
        }

        $twig->addExtension(new \App\TwigExtensions\ContentsQuery());

        return $twig;
    }
}

if (! function_exists('html_escape')) {
    function html_escape($html)
    {
        return str_replace('\'', '\\\'', $html);
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
