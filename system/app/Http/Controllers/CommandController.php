<?php

namespace App\Http\Controllers;

use App\Models\Index;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
     * 检索关键词
     *
     * @return array
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
}
