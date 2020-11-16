<?php

use Illuminate\Support\Str;

if (! function_exists('july_path')) {
    /**
     * 主要功能区路径
     *
     * @param  string  $path
     * @return string
     */
    function july_path($path = '')
    {
        return base_path('july'.($path ? DIRECTORY_SEPARATOR.ltrim($path, '\\/') : $path));
    }
}

if (! function_exists('twig')) {
    function twig($path = null, $debug = false)
    {
        $loader = new \Twig\Loader\FilesystemLoader($path ?: 'template', frontend_path());
        if ($debug) {
            $twig = new \Twig\Environment($loader, ['debug' => true]);
            $twig->addExtension(new \Twig\Extension\DebugExtension);
        } else {
            $twig = new \Twig\Environment($loader);
        }

        $twig->addExtension(new \July\Support\Twig\EntityQueryExtension);

        return $twig;
    }
}

if (! function_exists('variablize')) {
    function variablize(string $str, string $replacement = '_')
    {
        $str = preg_replace('/[^0-9a-z_]/', $replacement, Str::snake($str));

        $str = preg_replace('/[^0-9a-z_]/', '_', $str);
        if (!preg_match('/^[a-z_]/', $str)) {
            $str = '_'.$str;
        }

        return $str;
    }
}
