<?php

namespace JulyInstaller;

class EnvEntry
{
    /**
     * @var string|null
     */
    protected $key;

    /**
     * @var string|null
     */
    protected $value;

    /**
     * @var string|null
     */
    protected $comment;


    /**
     * 新建 env 条目
     */
    function __construct($key = null, $value = null, $comment = null)
    {
        if (! self::isValidKey($key)) {
            $key = '';
            $value = '';
        }
        $this->setKey($key)->setValue($value)->setComment($comment);
    }

    public static function create($key, $value = null, $comment = null)
    {
        if (! self::isValidKey($key)) {
            return null;
        }
        return new self($key, $value, $comment);
    }

    /**
     * 从一行字符生成一个 env 条目
     */
    public static function createFromLine($line = '')
    {
        $line = trim($line);

        if (! strlen($line)) {
            return new self;
        }

        if (substr($line, 0, 1) === '#') {
            return new self(null, null, $line);
        }

        if (strpos($line, '=') > 0) {
            list($key, $value) = preg_split('/=/', $line, 2);
            if (! self::isValidKey($key)) {
                return null;
            }
            if (preg_match('/^(\'|")[^\'"]*\1$/', $value) || !preg_match('/\s+#\s+/', $value, $matches)) {
                return new self($key, $value);
            } else {
                preg_match('/^([^#]*?)\s+#\s+(.*)$/', $value, $matches);
                return new self($key, $matches[1], $matches[2]);
            }
        }

        return null;
    }

    public static function isValidKey($key)
    {
        return !!preg_match('/^[a-zA-Z0-9._]+$/', trim($key));
    }

    public function toString()
    {
        $line = '';

        if ($this->key) {
            $line .= $this->key . '=' . $this->value;
        }

        if ($this->comment) {
            $line .= ' # ' . $this->comment;
        }

        return $line;
    }

    public function isEmpty()
    {
        return empty($this->key) && empty($this->comment);
    }

    public function isComment()
    {
        return empty($this->key) && !empty($this->comment);
    }

    /**
     * 设置 key
     *
     * @param  mixed  $value
     * @return self
     */
    protected function setKey($key)
    {
        $this->key = trim($key);
        return $this;
    }

    /**
     * 设置 value
     *
     * @param  mixed  $value
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $this->quoteValue($value);
        return $this;
    }

    public function setComment($comment)
    {
        $this->comment = trim(preg_replace('/^#\s*/', '', $comment));
        return $this;
    }

    protected function castValue($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return $value;
    }

    protected function quoteValue($value)
    {
        if (is_string($value)) {
            if (strpos($value, ' ') !== false && !preg_match('/^(\'|")[^\'"]*\1$/', $value)) {
                return '"' . $value . '"';
            }
            return $value;
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return '' . $value;
    }

    public function __get($name)
    {
        return $name == 'value' ? $this->castValue($this->value) : ($this->$name ?? null);
    }
}
