<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class MediaController extends Controller
{
    protected $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function index()
    {
        return view('media.index');
    }

    public function select()
    {
        return view('media.select');
    }

    public function under(Request $request)
    {
        $cwd = $request->input('cwd');
        $path = $request->file('path');

        $entries = $this->media->prepare($cwd)->under($path);
        return Response::make($entries, $this->media->getCode());
    }

    public function upload(Request $request)
    {
        $cwd = $request->input('cwd');
        $files = $request->file('files');

        $message = $this->media->prepare($cwd)->save($files);

        return Response::make($message, $this->media->getCode());
    }

    public function makeDir(Request $request)
    {
        $cwd = $request->input('cwd');
        $name = $request->input('name');

        $message = $this->media->prepare($cwd)->mkdir($name);
        $code = $this->media->getCode();

        return Response::make($message, $code);
    }

    public function rename(Request $request, $type = 'files')
    {
        $cwd = $request->input('cwd');
        $old = $request->input('old_name');
        $new = $request->input('new_name');

        if ($type === 'files' || $type === 'images') {
            $message = $this->media->prepare($cwd)->rename($old, $new);
            $code = $this->media->getCode();
        } else {
            $message = '暂无法重命名文件夹';
            $code = 405;
        }

        return response($message, $code);
    }

    public function delete(Request $request, $type = 'files')
    {
        $cwd = $request->input('cwd');
        $files = $request->input('name');

        if ($type === 'files' || $type === 'images') {
            $message = $this->media->prepare($cwd)->delete($files);
            $code = $this->media->getCode();
        } else {
            $message = '暂无法删除文件夹';
            $code = 405;
        }

        return Response::make($message, $code);
    }
}
