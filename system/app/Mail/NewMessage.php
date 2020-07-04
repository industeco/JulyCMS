<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessage
{
    /**
     * 表单数据
     *
     * @var array
     */
    protected $data = [];

    protected $ip;

    /**
     * 错误信息
     *
     * @var string
     */
    protected $error = null;

    /**
     * 换行符
     *
     * @var string
     */
    protected $eol = "\n";

    /**
     * 可用字段
     *
     * @var array
     */
    protected $fields = [
        'email' => 'E-mail',
        'name' => 'Name',
        'message' => 'Message',
        'phone' => 'Phone',
        'company' => 'Company',
        'user_agent' => 'UserAgent',
    ];

    /**
     * 必填字段
     *
     * @var array
     */
    protected $required_fields = [
        'email', 'message', 'name',
    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->data = $request->all();
        $this->ip = $request->ip();

        $ua = $request->userAgent();
        $this->data['user_agent'] = $request->input('user_agent') ?: $this->getUserAgent($ua);

        $os = strtoupper(substr(PHP_OS,0,3));
        if ($os === 'WIN') {
            $this->eol = "\r\n";
        } elseif ($os === 'MAC') {
            $this->eol = "\r";
        }
    }

    public function send()
    {
        if ($this->validate()) {
            $to = config('mail.to.address');
            $subject = 'New Message:';
            $mailBody = $this->getMailBody();
            Log::info(compact('to', 'subject', 'mailBody'));

            if (config('app.demo')) {
                return true;
            }

            if (mail($to, $subject, $mailBody)) {
                return true;
            } else {
                $this->error = 'Failed!';
            }
        }

        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function getUserAgent($ua = null)
    {
        return user_agent($ua);
    }

    protected function validate()
    {
        // 检查必填字段
        foreach ($this->required_fields as $field) {
            if (empty($this->data[$field] ?? null)) {
                $this->error = 'Field "message" is required.';
                return false;
            }
        }

        // 验证 message 字段
        $message = $this->data['message'];
        if(preg_match("/(https?|ftp):\/\/|www\./i", $message)) {
            $this->error = 'URL is not allowed in "message".';
            return false;
        }

        // 验证 name 字段
        $name = $this->data['name'];

        if(preg_match("/(https?|ftp):\/\/|www\./i", $name)){
            $this->error = 'URL is not allowed in "name".';
            return false;
        }

        if(strlen($name) > 35){
            $this->error = 'Name should be shorter than 35 characters.';
            return false;
        }

        // 验证 email 字段
        $email = $this->data['email'];
        $preg_email = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";

        if(!preg_match($preg_email, $email)){
            $this->error = 'Email address is not valid.';
            return false;
        }

        // 验证 phone 字段
        if ($phone = $this->data['phone'] ?? null) {
            if (strlen($phone) > 40) {
                $this->error = 'Phone should be shorter than 40 characters.';
                return false;
            }
        }

        return true;
    }

    protected function getMailBody()
    {
        $content ='';
        foreach ($this->fields as $key => $label) {
            $value = $this->data[$key] ?? null;
            $content .= $label.': '.$value.$this->eol;
        }
        $content .= $this->eol;

        if ($tracks = $this->getTracks()) {
            $content .= $tracks.$this->eol;
        }

        $content .= 'IP: '.$this->ip.' (https://www.iplocation.net/)'.$this->eol;

        return $content;
    }

    protected function getTracks()
    {
        if ($tracks = $this->data['track_report'] ?? null) {
            $tracks = json_decode(stripslashes($tracks), true);
        }

        $report = '';
        if (! empty($tracks)) {
            $report .= 'Traces: '.$this->eol;

            $first = array_splice($tracks['trace'], -2);
            $last = array_splice($tracks['trace'], 0, 15);

            foreach ($last as $record) {
                $report .= '( '.strval(intval($record[1]/60)).'m '.strval(intval($record[1]%60)).'s ) '.$record[0].$this->eol;
            }

            if (! empty($tracks['trace'])) {
                $report .= '...'.$this->eol;
            }

            foreach ($first as $record) {
                $report .= '( '.strval(intval($record[1]/60)).'m '.strval(intval($record[1]%60)).'s ) '.$record[0].$this->eol;
            }

            $report .= '(Refer) '.$tracks['refer'].$this->eol;
        }

        return $report;
    }
}
