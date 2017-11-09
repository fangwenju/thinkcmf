<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\service\ApiService;

/**
 *  预约订单控制器
 */
class AdminAppointmentController extends AdminBaseController
{
    public function index()
    {
        $request = input('request.');
        $where   = [];
        $status = 0;
        if (!empty($request['status'])) {
            $status = $request['status'];
            if ($request == '6') {
                $request['status'] = 0;
            }
            $where['status'] = $request['status'];
        }
        if (!empty($request['name'])) {
            $name = trim($request['name']);
            $where['name'] = ['like', "%$name%"];
        }
        if (!empty($request['mobile'])) {
            $where['mobile'] = trim($request['mobile']);
        }
        if (!empty($request['province']) && $request['province'] != '-1') {
            $where['province'] = $request['province'];
        }
        if (!empty($request['city']) && $request['city'] != '-1') {
            $where['city'] = $request['city'];
        }
        if (!empty($request['area']) && $request['area'] != '-1') {
            $where['area'] = $request['area'];
        }
        if (!empty($request['start_time'])) {
            $start_time = strtotime($request['start_time']);
            $where['create_time'] = ['>=', $start_time];
        }
        if (!empty($request['end_time'])) {
            $end_time = strtotime($request['end_time'])+24*3600;
            $where['create_time'] =['<', $end_time];
        }
        if (isset($start_time) && isset($end_time)) {
            $where['create_time'] =['between',[$start_time,$end_time]];
        }

        $weight = Db::name('weight_interval')->select();
        foreach ($weight as $key => $value) {
            $weights[$value['id']] = $value['title'];
        }
        $this->assign('weights', $weights);

        $list = Db::name('appointment')->where($where)->order("create_time DESC")->paginate(20);
        // 获取分页显示
        $page = $list->render();
        $lis = $this->lis($status);

        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('lis', $lis);
        $this->assign('status', $status);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 头部li列表
     */
    private function lis($status)
    {
        $url = url('AdminAppointment/index');
        $a = "<li><a href='$url'>全部</a></li>";
        $a1 = "<li class='active'><a href='#' data-toggle='tab'>全部</a></li>";
        $b = "<li><a href='$url?status=1'>待审核</a></li>";
        $b1 = "<li class='active'><a href='#' data-toggle='tab'>待审核</a></li>";
        $c = "<li><a href='$url?status=2'>审核失败</a></li>";
        $c1 = "<li class='active'><a href='#' data-toggle='tab'>审核失败</a></li>";
        $d = "<li><a href='$url?status=3'>已通知物流收货</a></li>";
        $d1 = "<li class='active'><a href='#' data-toggle='tab'>已通知物流收货</a></li>";
        $e = "<li><a href='$url?status=4'>物流已收货</a></li>";
        $e1 = "<li class='active'><a href='#' data-toggle='tab'>物流已收货</a></li>";
        $f = "<li><a href='$url?status=5'>仓库已收货</a></li>";
        $f1 = "<li class='active'><a href='#' data-toggle='tab'>仓库已收货</a></li>";
        $g = "<li><a href='$url?status=6'>订单异常</a></li>";
        $g1 = "<li class='active'><a href='#' data-toggle='tab'>订单异常</a></li>";

        switch ($status) {
            case '1':
                $html = $a.$b1.$c.$d.$e.$f.$g;
                break;
            case '2':
                $html = $a.$b.$c1.$d.$e.$f.$g;
                break;
            case '3':
                $html = $a.$b.$c.$d1.$e.$f.$g;
                break;
            case '4':
                $html = $a.$b.$c.$d.$e1.$f.$g;
                break;
            case '5':
                $html = $a.$b.$c.$d.$e.$f1.$g;
                break;
            case '6':
                $html = $a.$b.$c.$d.$e.$f.$g1;
                break;
            default:
                $html = $a1.$b.$c.$d.$e.$f.$g;
                break;
        }
        return $html;
    }

    /**
     * 添加预约订单
     */
    public function add()
    {
        $weights = Db::name('weight_interval')->where('ishide',0)->select();
        $this->assign('weights', $weights);
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $message = '';
            if (empty($params['name'])) {
                $message = '联系人不能为空';
            } elseif (empty($params['mobile']) || !preg_match("/^1[34578]\d{9}$/", $params['mobile'])) {
                $message = '手机号码错误';
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
            }

            if (!empty($message)) {
                $this->error($message);
            }

            $params['appointment_time'] = strtotime($params['appointment_time']);
            $params['create_time'] = time();
            $params['type'] = 3;
            $params['status'] = 1;
            $res = Db::name('appointment')->insertGetId($params);
            if ($res) {
                ApiService::log('添加预约订单#'.$res);
                $this->success("添加成功！",url('AdminAppointment/index'));
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
                    ApiService::log('修改预约订单状态#'.$params['id']);
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

    /**
     * 重量区间展示页
     * @return [type] [description]
     */
    public function weightInterval()
    {
        $weightQuery = Db::name('weight_interval');
        $list = $weightQuery->paginate(20);
        // 获取分页显示
        $page = $list->render();
        $this->assign('title','新增区间');
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }

    /**
     * 编辑重量区间
     * @return [type] [description]
     */
    public function weightEdit()
    {
        $params = $this->request->param();
        if ( $this->request->isPost() ) {
            if (!empty($params['title']) && !empty($params['id'])) {
                $res = Db::name('weight_interval')->update($params);
                if ($res) {
                    ApiService::log('修改了重量区间#'.$params['id']);
                    $this->success("编辑成功！",url('AdminAppointment/weightInterval'));
                }
                $this->success("数据无改变！",url('AdminAppointment/weightInterval'));
            }
            $this->error("编辑失败！");
        } else {
            if (empty($params['id'])) {
                $this->error("编辑内容不存在");
            }

            $list = Db::name('weight_interval')->where($params)->find();
            $this->assign('list', $list);
            return $this->fetch();
        }

    }

    /**
     * 添加重量区间
     * @return [type] [description]
     */
    public function weightAdd()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            if (!empty($params['title'])) {
                $res = Db::name('weight_interval')->insertGetId($params);
                if ($res) {
                    ApiService::log('添加了重量区间#'.$res);
                    $this->success("添加成功！",url('AdminAppointment/weightInterval'));
                }
                $this->error("添加失败！");
            }
            $this->error("区间值不能为空");
        }
        return $this->fetch();
    }
}