<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
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

    public function under(Request $request)
    {
        $path = $request->input('path');
        return Response::make($this->media->under($path));
    }
}
