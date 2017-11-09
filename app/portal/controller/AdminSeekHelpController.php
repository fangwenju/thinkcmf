<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\service\ApiService;

/**
 *  求助订单控制器
 */
class AdminSeekHelpController extends AdminBaseController
{
    public function index()
    {
        $request = input('request.');
        $where   = [];
        $status = 0;
        if (!empty($request['status'])) {
            $status = $request['status'];
            $where['status'] = $status;
        }

        $list = Db::name('seek_help')->where($where)->order("create_time DESC")->paginate(20);
        foreach ($list as $key => $value) {
            if (!empty($value['img'])) {
                $value['img'] = explode(',', $value['img']);
            }
            $list[$key] = $value;
        }

        // 获取分页显示
        $page = $list->render();
        $lis = $this->lis($status);

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('lis', $lis);
        $this->assign('status', $status);
        return $this->fetch();
    }

    /**
     * 头部li列表
     */
    private function lis($status)
    {
        $url = url('AdminSeekHelp/index');
        $a = "<li><a href='$url'>全部</a></li>";
        $a1 = "<li class='active'><a href='#' data-toggle='tab'>全部</a></li>";
        $b = "<li><a href='$url?status=1'>待审核</a></li>";
        $b1 = "<li class='active'><a href='#' data-toggle='tab'>待审核</a></li>";
        $c = "<li><a href='$url?status=2'>审核失败</a></li>";
        $c1 = "<li class='active'><a href='#' data-toggle='tab'>审核失败</a></li>";
        $d = "<li><a href='$url?status=3'>待资助</a></li>";
        $d1 = "<li class='active'><a href='#' data-toggle='tab'>待资助</a></li>";
        $e = "<li><a href='$url?status=4'>已资助</a></li>";
        $e1 = "<li class='active'><a href='#' data-toggle='tab'>已资助</a></li>";

        switch ($status) {
            case '1':
                $html = $a.$b1.$c.$d.$e;
                break;
            case '2':
                $html = $a.$b.$c1.$d.$e;
                break;
            case '3':
                $html = $a.$b.$c.$d1.$e;
                break;
            case '4':
                $html = $a.$b.$c.$d.$e1;
                break;
            default:
                $html = $a1.$b.$c.$d.$e;
                break;
        }
        return $html;
    }

    /**
     * 添加求助订单
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $message = '';
            if (empty($params['name'])) {
                $message = '联系人不能为空';
            } elseif (empty($params['mobile'])) {
                $message = '手机号不能为空';
            } elseif ( empty($params['address']) ) {
                $message = '详细地址不能为空';
            } elseif ( empty($params['content']) ) {
                $message = '求助说明不能为空';
            } elseif ($params['status'] == 4 && empty($params['weight']) ) {
                $message = '请填写发货重量';
            }

            if (!empty($message)) {
                $this->error($message);
            }

            $img = '';
            if (!empty($params['img'])) {
                foreach ($params['img'] as $key => $value) {
                    $img .= $value.',';
                }
                $img = substr($img,0,strlen($img)-1);
            }
            $params['img'] = $img;

            $params['create_time'] = time();
            $res = Db::name('seek_help')->insertGetId($params);
            if ($res) {
                ApiService::log('添加求助信息#'.$res);
                $this->success("添加成功！",url('AdminSeekHelp/index'));
            }
            $this->error('添加失败');
        }
        return $this->fetch();
    }

    /**
     * 订单修改
     * @return [type] [description]
     */
    public function edit()
    {
        $params = $params = $this->request->param();
        if ( $this->request->isPost() ) {
            if (!empty($params['status']) && !empty($params['id'])) {
                $res = Db::name('appointment')->update($params);
                if ($res) {
                    $this->error('提交成功');
                }
            }
            $this->error('提交失败');
        } else {
            $list = Db::name('appointment')->where('id', $params['id'])->find();
            $weight = Db::name('weight_interval')->select();
            foreach ($weight as $key => $value) {
                if ($value['id'] == $list['weight_interval']) {
                    $list['weight_interval'] = $value['title'];
                    break;
                }
            }

            $this->assign('list', $list);
        }
        return $this->fetch();
    }
}