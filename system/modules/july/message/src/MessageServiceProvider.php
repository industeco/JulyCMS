<?php

namespace July\Message;

use App\Providers\ModuleServiceProviderBase;
use App\Support\JustInTwig;

class MessageServiceProvider extends ModuleServiceProviderBase
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // 获取表单对象
        JustInTwig::macro('get_form', function($form) {
            return MessageForm::find($form) ?? MessageForm::default();
        });
    }

    /**
     * 获取实体类
     *
     * @return array
     */
    protected function discoverEntities()
    {
        return [
            \July\Message\Message::class,
        ];
    }

    protected function discoverEntityFieldTypes()
    {
        return [
            \July\Message\FieldTypes\Attachment::class,
            \July\Message\FieldTypes\MultipleAttachment::class,
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
        return 'message';
    }
}
