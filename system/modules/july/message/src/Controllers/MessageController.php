<?php

namespace July\Message\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\Lang;
use Illuminate\Http\Request;
use July\Message\Message;
use July\Message\MessageForm;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $keys = ['id','mold_id','updated_at','created_at'];
        $data = [
            'models' => Message::index($keys),
            'context' => [
                'message_forms' => MessageForm::query()->pluck('label', 'id')->all(),
                'languages' => Lang::getTranslatableLangnames(),
            ],
        ];

        return view('message::message.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Message\MessageForm  $messageForm
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request, MessageForm $messageForm)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Message\Message  $content
     * @return \Illuminate\Http\Response
     */
    public function show(Message $content)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \July\Message\MessageForm  $messageForm
     * @return \Illuminate\Http\Response
     */
    public function create(MessageForm $messageForm)
    {
        //
    }

    /**
     * 展示编辑或翻译界面
     *
     * @param  \July\Message\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Message\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Message\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        // Log::info($node->id);
        $message->delete();

        return response('');
    }
}
