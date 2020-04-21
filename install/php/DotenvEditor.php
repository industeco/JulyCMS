<?php

namespace JulyInstaller;

use Exception;
use InvalidArgumentException;
use SplFileObject;
use JulyInstaller\EnvEntry;

class DotenvEditor
{
    /**
     * @var EnvEntry[]
     */
    public $env = [];

    /**
     * @var \SplFileObject|null
     */
    protected $envFile;

    /**
     * Load an values from an env file.
     *
     * @param  string  $path
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function load($path)
    {
        if (! file_exists($path)) {
            throw new InvalidArgumentException(sprintf('%s does not exist', $path));
        }

        $this->envFile = new SplFileObject($path, 'r+');

        if ($this->envFile->getSize() > 0) {
            $this->getEnvEntries();
        }

        return $this;
    }

    protected function getEnvEntries()
    {
        $this->env = [];

        $lines = preg_split(
            "/(\r\n|\n|\r)/",
            $this->envFile->fread($this->envFile->getSize())
        );

        foreach ($lines as $line) {
            if ($entry = EnvEntry::createFromLine($line)) {
                $this->env[] = $entry;
            } else {
                throw new Exception('.env 格式错误：'.$line);
            }
        }
    }

    /**
     * Set a key value pair for the env file.
     *
     * @param  string  $key
     * @param  string  $value
     * @return self
     */
    public function set($key, $value)
    {
        foreach ($this->env as $entry) {
            if ($entry->key === $key) {
                $entry->setValue($value);
                return $this;
            }
        }

        if (EnvEntry::isValidKey($key)) {
            $this->env[] = new EnvEntry($key, $value);
        }

        return $this;
    }

    /**
     * Unset a key value of the env file.
     *
     * @param  string  $key
     * @return self
     */
    public function unset($key)
    {
        foreach ($this->env as $index => $entry) {
            if ($entry->key === $key) {
                $this->env = array_splice($this->env, $index, 1);
                return $this;
            }
        }
        return $this;
    }

    /**
     * Get all of the env values or a single value by key.
     *
     * @param  string  $key
     * @return array|string
     */
    public function get($key)
    {
        foreach ($this->env as $entry) {
            if ($entry->key === $key) {
                return $entry->value;
            }
        }
        return null;
    }

    /**
     * Save the current representation to disk. If no path is specifed and
     * a file was loaded, it will overwrite the file that was loaded.
     *
     * @param  string  $path
     * @return bool
     */
    public function save()
    {
        $this->envFile->rewind();
        $this->envFile->ftruncate(0);
        $this->envFile->fwrite($this->toString());

        return $this;
    }

    /**
     * Add an empty line to the config.
     * @return self
     */
    public function addEmptyLine()
    {
        $this->env[] = new EnvEntry();

        return $this;
    }

    public function __destruct()
    {
        if ($this->envFile) {
            $this->envFile = null;
        }
    }

    public function close()
    {
        $this->envFile = null;
    }

    /**
     * Format the config file in key=value pairs.
     *
     * @return string
     */
    public function toString()
    {
        $lines = array_map(function($entry) {
            return $entry->toString();
        }, $this->env);

        return implode("\n", $lines);
    }
}
