<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/5/23
 * Time: 11:04
 */

namespace app\index\controller\front;

use think\Request;
use think\Controller;
use think\Db;
use think\cache\driver\Redis;
use think\Url;

class Base extends Controller
{
    //生成的url
    const url =  "/luobo/public/index.php/index/front.show/getid?id=";

    /*
     * @note 登陆测试
     * */
    public function _initialize()
    {
        $username = $this->request->post("username");
        $password = $this->request->post("password");
        $user_md5_pwd = md5($username . $password);
        //连接本地的 Redis 服务
        $redis = new Redis();
        $user_pwd = $redis->get("user_info");
        if (empty($user_pwd) || ($user_md5_pwd != $user_pwd)) {
            $user_info = Db::name("login")->where(["username" => $username, "password" => $password])->select();
            if (!empty($user_info)) {
                $redis->set("user_info", md5($username . $password), 604800);
            }
            return false;
        }
        return true;
    }

    /**
     * 分页
     * */
    public function page($current_page,$count,$limit){
        //总数
        $page_info['count']=$count;
        //总页数
        $page_info['page_count']=ceil($count/$limit);
        if($page_info['page_count']==0){
            $page_info['page_count']=1;
        }
        //界定值：
        $page_info['pagesize'] = ($current_page-1) * $limit;
        //上一页
        $page_info['prevpage'] = $current_page - 1;
        if( $page_info['prevpage']<=0){
            $page_info['prevpage']=1;
        }
        //下一页
        $page_info['nextpage']  = $current_page + 1;
        if($page_info['nextpage']> $page_info['page_count']){
            $page_info['nextpage']=$page_info['page_count'];
        }
        return $page_info;
    }
}