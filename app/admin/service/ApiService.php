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
namespace app\admin\service;

use think\Db;

class ApiService
{
    /**
     * 获取所有友情链接
     */
    public static function links()
    {
        return Db::name('link')->where('status', 1)->order('list_order ASC')->select();
    }

    /**
     * 获取所有幻灯片
     * @param $slideId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function slides($slideId)
    {
        $slideCount = Db::name('slide')->where('id', $slideId)->where(['status' => 1, 'delete_time' => 0])->count();

        if ($slideCount == 0) {
            return [];
        }

        $slides = Db::name('slide_item')->where('status', 1)->where('slide_id', $slideId)->order('list_order ASC')->select();

        return $slides;
    }

    /**
     * 记录后台操作日志
     * @return [type] [description]
     */
    public static function log($action, $user_id=0)
    {
        if (!$user_id) {
            $user_id = cmf_get_current_admin_id();
        }
        $user = Db::name('user')->where('id', $user_id)->find();
        if ($user) {
            $data = [
                'user_name' => $user['user_login'],
                'ip' => get_client_ip(),
                'create_time' => time(),
                'action' => $action,
            ];
            Db::name('admin_log')->insert($data);
        }
    }
}