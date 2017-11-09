<?php
namespace app\portal\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;
use app\admin\service\ApiService;
/**
 * 推广伙伴
 */
class AdminCollaboratorController extends AdminBaseController
{
    public function index()
    {
        $where = ["user_type" => 3];
        //搜索条件
        $user_login = $this->request->param('user_login');
        $mobile = trim($this->request->param('mobile'));

        if ($user_login) {
            $where['user_login'] = ['like', "%$user_login%"];
        }

        if ($mobile) {
            $where['mobile'] = ['like', "%$mobile%"];;
        }

        $users = Db::name('user')
            ->where($where)
            ->order("id DESC")
            ->paginate(20);
        // 获取分页显示
        $page = $users->render();

        $this->assign("page", $page);
        $this->assign("users", $users);
        return $this->fetch();
    }

    /**
     * 合作伙伴添加
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            //验证数据
            $postdata = $this->request->param();
            $this->validata($postdata);

            //保存数据
            $postdata['user_type'] = 3;
            $postdata['user_status'] = 0;
            $res = Db::name('user')->insert($postdata);
            if ($res) {
                ApiService::log('添加合作伙伴#'.$res);
                $this->success("添加成功！",url('AdminCollaborator/index'));
            }
            $this->error("添加失败！");
        } else {
            return $this->fetch('add');
        }
    }

    /**
     * 合作伙伴编辑
     */
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $user = DB::name('user')->where(["id" => $id])->find();
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 合作伙伴编辑提交
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $postdata = $this->request->param();
            $this->validata($postdata);

            // promotion_information($postdata['id'], 3);//更新推广信息

            //保存数据
            $postdata['user_type'] = 3;
            $postdata['user_status'] = 0;
            $res = Db::name('user')->update($postdata);
            if ($res) {
                ApiService::log('修改合作伙伴#'.$postdata['id']);
                $this->success("修改成功！",url('AdminCollaborator/index'));
            }
            $this->error("无修改或修改失败！");
        }
    }

    /**
     * 数据验证
     * @param  [type] $data 验证的数据
     * @return [type]       [description]
     */
    private function validata($data)
    {
        $validate = new Validate([
            'user_login' => 'require',
            'mobile'     => 'require',
            // 'user_email' => 'require|email',
            'bank_name'  => 'require',
            'bank_card'  => 'require|number',
        ]);
        $validate->message([
            'user_login.require' => '名称必须',
            'mobile.require'     => '联系方式必须',
            // 'user_email.require' => '联系邮箱必须',
            // 'user_email.email'   => '邮箱格式错误',
            'bank_name.require'  => '开户银行必须',
            'bank_card.require'  => '银行卡账号必须',
            'bank_card.number'  => '银行卡账号不正确',
        ]);
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
    }

    /**
     * 禁用
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("user")->where(["id" => $id, "user_type" => 3])->setField('user_status', 0);
            if ($result) {
                $this->success("推广伙伴禁用成功！",'');
            } else {
                $this->error('推广伙伴拉黑失败');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("user")->where(["id" => $id, "user_type" => 3])->setField('user_status', 1);
            $this->success("推广伙伴启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
}