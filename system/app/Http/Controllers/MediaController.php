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
        return view('admin::media.index');
    }

    public function select()
    {
        return view('admin::media.select');
    }

    public function under(Request $request)
    {
        $path = $request->input('path');
        return Response::make($this->media->under($path));
    }

    public function upload(Request $request)
    {
        $path = $request->input('path');
        $files = $request->file('files');
        // $file->getClientMimeType();

        $errors = $this->media->save($path, $files);
        return Response::make($errors);
    }

    public function createFolder(Request $request)
    {
        $path = $request->input('path');
        $folder = $request->input('folder');

        $errors = $this->media->mkdir($path.'/'.$folder);
        return Response::make($errors);
    }

    public function renameFile(Request $request)
    {
        $path = $request->input('path').'/';
        $old_name = $path.$request->input('old_name');
        $new_name = $path.$request->input('new_name');

        $errors = $this->media->rename($old_name, $new_name);
        return Response::make($errors);
    }

    public function deleteFile(Request $request)
    {
        $path = $request->input('path');
        $file = $request->input('file');

        $errors = $this->media->delete($path, $file);
        return Response::make($errors);
    }
}
