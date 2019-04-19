<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/5/25
 * Time: 11:22
 */
namespace app\index\controller\api;
use think\config;

class Luobotem extends Base{
    /*
     * 小程序模板消息
     * @param
     * @return
     * */
    public function index(){
        $config = Config::get("weiconfig");
        $appid = $config['appid'];
        $secret = $config['secret'];
        $wei_url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        $result=$this->get_info($wei_url);
        $tem_url="https://api.weixin.qq.com/cgi-bin/wxopen/template/list?access_token=".$result["access_token"];
        $this->get_info($tem_url);
        print_r($result);die;
    }
}