<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use app\portal\service\PostService;
use think\Db;

class PageController extends HomeBaseController
{
    public function index()
    {
        $postService = new PostService();
        $pageId      = $this->request->param('id', 0, 'intval');
        $page        = $postService->publishedPage($pageId);

        if (!empty($page)) {
            $this->assign('page', $page);
            $more = $page['more'];
            $tplName = empty($more['template']) ? 'page' : $more['template'];

        } elseif ( !empty($this->request->param('introducer'))) {
            $tplName = 'page_recovery';

        } else {

            abort(404, ' 页面不存在!');
        }

        //如果是回收预约页面则查询重量设置信息
        if ($tplName == 'page_recovery') {
            $this->appointment();
        }

        return $this->fetch("/$tplName");
    }

    /**
     * 预约回收
     * @return [type] [description]
     */
    public function appointment()
    {
        $params = $this->request->param();

        if ($this->request->isPost()) {
            $message = '';
            if (empty($params['name'])) {
                $message = '联系人不能为空';
            } elseif (empty($params['mobile']) || !preg_match("/^1[34578]\d{9}$/", $params['mobile'])) {
                $message = '手机号码不规范';
            } elseif ($params['province'] == '-1') {
                $message = '请选择省';
            } elseif ($params['city'] == '-1') {
                $message = '请选择市';
            } elseif ($params['area'] == '-1') {
                $message = '请选择区';
            } elseif ( empty($params['address']) ) {
                $message = '请填写详细地址';
            } elseif ( empty($params['appointment_time'] ) ) {
                $message = '请选择期望上门时间';
            } elseif ( strtotime($params['appointment_time']) < time() ) {
                $message = '上门时间必须大于当前时间';
            } elseif ( cmf_is_user_login() === false ) {
                //未登录用户需做手机验证
                if (empty($params['verification_code'])) {
                    $message = '请输入手机验证码';
                } else {
                    $message = cmf_check_verification_code($params['mobile'], $params['verification_code'], true);
                }

                //注册用户
                $recommender = 0;//推荐人信息
                $source = 0;//来源
                if ( !empty($params['introducer']) ) {
                    $introducer = decode_number($params['introducer']);
                    $recommender = substr($introducer, 0, -1);
                    $source = substr($introducer, -1);
                }
                $data = [
                    'mobile'      => $params['mobile'],
                    'recommender' => $recommender,
                    'source'      => $source,
                ];
                register_quick($data);
            }

            if (!empty($message)) {
                $this->error($message);
            }

            $params['type']             = 3;
            $params['status']           = 1;
            $params['create_time']      = time();
            $params['user_id']          = cmf_get_current_user_id();
            $params['appointment_time'] = strtotime($params['appointment_time']);

            $res = Db::name('appointment')->insertGetId($params);
            if ($res) {
                $this->success("提交成功！",url('/'));
            }
            $this->error('提交失败');
        }

        $introducer = empty($params['introducer'])?0:$params['introducer'];
        $this->assign('introducer', $introducer);
        $weights = Db::name('weight_interval')->select();
        $this->assign('weights', $weights);
    }

}
