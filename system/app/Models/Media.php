<?php

namespace App\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class Media
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('media');
    }

    public function under($path)
    {
        $folders = [];
        foreach ($this->disk->directories($path) as $dir) {
            $info = $this->dirInfo($dir);
            if ($info['name'] !== '.thumbs') {
                $folders[] = $info;
            }
        }

        $files = [];
        foreach ($this->disk->files($path) as $file) {
            $files[] = $this->fileInfo($file);
        }

        return compact('folders', 'files');
    }

    public function dirInfo($dir)
    {
        return [
            'name' => basename($dir),
        ];
    }

    public function path($path)
    {
        return $this->disk->path($path);
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

    public function save($path, $files)
    {
        if ($files instanceof UploadedFile) {
            return $this->saveUploadedFile($path, $files);
        }

        $errors = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $errors = array_merge($errors, $this->saveUploadedFile($path, $file));
            }
        }

        return $errors;
    }

    protected function saveUploadedFile($path, UploadedFile $file)
    {
        if (! $file->isValid()) {
            return [];
        }

        // 文件名
        $name = $file->getClientOriginalName();

        // 保存文件
        $file->storePubliclyAs($path, $name, [
            'disk' => 'media',
        ]);

        // 生成缩略图
        if (Str::startsWith($file->getMimeType(), 'image')) {
            $thumb = $path.'/.thumbs/'.$name;
            $this->mkdir(dirname($thumb));
            Image::make($file)->widen(200)->save($this->path($thumb));
        }

        return [$name => true];
    }

    public function mkdir($path)
    {
        if (! $this->disk->exists($path)) {
            $this->disk->makeDirectory($path);
        }
        return [$path => true];
    }

    protected function thumb($file)
    {
        return dirname($file).'/.thumbs/'.basename($file);
    }

    public function rename($old, $new)
    {
        $this->disk->move($old, $new);

        $thumb = $this->thumb($old);
        if ($this->disk->exists($thumb)) {
            $newThumb = $this->thumb($new);
            $this->mkdir(dirname($newThumb));
            $this->disk->move($thumb, $newThumb);
        }

        return [$new => true];
    }

    public function delete($path, $files)
    {
        $path = rtrim($path, '\\/').'/';

        if (is_string($files)) {
            $files = [$files];
        }

        $errors = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $errors = array_merge($errors, $this->deleteFile($path.$file));
            }
        }

        return $errors;
    }

    public function deleteFile($file)
    {
        if ($file == 'images/' || $file == 'files/') {
            return [$file => false];
        }

        $this->disk->delete($file);

        $thumb = $this->thumb($file);
        if ($this->disk->exists($thumb)) {
            $this->disk->delete($thumb);
        }

        return [$file => true];
    }
}
