<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class LogController extends AdminBaseController
{
    public function index()
    {
        $params = input('request.');
        $where = [];
        if (!empty($params['user_name'])) {
            $where['user_name'] = ['like', '%'.$params['user_name'].'%'];
        }
        if (!empty($params['start_time'])) {
            $start_time = strtotime($params['start_time']);
            $where['create_time'] = ['>=', $start_time];
        }
        if (!empty($params['end_time'])) {
            $end_time = strtotime($params['end_time'])+24*3600;
            $where['create_time'] =['<', $end_time];
        }
        if (isset($start_time) && isset($end_time)) {
            $where['create_time'] =['between',[$start_time,$end_time]];
        }

        $loglist = Db::name('admin_log')->where($where)->order("id DESC")->paginate(15);
        $page = $loglist->render();

        $user = Db::name('user')->select();
        $this->assign("user", $page);
        $this->assign("page", $page);
        $this->assign("loglist", $loglist);
        return $this->fetch();
    }
}