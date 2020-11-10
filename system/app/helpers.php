<?php

use App\Utils\Types;
use App\Utils\Lang;
use App\Utils\Html;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;

// if (! function_exists('july_path')) {
//     /**
//      * 后台主题路径
//      *
//      * @param  string  $path
//      * @return string
//      */
//     function july_path($path = '')
//     {
//         $pieces = array_filter([
//             'july',
//             ltrim($path, '\\/'),
//         ]);

//         return base_path(join(DIRECTORY_SEPARATOR, $pieces));
//     }
// }

if (! function_exists('backend_path')) {
    /**
     * 后台主题路径
     *
     * @param  string  $path
     * @return string
     */
    function backend_path($path = '')
    {
        $pieces = array_filter([
            'themes',
            trim(config('jc.theme.backend'), '\\/'),
            ltrim($path, '\\/'),
        ]);

        return public_path(join(DIRECTORY_SEPARATOR, $pieces));
    }
}

if (! function_exists('frontend_path')) {
    /**
     * 前端主题路径
     *
     * @param  string  $path
     * @return string
     */
    function frontend_path($path = '')
    {
        $pieces = array_filter([
            'themes',
            trim(config('jc.theme.frontend'), '\\/'),
            ltrim($path, '\\/'),
        ]);

        return public_path(join(DIRECTORY_SEPARATOR, $pieces));
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
     * @return \App\Utils\Lang
     */
    function lang($langcode = null)
    {
        return new Lang($langcode);
    }
}

if (! function_exists('langcode')) {
    function langcode($alias = null)
    {
        return lang()->findLangcode($alias);
    }
}

if (! function_exists('cast')) {
    function cast($value, $caster, $force = true)
    {
        return Types::cast($value, $caster, $force);
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
    function short_url($name, $parameters = [])
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
        $route = short_url($route);
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

if (! function_exists('str_diff')) {
    function str_diff($str1, $str2)
    {
        $diff = str_replace(str_split($str1), '', $str2);
        return strlen($diff);
    }
}

if (! function_exists('normalize_args')) {
    /**
     * 格式化传入参数
     *
     * @param array $args 文件名
     * @return array
     */
    function normalize_args(array $args)
    {
        // 如果只有一个参数，而且是一个数组，则假设该数组才是用户真正想要传入的参数
        if (count($args) === 1 && is_array($args[0] ?? null)) {
            $args = $args[0];
        }

        return $args;
    }
}

if (! function_exists('events')) {
    /**
     * @return \App\Utils\EventsBook
     */
    function events()
    {
        return app('events_book');
    }
}


// if (! function_exists('html')) {
//     /**
//      * 获取 Html 对象
//      *
//      * @param string $html
//      * @return \App\Utils\Html
//      */
//     function html($html)
//     {
//         return new Html($html);
//     }
// }

// if (! function_exists('get_file_list')) {
//     /**
//      * 根据白名单获取文件列表
//      *
//      * @param array $list 白名单
//      * @param string|null $root 起始目录
//      * @return array 文件名列表，格式：[文件路径 => 新的文件路径|'']
//      */
//     function get_file_list(array $list, $root = null)
//     {
//         $files = [];
//         $root = rtrim($root ?: public_path(), '/').'/';
//         foreach ($list as $original => $target) {
//             if ('/*' === substr($original, -2)) {
//                 $original = substr($original, 0, strlen($original)-1);
//                 if ($target) {
//                     $target = rtrim($target, '/').'/';
//                 }
//                 $_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.$original));
//                 foreach ($_files as $name => $file) {
//                     $filePath = str_replace('\\', '/', $file->getRealPath());
//                     $relativePath = substr($filePath, strlen($root));
//                     if ($file->isDir()) {
//                         $relativePath .= '/';
//                     }
//                     $files[$relativePath] = $target ? $target.substr($filePath, strlen($root.$original)) : '';
//                 }
//             } else {
//                 $files[$original] = $target;
//             }
//         }

//         return $files;
//     }
// }

// if (! function_exists('package_files')) {
//     /**
//      * 打包文件
//      *
//      * @param string $pkg 文件名
//      * @param array $files 文件名列表，格式：[文件路径 => 新的文件路径|'']
//      * @return file
//      */
//     function package_files($pkg, array $files)
//     {
//         $zip = new \ZipArchive();
//         $zip->open($pkg, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

//         $path = rtrim(public_path(), '/').'/';
//         foreach ($files as $file => $newName) {
//             $filePath = $path.$file;
//             if (substr($file, -1) === '/' || is_dir($filePath)) {
//                 $zip->addEmptyDir($file);
//             } elseif (is_file($filePath)) {
//                 $zip->addFile($filePath, $newName ?: $file);
//             }
//         }
//         $zip->close();

//         return true;
//     }
// }
