<?php

namespace July\Message;

use App\Casts\Serialized;
use App\Entity\EntityBase;
use Illuminate\Mail\Message as MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use IP2Location\Database as Location;
use July\Message\FieldTypes\Attachment;
use July\Message\FieldTypes\MultipleAttachment;

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
     * 实体标题列
     *
     * @var string
     */
    protected $titleColumn = 'subject';

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
        $trails = empty($trails) ? [] : $this->formatTrails($trails);
        $this->attributes['trails'] = serialize($trails);
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
        $attachments = $this->getAttachments();
        $subject = $this->subject ?? 'New Message';

        try {
            Mail::raw($content, function(MailMessage $message) use($subject, $attachments) {
                $message->subject($subject)->to(config('mail.to.address'), config('mail.to.name'));
                foreach ($attachments as $attachment) {
                    $message->attach($attachment['path'], $attachment['options']);
                }
            });
            $success = true;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $success = false;
        }

        if (! $success) {
            $success = mail(config('mail.to.address'), $subject, $content);
        }

        if ($success && !$this->is_sent) {
            $this->clearRaw()->update(['is_sent' => true]);
        }

        return $success;
    }

    /**
     * @return array[]
     */
    public function getAttachments()
    {
        /** @var \Illuminate\Http\UploadedFile[] */
        $files = [];

        foreach ($this->fields as $field) {
            $fieldType = $field->getFieldType();
            // 附件字段
            if ($fieldType instanceof Attachment) {
                $files[] = request()->file($field->getKey());
            }

            // 多附件字段
            elseif ($fieldType instanceof MultipleAttachment) {
                $value = request()->file($field->getKey());
                if (is_array($value)) {
                    $files = array_merge($files, $value);
                }
            }
        }

        $attachments = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $attachments[] = [
                    'path' => $file->getPathname(),
                    'options' => [
                        'mime' => $file->getMimeType(),
                        'as' => $file->getClientOriginalName(),
                    ],
                ];
            }
        }

        return $attachments;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render($showin = 'mail')
    {
        $view = 'message/content/'.$this->mold_id.'.twig';
        $data = [
            'message' => $this->attributesToArray(),
            'fields' => $this->fields->map(function (MessageField $field) {
                    return array_merge($field->getMeta(), ['value' => $field->bindEntity($this)->getValue()]);
                })->keyBy('id')->all(),
            'show_in' => $showin,
        ];

        return app('twig')->render($view, $data);
    }

    /**
     * 格式化浏览轨迹报告
     *
     * @param  string|null $report
     * @return array
     */
    protected function formatTrails(?string $report = null)
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
