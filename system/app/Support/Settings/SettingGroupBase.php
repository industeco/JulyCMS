<?php

namespace App\Support\Settings;

abstract class SettingGroupBase
{
    /**
     * 配置组名称
     *
     * @var string
     */
    protected $name = '';

    /**
     * 配置组标题
     *
     * @var string
     */
    protected $title = '';

    /**
     * 配置项
     *
     * @var array
     */
    protected $items = [];

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * 获取配置文件路径
     *
     * @return string
     */
    protected function getPath()
    {
        return storage_path('settings/'.$this->name.'.php');
    }

    /**
     * 加载配置
     *
     * @return void
     */
    public function load()
    {
        if (is_file($file = $this->getPath())) {
            $this->merge(require $file);
        }

        $this->addMenuItem();
    }

    /**
     * 添加菜单项
     */
    public function addMenuItem()
    {
        if ($this->title) {
            $children = config('app.main_menu.settings.children', []);
            $children[] = [
                'title' => $this->title,
                'icon' => null,
                'route' => ['settings.edit', $this->name],
                'children' => [],
            ];
            config(['app.main_menu.settings.children' => $children]);
        }
    }

    /**
     * 整合配置数据到当前配置中，然后保存到文件
     *
     * @param  array $settings
     * @return void
     */
    public function save(array $settings)
    {
        // 过滤并整合配置数据到当前配置环境
        $settings = $this->merge($settings);

        // 保存配置数据到文件
        $content = "<?php\n\nreturn ".trim(var_export($settings, TRUE)).";\n";
        file_put_contents($this->getPath(), $content);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function view()
    {
        $data = [
            'name' => $this->name,
            'title' => $this->title,
            'items' => $this->items,
            'settings' => [],
        ];

        foreach ($data['items'] as $key => $item) {
            $data['items'][$key]['tips'] = "{{ config('{$key}') }}";
            $data['settings'][$key] = config($key);
        }

        return view()->make('settings.'.$this->name, $data);
    }

    /**
     * 过滤配置数据，然后整合到当前配置环境
     *
     * @param  array $data
     * @return array
     */
    protected function merge(array $data)
    {
        // 过滤配置
        $settings = [];
        foreach (array_keys($this->items) as $key) {
            $settings[$key] = $data[$key] ?? null;
        }

        // 合并到当前配置环境
        config($settings);

        return $settings;
    }
}
