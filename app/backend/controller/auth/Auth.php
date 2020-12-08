<?php
/**
 * FunAdmin
 * ============================================================================
 * 版权所有 2017-2028 FunAdmin，并保留所有权利。
 * 网站地址: https://www.FunAdmin.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2017/8/2
 */

namespace app\backend\controller\auth;

use app\common\controller\Backend;
use app\backend\model\AuthRule;
use app\common\traits\Curd;
use think\App;
use think\facade\Cache;
use think\facade\View;

class Auth extends Backend
{
    use Curd;
    public $uid;
    public function __construct(App $app)
    {
        parent::__construct($app);
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->modelClass = new AuthRule();
        $this->uid = session('admin.id');
    }


    /**
     * @return array|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * 权限列表
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $uid = $this->uid;
            $list = Cache::get('ruleList_' . $uid);
            if (!$list) {
                $list = $this->modelClass
                    ->order('pid asc,sort asc')
                    ->select()->toArray();
                foreach ($list as $k => &$v) {
                    $v['lay_is_open'] = true;
                    $v['title'] = lang($v['title']);
                }
                Cache::set('ruleList_' . $uid, $list, 3600);
            }
            $result = ['code' => 0, 'msg' => lang('get info success'), 'data' => $list, 'count' => count($list), 'is' => true, 'tip' => '操作成功'];
            return json($result);
        }
        return view();
    }

    // 权限增加
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['title'])) {
                $this->error(lang('rule name cannot null'));
            }
            if (empty($post['sort'])) {
                $this->error(lang('sort') . lang(' cannot null'));
            }
            $post['icon'] = $post['icon'] ? 'layui-icon '.$post['icon'] : 'layui-icon layui-icon-diamond';
            $post['href'] = trim($post['href'], '/');
            $rule = [
                'href'=>'require|unique:auth_rule',
                'title'=>'require'
            ];
            $this->validate($post, $rule);
            if ($this->modelClass->save($post)) {
                $this->success(lang('operation success'));
            } else {
                $this->error(lang('operation failed'));
            }
        } else {
            $list = $this->modelClass
                ->order('sort ASC')
                ->select();
            $list = $this->modelClass->cateTree($list);
            $view = [
                'formData' => null,
                'ruleList' => $list,
            ];
            View::assign($view);
            return view();
        }
    }

    //权限修改
    public function edit()
    {
        if (request()->isAjax()) {
            $post = $this->request->param();
            $post['icon'] = $post['icon'] ? 'layui-icon '.$post['icon'] : 'layui-icon layui-icon-diamond';
            $model = $this->findModel($this->request->param('id'));
            $model->save($post);
            $this->success(lang('operation success'));
        } else {
            $list = $this->modelClass
                ->order('sort asc')
                ->select();
            $list = $this->modelClass->cateTree($list);
            $id = $this->request->param('id');
            $one = $this->modelClass->find($id)->toArray();
            $one['icon'] = $one['icon'] ? trim(substr($one['icon'],10),' ') : 'layui-icon layui-icon-diamond';
            $view = [
                'formData' => $one,
                'ruleList' => $list,
            ];
            View::assign($view);
            return view('add');
        }
    }
    //子权限添加
    public function child()
    {
        if (request()->isAjax()) {
            $post = $this->request->post();
            $post['icon'] = $post['icon'] ? $post['icon'] : 'layui-iconpicker-icon layui-unselect';
            $rule = [
                'href'=>'require|unique:auth_rule',
                'title'=>'require'
            ];
            $this->validate($post, $rule);
            $save = $this->modelClass->save($post);
            Cache::delete('ruleList_' . $this->uid);
            $save ? $this->success(lang('operation success')) : $this->error(lang('operation failed'));
        } else {
            $ruleList =$this->modelClass
                ->order('sort asc')
                ->select();
            $ruleList = $this->modelClass->cateTree($ruleList);
            $parent = $this->modelClass->find($this->request->param('id'));
            $view = [
                'formData' => '',
                'ruleList' => $ruleList,
                'parent' => $parent,
            ];
            View::assign($view);
            return view('child');
        }
    }

    // 权限删除
    public function delete()
    {
        $ids = $this->request->param('ids')?$this->request->param('ids'):$this->request->param('id');
        $list = $this->modelClass->find($ids);
        $child = $this->modelClass->where('pid', 'in', $ids)->select();
        if (!empty($child->toArray())) {
            $this->error(lang('delete child first'));
        } elseif (empty($child->toArray())) {
            $list->delete();
            $this->success(lang('operation success'));
        } else {
            $this->error('id' . lang('not exist'));
        }
    }

    public function modify()
    {
        $uid = session('admin.id');
        $id = $this->request->param('id');
        $field = $this->request->param('field');
        $value = $this->request->param('value');
        if($id){
            if(!$this->allowModifyFileds = ['*'] and !in_array($field, $this->allowModifyFileds)){

                $this->error(lang('Field Is Not Allow Modify：' . $field));
            }
            $model = $this->findModel($id);
            if (!$model) {
                $this->error(lang('Data Is Not 存在'));
            }
            $model->$field = $value;
            $save = $model->save();
            Cache::delete('ruleList_' . $uid);
            $save ? $this->success(lang('Modify success')) :  $this->error(lang("Modify Failed"));

        }else{
            $this->error(lang('Invalid data'));
        }
    }


}