<?php
/**
 * SpeedAdmin
 * ============================================================================
 * 版权所有 2018-2027 SpeedAdmin，并保留所有权利。
 * 网站地址: https://www.SpeedAdmin.cn
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2019/8/2
 */
namespace app\backend\controller;
use app\backend\lib\Auth;
use app\common\controller\Backend;
use speed\helper\SignHelper;
use think\facade\Request;

class Login extends Backend {

    /*
     * 登录
     */
    public function initialize()
    {

        parent::initialize(); // TODO: Change the autogenerated stub

    }
    public function index(){
        if (!Request::post()) {
            $admin= session('admin');
            $admin_sign= session('admin.token') == SignHelper::authSign($admin) ? $admin['id'] : 0;
            // 签名验证
            if ($admin && $admin_sign) {
                 redirect('index/index');
            }

            $view = ['loginbg'=> "/static/backend/images/admin-bg.jpg"];
            return view('',$view);

        } else {
            $username = $this->request->post('username', '', 'speed\helper\StringHelper::filterWords');
            $password = $this->request->post('password', '', 'speed\helper\StringHelper::filterWords');
            $captcha = $this->request->post('captcha', '', 'speed\helper\StringHelper::filterWords');
            $rememberMe = $this->request->post('rememberMe');
            // 用户信息验证
            try {
                if(!captcha_check($captcha)){
                    throw new \Exception(lang('captcha error'));
                }
                $auth = new Auth();
                $res = $auth->checkLogin($username, $password,$rememberMe);
            } catch (\Exception $e) {

                 $this->error(lang('login fail')."：{$e->getMessage()}");
            }
            $this->success(lang('login success').'...',url('index/index'));
        }
    }


    public function verify()
    {
        return parent::verify(); // TODO: Change the autogenerated stub
    }

}