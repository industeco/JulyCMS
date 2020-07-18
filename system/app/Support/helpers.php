<?php

use App\Models\Catalog;
use App\Support\Arr;
use App\Support\Lang;
use App\Support\Html;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (! function_exists('background_path')) {
    /**
     * 后台主题路径
     *
     * @param  string  $path
     * @return string
     */
    function background_path($path = '')
    {
        return public_path('themes/'.config('jc.theme.background').'/'.ltrim($path, '\\/'));
    }
}

if (! function_exists('foreground_path')) {
    /**
     * 前端主题路径
     *
     * @param  string  $path
     * @return string
     */
    function foreground_path($path = '')
    {
        return public_path('themes/'.config('jc.theme.foreground').'/'.ltrim($path, '\\/'));
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

if (! function_exists('lang')) {
    /**
     * 获取语言操作对象
     *
     * @param string|null $langcode
     * @return \App\Support\Lang
     */
    function lang($langcode = null)
    {
        return new Lang($langcode);
    }
}

if (! function_exists('langcode')) {
    function langcode($alias = null)
    {
        return lang()->findCode($alias);
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

if (! function_exists('under_route')) {
    function under_route($route, $path)
    {
        $route = short_route($route);
        return $path == $route || strpos($path, $route.'/') === 0;
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
            'langcode' => langcode('content'),
        ], $data);

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('twig')) {
    function twig($path = null, $debug = false)
    {
        $loader = new \Twig\Loader\FilesystemLoader($path ?: 'template', foreground_path());
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

if (! function_exists('is_json')) {
    function is_json($value)
    {
        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (! function_exists('last_modified')) {
    function last_modified($path)
    {
        if (is_file($path)) {
            return app('files')->lastModified($path);
        } elseif (is_dir($path)) {
            $fs = app('files');
            $lastModified = 0;
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach ($files as $file) {
                $modified = $fs->lastModified($file->getRealPath());
                if ($modified > $lastModified) {
                    $lastModified = $modified;
                }
            }
            return $lastModified;
        }
        return null;
    }
}

if (! function_exists('guess_ua')) {
    function guess_ua($ua = null)
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

if (! function_exists('html')) {
    /**
     * 获取 Html 对象
     *
     * @param string $html
     * @return \App\Support\Html
     */
    function html($html)
    {
        return new Html($html);
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

        $eol = "\n";

        // xml 文件
        $xml = '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.$eol;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        $xml .= ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
        $xml .= ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'.$eol;

        $xml .= '<url><loc>'.$home.'/'.'</loc></url>'.$eol;

        $urls = DB::table('content__url')
                ->where('langcode', $langcode)
                ->get()
                ->pluck('url_value', 'content_id')
                ->all();

        // 生成 xml 内容
        foreach (Catalog::default()->get_contents() as $content) {
            $url = $urls[$content->id] ?? null;
            if (!$url || $url === '/404.html') continue;

            $html = $content->getHtml($langcode);
            if (is_null($html)) {
                continue;
            }
            $html = html($html);

            $xml .= '<url><loc>'.$home.$url.'</loc>';

            foreach ($html->extractImageLinks() as $src) {
                $xml .= '<image:image><image:loc>'.$home.$src."</image:loc></image:image>".$eol;
            }
            $xml .= '</url>'.$eol;

            $pdfList = array_merge($pdfList, $html->extractPdfLinks());
        }

        foreach(array_unique($pdfList) as $pdf) {
            $xml .= '<url><loc>'.$home.$pdf.'</loc></url>'.$eol;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}

if (! function_exists('str_diff')) {
    function str_diff($str1, $str2)
    {
        $str2 = str_replace(str_split($str1), '', $str2);
        return strlen($str2);
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
