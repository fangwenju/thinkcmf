<?php
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use app\user\model\UserModel;

class QuickController extends HomeBaseController
{

    /**
     * 登录
     */
    public function index()
    {
        $redirect = $this->request->post("redirect");
        if (empty($redirect)) {
            $redirect = $this->request->server('HTTP_REFERER');
        } else {
            $redirect = base64_decode($redirect);
        }
        session('login_http_referer', $redirect);
        if (cmf_is_user_login()) { //已经登录时直接跳到首页
            return redirect($this->request->root() . '/');
        } else {
            return $this->fetch(":quick");
        }
    }

    /**
     * 登录验证提交
     */
    public function doLogin()
    {
        if ($this->request->isPost()) {

            $data = $this->request->post();
            if (empty($data['verification_code'])) {
                $this->error('请输入手机验证码');
            }

            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code'], true);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }

            $userModel = new UserModel();
            $log       = mobile_quick($data['username']);

            $session_login_http_referer = session('login_http_referer');
            $redirect                   = empty($session_login_http_referer) ? $this->request->root() : $session_login_http_referer;
            switch ($log) {
                case 0:
                    cmf_user_action('login');
                    $this->success('登录成功', $redirect);
                    break;
                case 3:
                    $this->error('账号被禁止访问系统');
                    break;
                default :
                    $this->error('未受理的请求');
            }
        } else {
            $this->error("请求错误");
        }
    }
}