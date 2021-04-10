<?php

namespace July\Node;

use App\Providers\ModuleServiceProviderBase;
use App\Support\JustInTwig;
use July\Node\TwigExtensions\NodeMixin;

class NodeServiceProvider extends ModuleServiceProviderBase
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        JustInTwig::mixin(new NodeMixin);
    }

    /**
     * 获取实体类
     *
     * @return array
     */
    protected function discoverEntities()
    {
        return [
            \July\Node\Node::class,
        ];
    }

    protected function discoverActions()
    {
        return [
            \July\Node\Actions\RebuildIndex::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModuleRoot()
    {
        return dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModuleName()
    {
        return 'node';
    }
}
