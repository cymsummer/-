<?php
/**
 * Created by PhpStorm.
 * User: rwcym
 * Date: 2018/6/6
 * Time: 14:06
 */

namespace app\index\controller\api;

use think\Db;

class Luobopl extends Base
{
    /**
     * 萝卜评论列表展示
     * @param wz_id 文章id
     * @param pl_id 评论总id
     * @return json
     * */
    public function pllist()
    {
        //条件查询
        $id = $this->request->get("wz_id");//文章id
        $pl_id = $this->request->get("pl_id");//评论id
        $wz_info = Db::name("content")->where("id=$id and pl_id=$pl_id")->find();
        if(!empty($wz_info)){
            $code="1";
            $pl_info = Db::name("pl")->where("pl_id=$pl_id")->select();
            $msg="成功";
        }else{
            $code="0";
            $pl_info=[];
            $msg="失败";
        }

        $result=array(
            "code"=>$code,
            "msg"=>$msg,
            "data"=>$pl_info
        );
        return json_encode($result);
    }

    /**
     * 评论内容添加
     * @param wz_id 文章id
     * @param pl_id 评论总id
     * @param pl_fid 评论fid
     * @return json
     * */
    public function pltj()
    {
        //条件查询
        $where["id"] = $this->request->get("wz_id");//文章id
        $where["pl_id"] = $this->request->get("pl_id");//评论id
        $wz_info = Db::name("content")->where($where)->find();
        //添加数据
        $data['luobo_pl_info']=$this->request->get("pl_info");//评论内容
        $data['luobo_id']=$this->request->get("wz_id");//文章id
        $data['luobo_pl_fid']=$this->request->get("luobo_pl_fid")?:"0";//评论父id
        $data['pl_id']=$this->request->get("pl_id");//评论id
        $data['publish_time']=date("Y-m-d H:i:s",time());//发布时间
        if (!empty($wz_info)) {
            Db::name("pl")->insert($data);
            $code = "1";
            $msg = "添加成功";
        } else {
            $code = "0";
            $msg = "无此文章";
        }
        $result = array(
            "code" => $code,
            "msg" => $msg
        );
        return json_encode($result);
    }

    /**
     * 点赞功能
     * @param wz_id 文章id
     * @pram act 请求类型 dztj：添加 dzqx：取消
     * @return json
     * */
    public function dztj()
    {
        $wz_id = $this->request->get("wz_id");
        $act = $this->request->get("act");
        if ($act == "dztj") {
            $msg = "点赞成功";
            $code = 1;
            Db::name('content')->where('wz_id', $wz_id)->setInc('dz');
        } else {
            $msg = "点赞失败";
            $code = 0;
            Db::name('content')->where('wz_id', $wz_id)->setDec('dz');
        }
        $dz = Db::table('content')->count('dz');
        $result = array(
            "code" => $code,
            "count" => $dz,
            "msg" => $msg
        );
        return json_encode($result);
    }
}