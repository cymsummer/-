<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/5/25
 * Time: 9:59
 */

namespace app\index\controller\api;

use think\config;
use think\Db;

class Luobologin extends Base
{
    /*
     * 小程序登陆
     * @param
     * @return result 微信获取数据
     * */
    public function index()
    {
        $config = Config::get("weiconfig");
        $appid = $config['appid'];
        $secret = $config['secret'];
        $code = $this->request->get("code");
        $weixin_url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code";
        $result = $this->get_info($weixin_url);
        print_r($result);
        die;
    }


}