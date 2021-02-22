<?php

namespace July\Message;

use App\Casts\Serialized;
use App\Entity\EntityBase;
use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use IP2Location\Database as Location;

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
        'subject',
        'langcode',
        'is_sent',
        'user_agent',
        'ip',
        'trails',
        '_server',
    ];

    protected $casts = [
        'trails' => Serialized::class,
        '_server' => Serialized::class,
    ];

    /**
     * 附加属性
     *
     * @var array
     */
    protected $appends = [
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
     * trails 属性的 Mutator
     *
     * @param  string $trails
     * @return string
     */
    public function setTrailsAttribute($trails)
    {
        if (empty($trails)) {
            $this->attributes['trails'] = null;
        } else {
            $this->attributes['trails'] = serialize($this->formatTrails($trails));
        }
    }

    /**
     * location 属性的 Accessor
     *
     * @return string
     */
    public function getLocationAttribute()
    {
        $ip = $this->ip;
        if (! $ip) {
            return 'Location: -/-/-';
        }

        return 'Location: '.join('/', static::getLocation($ip));
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

    /**
     * 以邮件形式发送消息
     *
     * @return bool
     */
    public function sendMail()
    {
        $content = $this->render();
        $subject = $this->subject ?? 'New Message';
        $success = Mail::raw($content, function(MailMessage $message) use($subject) {
            $message->subject($subject)->to(config('mail.to.address'), config('mail.to.name'));
        });

        if (! $success) {
            $success = mail(config('mail.to.address'), $subject, $content);
        } elseif (!$this->is_sent) {
            $this->is_sent = true;
            $this->save();
        }

        return $success;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $view = 'message/content/'.$this->mold_id.'.twig';
        $data = [
            'message' => $this->attributesToArray(),
            'fields' => $this->fields->map(function (MessageField $field) {
                    return $field->attributesToArray() + ['value' => $field->bindEntity($this)->getValue()];
                })->keyBy('id')->all(),
        ];

        return app('twig')->render($view, $data);
    }

    /**
     * 格式化浏览轨迹报告
     *
     * @param  string $report
     * @return array
     */
    protected function formatTrails(string $report)
    {
        $report = json_decode(stripslashes($report), true);

        $trails = array_map(function ($record) {
            $min = strval(intval($record[1]/60));
            $sec = strval(intval($record[1]%60));
            return "[{$min}m {$sec}s] ".$record[0];
        }, $report['trace']);

        $trails[] = '[-] '.$report['refer'];

        return $trails;
    }

    /**
     * 从 ip 获取位置信息
     *
     * @return array
     */
    public static function getLocation(string $ip)
    {
        $record = (new Location)->lookup($ip, Location::ALL);
        $location = [];
        foreach (['countryName', 'regionName', 'cityName'] as $name) {
            $location[$name] = $record[$name] ?? '-';
        }
        return $location;
    }
}
