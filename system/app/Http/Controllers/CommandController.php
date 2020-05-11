<?php

namespace App\Http\Controllers;

use App\Mail\NewMessage;
use App\Models\Catalog;
use App\Models\Index;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CommandController extends Controller
{
    /**
     * 清除缓存
     *
     * @return boolean
     */
    public function clearCache()
    {
        return Artisan::call('cache:clear');
    }

    /**
     * 重建索引
     *
     * @return boolean
     */
    public function rebuildIndex()
    {
        return Index::rebuild();
    }

    /**
     * 发送 New Message 邮件
     */
    public function newMessage(Request $request)
    {
        $msg = new NewMessage($request);
        if ($msg->send()) {
            $reply = 'Message sent! We will contact you soon.';
        } else {
            $reply = $msg->getError();
        }

        return view('admin::mail', ['message' => $reply]);
    }

    /**
     * 检索关键词
     *
     * @return string
     */
    public function search(Request $request)
    {
        Node::fetchAll();

        $results = Index::search($request->input('keywords'));
        foreach ($results['results'] as &$result) {
            $result['node'] = Node::fetch($result['node_id']);
        }
        $results['title'] = 'Search';
        $results['meta_title'] = 'Search Result';
        $results['meta_keywords'] = 'Search';
        $results['meta_description'] = 'Search Result';

        $twig = twig('default/template', true);
        return $twig->render('search.twig', $results);
    }

    /**
     * 生成谷歌站点地图（.xml 文件）
     */
    public function buildGoogleSitemap()
    {
        $urls = Node::urls();
        $sitemap = build_google_sitemap($urls);
        Storage::disk('public')->put('sitemap.xml', $sitemap);
        return true;
    }

    /**
     * 修改后台用户密码
     */
    public function changeAdminPassword(Request $request)
    {
        if (config('app.demo')) {
            return response('');
        }

        $user = Auth::guard('admin')->user();

        $valid = Hash::check($request->input('current_password'), $user->getAuthPassword());
        if (! $valid) {
            return response('', 202);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();
        Auth::guard('admin')->login($user);

        return response('');
    }
}
