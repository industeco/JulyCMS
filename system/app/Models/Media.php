<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class Media
{
    protected $disk;
    protected $code = 200;
    protected $category = 'images';
    protected $cwd = 'images/';

    public function __construct()
    {
        $this->disk = Storage::disk('media');
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $cwd
     * @return self
     */
    public function prepare($cwd)
    {
        $this->code = 200;
        $this->cwd  = str_replace('\\', '/', trim($cwd, '\\/').'/');

        $this->category = null;
        if (preg_match('/^[^\/]+/', $this->cwd, $matches)) {
            $this->category = $matches[0];
        }

        return $this;
    }

    public function diskPath($file = null)
    {
        if (! $file) {
            return $this->cwd;
        }

        if (strncasecmp($file, $this->cwd, strlen($this->cwd)) !== 0) {
            return $this->cwd.ltrim($file, '\\/');
        }

        return $file;
    }

    public function path($file)
    {
        return $this->disk->path($this->diskPath($file));
    }

    protected function thumb($file)
    {
        return $this->diskPath('_thumbs/'.basename($file));
    }

    public function under($path)
    {
        $path = $this->diskPath($path);

        $folders = [];
        foreach ($this->disk->directories($path) as $dir) {
            if (basename($dir) !== '_thumbs') {
                $folders[] = $this->dirInfo($dir);
            }
        }

        $files = [];
        foreach ($this->disk->files($path) as $file) {
            if (basename($file) !== '.gitignore') {
                $files[] = $this->fileInfo($file);
            }
        }

        return compact('folders', 'files');
    }

    public function dirInfo($dir)
    {
        return [
            'name' => basename($dir),
        ];
    }

    public function fileInfo($file)
    {
        $info = [
            'name'      => basename($file),
            'mimeType'  => $this->disk->mimeType($file),
            'size'      => $this->disk->size($file),
            'modified'  => $this->disk->lastModified($file),
            'thumb'     => $this->disk->exists($this->thumb($file)),
        ];

        if (Str::startsWith($info['mimeType'], 'image')) {
            $dimenssion = getimagesize($this->path($file));
            $info['width'] = $dimenssion[0];
            $info['height'] = $dimenssion[1];
        }

        return $info;
    }

    public function save($files)
    {
        if ($files instanceof UploadedFile) {
            return $this->saveUploadedFile($files);
        }

        $message = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $message = array_merge($message, $this->saveUploadedFile($file));
            }
        }

        return $message;
    }

    protected function saveUploadedFile(UploadedFile $file)
    {
        if (! $file->isValid()) {
            return [];
        }

        // 文件名
        $name = $file->getClientOriginalName();
        $legalName = $this->formatName($name);
        if ($this->disk->exists($this->diskPath($legalName))) {
            return [$name => false];
        }

        // 保存文件
        $file->storePubliclyAs($this->cwd, $legalName, [
            'disk' => 'media',
        ]);

        // 生成缩略图
        if (Str::startsWith($file->getMimeType(), 'image')) {
            $thumb = $this->path($this->thumb($legalName));
            $this->mkdir('_thumbs/');
            Image::make($file)->widen(200)->save($thumb);
        }

        return [$name => $legalName];
    }

    protected function formatName($name)
    {
        // 全部转为小写
        $name = strtolower($name);

        // 文件名和扩展名
        $filename = pathinfo($name, PATHINFO_FILENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        // 替换双字节字符为下划线
        $filename = preg_replace('/[^\x00-\xff]/u', '_', $filename);

        // 替换其它非法字符为下划线
        $filename = preg_replace('/[^a-z0-9\-_]/', '_', $filename);

        return $filename.'.'.$ext;
    }

    public function mkdir($dir)
    {
        $path = $this->diskPath($dir);
        if (! $this->disk->exists($path)) {
            $this->disk->makeDirectory($path);
        }
        return [$path => true];
    }

    public function rename($old, $new)
    {
        $old = $this->diskPath($old);
        $new = $this->diskPath($new);

        if ($this->disk->exists($new)) {
            $this->code = 202;
            return '文件已存在';
        }

        $this->disk->move($old, $new);

        $thumb = $this->thumb($old);
        if ($this->disk->exists($thumb)) {
            $this->disk->move($thumb, $this->thumb($new));
        }

        return '';
    }

    public function delete($files)
    {
        if (is_string($files)) {
            $files = [$files];
        }

        $message = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $message = array_merge($message, $this->deleteFile($file));
            }
        }

        return $message;
    }

    protected function deleteFile($file)
    {
        if (!$file) {
            return [];
        }
        $file = $this->diskPath($file);

        $this->disk->delete($file);

        $thumb = $this->thumb($file);
        if ($this->disk->exists($thumb)) {
            $this->disk->delete($thumb);
        }

        return [$file => true];
    }
}
