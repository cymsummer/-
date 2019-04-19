<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018/5/23
 * Time: 11:04
 */

namespace app\index\controller\front;

use think\cache\driver\Redis;
use think\Session;
class Login extends Base
{
    /*
     * @note 登陆
     * @return
     * */
    public function index()
    {
        $username = $this->request->post("username");
        $password = $this->request->post("password");
        if (empty($username) && empty($password)) {
            return $this->fetch("front/login/luobo_login");
        } else {
            //连接本地的 Redis 服务
            $redis = new Redis();
            $user_pwd = $redis->get("user_info");
            if ($user_pwd == md5($username . $password)) {
                session::set("username",$username);
                $result = array(
                    "code" => "1",
                    "token"=>$user_pwd,
                    "msg" => "登陆成功！"
                );
                return json_encode($result);
            }else{
                $result = array(
                    "code" => "0",
                    "msg" => "请输入正确的信息！"
                );
                return json_encode($result);
            }
        }
    }
}