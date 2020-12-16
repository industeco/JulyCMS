<?php

namespace App\Plugin;

use App\Utils\Pocket;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class Plugin
{
    /**
     * 插件名
     *
     * @var string
     */
    protected $name;

    /**
     * 插件命名空间
     *
     * @var string
     */
    protected $namespace;

    /**
     * 插件路径
     *
     * @var string
     */
    protected $path;

    public function __construct(string $name, string $namespace = null, string $path = null)
    {
        $this->name = $name;
        $this->namespace = trim($namespace ?: ucfirst($name), '\\');
        $this->path = $path ?: base_path($name);
    }

    /**
     * 根据插件名查找插件
     *
     * @param  string $name 插件名
     * @return \App\Plugin\Plugin|null
     */
    public static function find(string $name)
    {
        if (is_file($json = base_path('plugins/'.$name.'/plugin.json'))) {
            $meta = json_decode(file_get_contents($json), true);
            return new static(
                $meta['name'],
                $meta['namespace'] ?? null
            );
        }
        return null;
    }

    /**
     * 找出所有插件
     *
     * @return \Illuminate\Support\Collection|\App\Plugin\Plugin[]
     */
    public static function all()
    {
        $plugins = 'production' === config('app.env') ? static::takeoutPlugins() : static::discoverPlugins();

        return collect($plugins)->keyBy('name')->map(function(array $plugin) {
            return new Plugin($plugin['name'], $plugin['namespace'] ?? null, $plugin['path'] ?? null);
        });
    }

    /**
     * 优先从缓存中检索插件信息
     *
     * @return array
     */
    protected static function takeoutPlugins()
    {
        $pocket = Pocket::make(static::class)->useKey('plugin_infos');
        if ($plugins = $pocket->get()) {
            return $plugins->value();
        }

        $pocket->put($plugins = static::discoverPlugins());

        return $plugins;
    }

    /**
     * 扫描文件系统，检索插件信息
     *
     * @return array
     */
    protected static function discoverPlugins()
    {
        $plugins = [];
        $jsons = Finder::create()->files()->name('plugin.json')->in(base_path('plugins'))->depth(1);
        foreach ($jsons as $json) {
            $meta = json_decode($json->getContents(), true);
            $plugins[] = [
                'name' => $meta['name'],
                'namespace' => $meta['namespace'] ?? null,
                'path' => $json->getPath(),
            ];
        }

        return $plugins;
    }

    /**
     * 获取插件名称
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取插件命名空间
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * 获取插件路径
     *
     * @return string
     */
    public function getPath(string $path = '')
    {
        $path = trim($path, '\\/');
        return $this->path.($path ? \DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * 获取插件配置文件
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->getPath('config.php');
    }

    /**
     * 获取插件多语言目录
     *
     * @return string
     */
    public function getTranslationsPath()
    {
        return $this->getPath('lang');
    }

    /**
     * 获取插件视图目录
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->getPath('views');
    }

    /**
     * 获取插件迁移目录
     *
     * @return string
     */
    public function getMigrationsPath()
    {
        return $this->getPath('migrations');
    }

    /**
     * 获取插件迁移目录
     *
     * @return string
     */
    public function getSeedersPath()
    {
        return $this->getPath('seeds');
    }

    /**
     * 查找插件的数据填充器
     *
     * @return array
     */
    public function getSeeders()
    {
        $seeders = [];
        $files = Finder::create()->files()->name('*.php')->in($this->path.\DIRECTORY_SEPARATOR.'seeds')->depth(0);
        foreach ($files as $file) {
            $seeders[] = $this->namespace.'\\seeds\\'.$file->getBasename('.php');
        }

        return $seeders;
    }

    /**
     * 加载插件路由
     */
    public function loadRoutes()
    {
        Route::middleware('web')
            ->group($this->getPath('routes/web.php'));

        Route::prefix('api')
            ->middleware('api')
            ->group($this->getPath('routes/api.php'));
    }
}
