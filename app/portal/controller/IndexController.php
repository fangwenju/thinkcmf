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

class IndexController extends HomeBaseController
{
    public function index()
    {
        // $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=6mXH6SkDlM6LCWo8IC5KEz7t7KZReqbDg1PxlYakD6l9tl3G-mQ936pvjj1EbWrICDrZrOtajVhrGcb2DJ4oY3_aC2lHPxPd2fOu8pCMTUQTroc-LHHGimrOj_kQtnwBRGTeACAKZM';
        $url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEN8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ2RSdFpQbkVlUTAxMDAwMHcwN3AAAgQQ0vJZAwQAAAAA';
        // $data=json_encode(array('action_name'=>'QR_LIMIT_STR_SCENE','action_info'=>array('scene'=>array('scene_str'=>'fwj'))));
        $data = $this->curl_get_https($url);
        // return $this->display('<img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEN8TwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ2RSdFpQbkVlUTAxMDAwMHcwN3AAAgQQ0vJZAwQAAAAA">');
        // $s = cart_qrcode('http://www.baidu.com', 'http://www.jiuyf.com/ad/baijy/logo.jpg', 6);
        // echo '<img src="'.cmf_get_image_url($s).'">';exit;
        return $this->fetch(':index');
    }

    public function curl_get_https($url){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);  // 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        curl_close($curl);//关闭URL请求
        return $tmpInfo;    //返回json对象
    }

    public function curl_post_https($url,$data){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }

    public function getOpenid()
    {
        $APPID='wx57696dd858c59cad';
        $APPSECRET='e31c66c841dffd0357d1d405e88e6e44';
        $REDIRECT_URI='http://www.fwjtb.com/weixin/getopenid';
        $scope='snsapi_base';
        $state='aaaa';

        if ( !isset($_GET["code"]) ) {
            $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
            header("Location:".$url);exit;
        }

        $code = $_GET["code"];
        $get_token_url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$APPID.'&secret='.$APPSECRET.'&code='.$code.'&grant_type=authorization_code';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$get_token_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);

        $json_obj = json_decode($res,true);
        // $openid = $json_obj['openid'];
        print_r($json_obj);
    }
}
