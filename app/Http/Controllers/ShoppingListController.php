<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ShoppingListRegisterPostRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\shopping_lists as ShoppingModel;
use Illuminate\Support\Facades\DB;
use App\Models\CompletedShoppingLists as CompletedShoppingListsModel;

class ShoppingListController extends Controller
{
    /**
     * 一覧ページ を表示する
     * 
     * @return \Illuminate\View\View
     */
    public function list()
    {
        // 1Page辺りの表示アイテム数を設定
        $per_page = 15;

        // 一覧の取得
        $list = ShoppingModel::where('user_id', Auth::id())
                                ->orderBy('name')
                                ->paginate($per_page);
                                //->get();
        //$sql = ShoppingModel::where('user_id', Auth::id()
        //                      ->orderBy('created_at'))
        //                      ->toSql();
        //echo "<pre>\n"; var_dump($sql, $list); exit;
        return view('shopping.list', ['list' => $list]);
    }

    /**
     * 「買うもの」の新規登録
     */
    public function register(ShoppingListRegisterPostRequest $request)
    {
        // validate済みのデータの取得
        $datum = $request->validated();
        //
        //$user = Auth::user();
        //$id = Auth::id();
        //var_dump($datum, $user, $id); exit;

        // user_idの追加
        $datum['user_id'] = Auth::id();

        //テーブルへのINSERT
        try {
            $r = ShoppingModel::create($datum);
            //var_dump($r); exit;
        } catch(\Throwable $e) {
            // XXX 本当はログに書く等の処理をする。今回は一端「出力する」だけ
            echo $e->getMessage();
            exit;
        }

        //　登録成功
        $request->session()->flash('front.shopping_register_success', true);

        //　リダイレクト
        return redirect('/shopping/list');
    }

    /**
     * 削除処理
     */
    public function delete(Request $request, $shopping_id)
    {
        // shopping_idのレコードを取得する
        $shopping = $this->getShoppingModel($shopping_id);

        // タスクを削除する
        if ($shopping !== null) {
            $shopping->delete();
            $request->session()->flash('front.shopping_delete_success', true);
        }

        // 一覧に遷移する
        return redirect('/shopping/list');
    }

    /**
     * 完了処理
     */
    public function complete(Request $request, $shopping_id)
    {
        /* 「買うもの」を完了テーブルに移動させる */
        try {
            // トランザクション開始
            DB::beginTransaction();

            // shopping_idのレコードを取得する
            $shopping = $this->getShoppingModel($shopping_id);
            if ($shopping === null) {
                // shopping_idが不正なのでトランザクション終了
                throw new \Exception('');
            }

            //var_dump($shopping->toArray()); exit;
            // shopping_lists側を削除する
            $shopping->delete();

            // completed_shopping_lists側にinsertする
            $dask_datum = $shopping->toArray();
            unset($dask_datum['created_at']);
            unset($dask_datum['updated_at']);
            $r = CompletedShoppingListsModel::create($dask_datum);
            if ($r === null) {
                // insertで失敗したのでトランザクション終了
                throw new \Exception('');
            }
            // echo '処理成功'; exit;

            // トランザクション終了
            DB::commit();
            // 完了メッセージ出力
            $request->session()->flash('front.shopping_completed_success', true);
        } catch(\Throwable $e) {
            var_dump($e->getMessage()); exit;
            // トランザクション異常終了
            DB::rollBack();
            // 完了失敗メッセージ出力
            $request->session()->flash('front.shopping_completed_failure', true);
        }

        // 一覧に遷移する
        return redirect('/shopping/list');
    }

    /**
     * 「単一のタスク」Modelの取得
     */
    protected function getShoppingModel($shopping_id)
    {
        // shopping_idのレコードを取得する
        $shopping = ShoppingModel::find($shopping_id);
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
        $shoppping = $this->getShoppingModel($shopping_id);
        if ($shopping === null) {
            return redirect('/shopping/list');
        }

        // テンプレートに「取得したレコード」の情報を渡す
        return view($template_name, ['shopping' => $shopping]);
    }
}
