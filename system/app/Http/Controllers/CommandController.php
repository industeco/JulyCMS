<?php

namespace App\Http\Controllers;

use App\Actions\GoogleSitemap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use July\Node\Catalog;
use July\Node\Node;
use July\Node\NodeField;
use Specs\Spec;

class CommandController extends Controller
{
    // /**
    //  * 清除缓存
    //  *
    //  * @return boolean
    //  */
    // public function clearCache()
    // {
    //     // 清除缓存
    //     Artisan::call('cache:clear');

    //     // 清除视图缓存
    //     Artisan::call('view:clear');

    //     return true;
    // }

    /**
     * 修改后台用户密码
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeAdminPassword(Request $request)
    {
        if (config('app.is_demo')) {
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

    /**
     * 搜索后台数据库
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     *
     * @todo 重构
     */
    public function searchDatabase(Request $request)
    {
        $keywords = $request->input('keywords');

        $results = [];
        foreach (NodeField::all() as $field) {
            $results = array_merge($results, $field->searchValue($keywords));
        }

        $titles = [];
        foreach (NodeField::carry('title')->getRecords() as $record) {
            $key = $record->node_id.'/'.$record->langcode;
            $titles[$key] = $record->title_value;
        }

        $nodes = Node::carryAll()->keyBy('id');
        foreach ($results as &$result) {
            $key = $result['node_id'].'/'.$result['langcode'];
            $result['node_title'] = $titles[$key] ?? null;
            $result['original_langcode'] = $nodes->get($result['node_id'])->langcode;
        }

        return view('search', [
            'keywords' => $keywords,
            'results' => $results,
            'langcode' => langcode('content'),
        ]);
    }

    // /**
    //  * 生成谷歌站点地图（.xml 文件）
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function buildGoogleSitemap()
    // {
    //     $urls = [];

    //     // 节点网址
    //     $aliases = NodeField::find('url')->getValueModel()->values();
    //     foreach (Catalog::default()->get_nodes() as $node) {
    //         $url = $aliases[$node->getKey()] ?? null;
    //         if (!$url || $url === '/404.html') {
    //             continue;
    //         }
    //         $urls[$url] = $node->fetchHtml();
    //     }

    //     // 规格网址
    //     foreach (Spec::all() as $spec) {
    //         foreach ($spec->getRecords() as $record) {
    //             $urls['/specs/'.$spec->getKey().'/records/'.$record['id']] = null;
    //         }
    //     }

    //     // 生成谷歌站点地图
    //     Storage::disk('public')->put('sitemap.xml', GoogleSitemap::render($urls));

    //     return response('');
    // }
}
