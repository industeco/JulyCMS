<?php

namespace July\Message;

use App\Entity\EntityBase;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class Message extends EntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'mold_id',
        'langcode',
    ];

    /**
     * 附加属性
     *
     * @var array
     */
    protected $appends = [
        'trails_report',
        'user_agent',
        'ip',
        'location',
    ];

    /**
     * 获取实体类型类
     *
     * @return string
     */
    public static function getMoldClass()
    {
        return MessageForm::class;
    }

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getFieldClass()
    {
        return MessageField::class;
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotClass()
    {
        return MessageFieldMessageForm::class;
    }

    /**
     * trails_report 属性的 Accessor
     *
     * @return string
     */
    public function getTrailsReportAttribute()
    {
        //
    }

    /**
     * user_agent 属性的 Accessor
     *
     * @return string
     */
    public function getUserAgentAttribute()
    {
        $agent = new Agent();

        $browser = $agent->browser();
        $browserVersion = $agent->version($browser);
        $platform = $agent->platform();
        $platformVersion = $agent->version($platform);

        return "{$browser}[{$browserVersion}] on {$platform}[{$platformVersion}]";
    }

    /**
     * ip 属性的 Accessor
     *
     * @return string
     */
    public function getIpAttribute()
    {
        //
    }

    /**
     * location 属性的 Accessor
     *
     * @return string
     */
    public function getLocationAttribute()
    {
        //
    }

    /**
     * 实体所属类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mold()
    {
        return $this->belongsTo(MessageForm::class, 'mold_id');
    }

    public function send()
    {
        //
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $view = 'message/content--'.$this->mold_id.'.twig';
        $data = [
            'message' => $this->attributesToArray(),
            'fields' => $this->fields->map(function (MessageField $field) {
                    return $field->attributesToArray() + ['value' => $field->bindEntity($this)->getValue()];
                })->keyBy('id')->all(),
        ];

        return app('twig')->render($view, $data);
    }
}
