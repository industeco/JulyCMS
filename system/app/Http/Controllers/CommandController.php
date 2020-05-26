<?php

namespace App\Http\Controllers;

use App\Mail\NewMessage;
use App\Models\Catalog;
use App\Models\Index;
use App\Models\Node;
use App\Models\NodeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommandController extends Controller
{
    /**
     * 清除缓存
     *
     * @return boolean
     */
    public function clearCache()
    {
        // 清除缓存
        Artisan::call('cache:clear');

        // 清空 storage/pages 目录
        $disk = Storage::disk('storage');
        foreach ($disk->files('pages') as $file) {
            if ($file !== 'pages/.gitignore') {
                $disk->delete($file);
            }
        }
        foreach ($disk->directories('pages') as $dir) {
            $disk->deleteDirectory($dir);
        }

        return true;
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
        if (config('jc.multi_language')) {
            $langcodes = available_langcodes('site_page');
        } else {
            $langcodes = [langcode('site_page')];
        }
        foreach ($langcodes as $langcode) {
            $sitemap = build_google_sitemap($langcode);
            Storage::disk('storage')->put('pages/'.$langcode.'/sitemap.xml', $sitemap);
        }

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

    public function adminSearch(Request $request)
    {
        $keywords = $request->input('keywords');

        $results = [];
        foreach (NodeField::fetchAll() as $field) {
            $results = array_merge($results, $field->search($keywords));
        }

        $titles = [];
        foreach (NodeField::fetch('title')->records() as $record) {
            $key = $record->node_id.'/'.$record->langcode;
            $titles[$key] = $record->title_value;
        }

        $nodes = Node::fetchAll()->keyBy('id');
        foreach ($results as &$result) {
            $key = $result['node_id'].'/'.$result['langcode'];
            $result['node_title'] = $titles[$key] ?? null;
            $result['original_langcode'] = $nodes->get($result['node_id'])->langcode;
        }

        return view_with_langcode('admin::search', [
            'keywords' => $keywords,
            'results' => $results,
        ]);
    }

    public function findInvalidLinks()
    {
        if (config('jc.multi_language')) {
            $langcodes = available_langcodes('site_page');
        } else {
            $langcodes = [langcode('site_page')];
        }
        $invalidLinks = [];
        foreach (Node::fetchAll() as $node) {
            foreach ($langcodes as $langcode) {
                $invalidLinks = array_merge($invalidLinks, $node->findInvalidLinks($langcode));
            }
        }

        return view_with_langcode('admin::invalid_links', [
            'invalidLinks' => $invalidLinks,
        ]);
    }
}
