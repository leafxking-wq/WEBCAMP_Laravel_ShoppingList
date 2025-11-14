<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompletedShoppingLists as CompletedShoppingListModel;

class CompletedShoppingListController extends Controller
{
    /**
     * 完了タスク一覧ページ を表示する
     * 
     * @return \Illuminate\View\View
     */
    public function list()
    {
        
        // 1Page辺りの表示アイテム数を設定
        $per_page = 15;
        
        // 一覧の取得
        $list = $this->getListBuilder()
                    ->orderBy('name')
                    ->orderBy('created_at')
                    ->paginate($per_page);
                        // ->get();
        /*
        $sql =  $this->getListBuilder()
            ->toSql();
        //echo "<pre>\n"; var_dump($sql, $list); exit;
        var_dump($sql);
        */
        
        return view('shopping.completed_shopping_list', ['list' => $list]);
    }
    
    /**
     * 「単一のタスク」Modelの取得
     */
    protected function getShoppingListModel($shopping_id)
    {
        // shopping_idのレコードを取得する
        $shopping = CompletedShoppingListModel::find($shopping_id);
        if ($shopping === null) {
            return null;
        }
        // 本人以外のタスクならNGとする
        if ($shopping->user_id !== Auth::id()) {
            return null;
        }
        
        return $shopping;
    }

    /**
     * 「単一のタスク」の表示
     */
    protected function singleShoppingRender($shopping_id, $template_name)
    {
        // shopping_idのレコードを取得する
        $shopping = $this->getShoppingListModel($shopping_id);
        if ($shopping === null) {
            return redirect('/shoppping/list');
        }

        // テンプレートに「取得したレコード」の情報を渡す
        return view($template_name, ['shopping' => $shopping]);
    }
    
    /**
     * 一覧用の Illuminate\Database\Eloquent\Builder インスタンスの取得
     */
    protected function getListBuilder()
    {
        return CompletedShoppingListModel::where('user_id', Auth::id());
    }        
}