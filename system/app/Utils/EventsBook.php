<?php

namespace App\Utils;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class EventsBook
{
    /**
     * @var array
     */
    protected $events = [];

    public function __construct()
    {
        $this->events = Cache::get('july_events_book', []);
    }

    /**
     * 存储到本地
     *
     * @return self
     */
    public function store()
    {
        // Log::info('Events Book has been stored.');
        Cache::put('july_events_book', $this->events);

        return $this;
    }

    /**
     * 记录一个事件
     *
     * @param  string $event 事件名
     * @return self
     */
    public function record(string $event)
    {
        $this->events[$event] = microtime(true);

        return $this;
    }

    /**
     * 清除一个事件，或清除所有
     *
     * @param  string|null $event 事件名
     * @return self
     */
    public function clear(string $event = null)
    {
        if (0 === func_num_args()) {
            $this->events = [];
        } elseif (isset($this->events[$event])) {
            unset($this->events[$event]);
        }

        return $this;
    }

    /**
     * 获取一个事件的发生时间
     *
     * @param string $event 事件名
     * @return float
     */
    public function when(string $event)
    {
        return $this->events[$event] ?? \INF;
    }

    /**
     * 判断事件是否已发生
     *
     * @param string $event 事件名
     * @return bool
     */
    public function hasHappened(string $event)
    {
        return isset($this->events[$event]);
    }

    /**
     * 判断事件是否在指定时间之前发生过
     *
     * @param  string|array $events 事件名（数组）
     * @param  float|string $time 时间戳或事件名
     * @return bool
     */
    public function hasHappenedBefore($events, $time = null)
    {
        if (! is_array($events)) {
            $events = [$events];
        }

        $time = $time ?? microtime(true);
        if (is_string($time)) {
            $time = $this->when($time);
        }

        foreach ($events as $event) {
            if ($this->when($event) < $time) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断事件是否在指定时间之后发生过
     *
     * @param  string|array $events 事件名（数组）
     * @param  float|string $time 时间戳或事件名
     * @return bool
     */
    public function hasHappenedAfter($events, $time = null)
    {
        if (! is_array($events)) {
            $events = [$events];
        }

        $time = $time ?? microtime(true);
        if (is_string($time)) {
            $time = $this->when($time);
        }

        foreach ($events as $event) {
            if ($this->when($event) >= $time) {
                return true;
            }
        }

        return false;
    }

    public static function __callStatic($name, $arguments)
    {
        return app('events_book')->$name(...$arguments);
    }
}
