<?php

use Illuminate\Support\Str;

if (! function_exists('july_path')) {
    /**
     * 后台主题路径
     *
     * @param  string  $path
     * @return string
     */
    function july_path($path = '')
    {
        $pieces = array_filter([
            'july',
            ltrim($path, '\\/'),
        ]);

        return base_path(join(DIRECTORY_SEPARATOR, $pieces));
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

if (! function_exists('normalize_entity_name')) {
    function normalize_entity_name(string $name)
    {
        return str_replace('.', '__', Str::snake($name));
    }
}

if (! function_exists('variablize')) {
    function variablize(string $str, array $replace = null)
    {
        if ($replace) {
            $str = str_replace(array_keys($replace), array_values($replace), $str);
        }
        return preg_replace('/[^0-9a-z_]/', '_', Str::snake($str));
    }
}
