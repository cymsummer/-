<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018/5/24
 * Time: 15:36
 */

namespace app\index\controller\front;

use Endroid\QrCode\QrCode;
use think\Db;
use think\cache\driver\Redis;
use think\Image;
use think\Url;

class Content extends Base
{

    /**
     * 首页面展示
     * @param
     * @return
     * */
    public function index()
    {
        //连接本地的 Redis 服务
        $redis = new Redis();
        $user_pwd = $redis->get("user_info");
        $tokne = $this->request->get("token");
        if ($user_pwd != $tokne) {
            $this->Redirect("front.login/index");
        } else {
            return $this->fetch("front/content/luobo_content");
        }
    }

    /**
     * 添加文章
     * @param info 简介
     * @param coverimg 封面图
     * param content 内容
     * @param title 标题
     * @param date 修改时间
     * @param luobo_state 保存类型
     * @return
     * */
    public function add()
    {
        $act = $this->request->request("act");
        $data = array();
        $data['luobo_info'] = $this->request->post("info");//简介
        $data['luobo_cover_img'] = $this->request->post("coverimg");//封面图
        $data['luobo_content'] = $this->request->post("content");//内容
        $data['luobo_title'] = $this->request->post("title");//标题
        $data['update_date'] = $this->request->post("date") ? $this->request->post("date") : date("Y-m-d H:i:s", time());//时间
        $type = $this->request->post("operate_type");//保存类型
        $redis = new Redis();
        $data["pl_id"] = $redis->inc("program_comment_id");//自增的评论总id
        $data["label_id"] = $redis->inc("program_tab_id");//自增的标签总id
        $wz_id = $this->request->request("wz_id");
        if ($act == "add") {
            $data['luobo_state'] = 2;
            $wz_id = Db::name("content")->insertGetId($data);
            $url = $this->request->server("HTTP_HOST");
            echo json_encode(array('code' => $type, 'data' => $data, 'ztid' => $wz_id, 'msg' => "返回值！", "act" => "add", "zturl" => $url . self::url . $wz_id));
            die;

        } elseif ($act == "edit") {
            //查信息显示
            if (empty($type)) {
                //获取文章id
                if ($wz_id) {
                    $wz_info = Db::name("content")->where(["id" => $wz_id])->findOrFail();
                }
                $this->assign("wz_info", $wz_info);
                $this->assign("act", "edit");
                return $this->fetch("front/content/editzt");
            } else {
                //update修改
                if ($type == 3) {
                    //已发布
                    Db::name("content")->where("id=$wz_id")->update($data);
                } elseif ($type == 1) {
                    //草稿
                    $data['luobo_state'] = 2;
                    Db::name("content")->insertGetId($data);
                }
                $url = $this->request->server("HTTP_HOST");
                echo json_encode(array('code' => $type, 'data' => $data, 'ztid' => $wz_id, 'msg' => "返回值！", "act" => "edit", "zturl" => $url . self::url . $wz_id));
                exit;
            }
        } else {
            //页面展示
            return $this->fetch("front/content/editzt");
        }

    }

    /*
     * 文章修改
     * */
//    public function edit()
//    {
//        $act = $this->request->request("act");
//        $data = array();
//        $data['luobo_info'] = $this->request->post("info");//简介
//        $data['luobo_cover_img'] = $this->request->post("coverimg");//封面图
//        $data['luobo_content'] = $this->request->post("content");//内容
//        $data['luobo_title'] = $this->request->post("title");//标题
//        $data['update_date'] = date("Y-m-d H:i:s", time());//时间
//        $type = $this->request->post("operate_type");//保存类型
//        $wz_id = $this->request->request("wz_id");
//        if ($act == "edit") {
//            //查信息显示
//            if (empty($type)) {
//                //获取文章id
//                if ($wz_id) {
//                    $wz_info = Db::name("content")->where(["id" => $wz_id])->findOrFail();
//                }
//                $this->assign("wz_info", $wz_info);
//                $this->assign("act", "edit");
//                return $this->fetch("front/content/editzt");
//            } else {
//                //update修改
//                if ($type == 3) {
//                    Db::name("content")->where("id=$wz_id")->update($data);
//                } elseif ($type == 1) {
//                    $data['luobo_state'] = 2;
//                    Db::name("content")->insertGetId($data);
//                }
//                $url = $this->request->server("HTTP_HOST");
//                echo json_encode(array('code' => $type, 'data' => $data, 'ztid' => $wz_id, 'msg' => "返回值！", "act" => "edit", "zturl" => $url . self::url . $wz_id));
//                exit;
//            }
//        }
//    }

    /**
     * 发布文章
     * @param wz_id 文章id
     * @param act 请求类型
     * @param posttime 发布时间
     * @result
     * */
    public function publish()
    {
        $wz_id = $this->request->request("wz_id");
        $act = $this->request->request("act");
        $noworlater = $this->request->request("noworlater");
        $wz_info = Db::name("content")->where(["id" => $wz_id])->findOrFail();
        //页面显示
        if (empty($noworlater)) {
            $this->assign("wz_info", $wz_info);
            $this->assign("act", $act);
            return $this->fetch("front/content/zt_save_post");
        } else {
            $publish_date = $this->request->post("posttime");//发布时间不会为空
            if (empty($publish_date)) {
                $publish_date = date("Y-m-d H:i:s", time());
                //已发布
                $state = 1;
            } else {
                //定时发布
                $state = 3;
            }
            //正式发布
            if ($act == "add") {
                $where["publish_date"] = $publish_date;
                $where["luobo_state"] = $state;
                if (Db::name("content")->where(["id" => $wz_id])->update($where)) {
                    $this->redirect("front.show/index");
                };
            } elseif ($act == "edit") {
                //修改发布
                $where = array();
                $wz_info = Db::name("content")->where("id=" . $wz_id)->find();
                //草稿箱 和 定时发布
                if ($wz_info["luobo_state"] == 2 || $wz_info["luobo_state"] == 3) {
                    $where["luobo_state"] = 1;
                    $where["publish_date"] = $publish_date;
                }
                $where["update_date"] = date("Y-m-d H:i:s", time());
                Db::name("content")->where(["id" => $wz_id])->update($where);
                $this->redirect("front.show/index");

            }
        }
    }


    /*
     * 插入图片
     * */
    public function insertimg()
    {
        $current_page = $this->request->get("page") ? $this->request->get("page") : 1;
        if ($current_page <= 1) {
            $current_page = 1;
        }
        $count = Db::name("image")->where("type=0")->count();
        $page_info = $this->page($current_page, $count, 10);
        $image_info = Db::name("image")->where("type=0")->limit($page_info["pagesize"], 10)->order("id desc")->select();
        $this->assign("image_info", $image_info);
        $this->assign("page_info", $page_info);
        $this->assign("page", $current_page);
        return $this->fetch("front/content/iframe_insertimg");
    }

    /*
     * 上传封面
     * */
    public function checking()
    {
        $current_page = $this->request->get("page") ? $this->request->get("page") : 1;
        if ($current_page <= 1) {
            $current_page = 1;
        }
        $count = Db::name("image")->where("type=0")->count();
        $page_info = $this->page($current_page, $count, 10);
        $image_info = Db::name("image")->where("type=0")->limit($page_info["pagesize"], 10)->order("id desc")->select();
        $this->assign("image_info", $image_info);
        $this->assign("page_info", $page_info);
        $this->assign("page", $current_page);
        return $this->fetch("front/content/iframe_checkimg");
    }

    /*
     * 图片上传
     * */
    public function imgadd()
    {

        $act = $this->request->request("act");
        $image_info = $_FILES["imags"];
        $tmp_name = $image_info['tmp_name'];
        $config = config("img_config");
        
        if (!empty($tmp_name)) {
            foreach ($tmp_name as $v) {
                $image = Image::open($v);
                // 返回图片的类型
                $type = $image->type();
                //添加水印
                //     $image->water('../public/luobo/images/logo.png', Image::WATER_SOUTHWEST, 100)->save('../public/upload/' . $new_img_name);
                //封面
                if (!empty($act) && $act == "cover") {
                    $new_img_name = $this->random(18) . "." . $type;
                    $image->thumb(400, 300, Image::THUMB_CENTER)->save($config["__ROOT__"] . '/' . $new_img_name);
                    $image->thumb(1600, 900, Image::THUMB_CENTER)->save($config["__BIGROOT__"] . '/' . $new_img_name);
                    $data["image_url"] = $config["src"] . "/" . $new_img_name;
                    $data["big_image_url"] = $config["big_src"] . "/" . $new_img_name;
                } else {
                    $new_img_name = $this->random(18) . "." . $type;
                    $image->save($config["__ROOT__"] . '/' . $new_img_name);
                    $data["image_url"] = $config["src"] . "/" . $new_img_name;
                    $data["big_image_url"] = "";
                }
                Db::name("image")->insert($data);
            }
        }
        return $data["image_url"];
    }

    /*
     * 图片删除
     * */
    public function imgdel()
    {
        $where['id'] = $this->request->get("id");
        $data['type'] = "-1";
        if (Db::name("image")->where($where)->update($data)) {
            echo 1;
        } else {
            echo 0;
        };
    }

    /**
     * 生成指定网址的二维码
     * @param string $url 二维码中所代表的网址
     */
    public function create_qrcode()
    {
        $id = $this->request->get("id");
        //生成当前的二维码
        $qrCode = new QrCode();
        if ($id) {
            //想显示在二维码中的文字内容，这里设置了一个查看文章的地址
            $url = Url::build('front.show/getid?', "id=$id", true, true);
            $qrCode->setText($url)
                ->setSize(115);
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }
    }

    public function random($length)
    {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }


    public function upd_img(){
        $new="";
//        $arr=Db::connect("db_config1")->name("small_program")->where("id",">","7410")->select();
////        print_r($arr);
//        foreach ($arr as$k=> $v){
////            $img_arr=explode("，",$v["program_pic"]);
////            array_pop($img_arr);
////            foreach ($img_arr as $val){
////                $new.=str_replace("https://www.rwcym.top/","https://img.loobo.top/",$val)."，";
////            }
//            $where["program_pic"]=str_replace("https://www.rwcym.top/","https://img.loobo.top/",$v['program_pic']);
////            print_r($where);die;
////            echo "\n\n\n\n\n\n\n";
//            Db::connect("db_config1")->name("small_program")->where(["id" => $v["id"]])->update($where);
//        }
//            print_r($new);die;
//            $where["image_url"]=str_replace("https://www.rwcym.top/upload//","https://img.loobo.top/upload/",$v["image_url"]);
//            $where["image_url"]=str_replace("https://www.rwcym.top/upload/","https://img.loobo.top/upload/",$v["image_url"]);
//            $where["image_url"]=str_replace("https://admin.loobo.top/upload/","https://img.loobo.top/upload/",$v["image_url"]);
//            $where["big_image_url"]=str_replace("http://lxr.rwcym.top/luobo/public/upload/big/","https://img.loobo.top/big/",$v["big_image_url"]);
//            $where["big_image_url"]=str_replace("https://www.rwcym.top/big/","https://img.loobo.top/big/",$v["big_image_url"]);
//            Db::connect("db_config1")->name("small_program")->where(["id" => $v["id"]])->update($where);
//            die;
//        $arr=Db::name("content")->where("id","107")->select();
//        print_r($arr);die;
////        https://img.loobo.top/upload/0adad526a5823c0df43261f5523024b7c8abd552-89acd9a689f3c4b09311fba61427ec0ce721b611.png
//        foreach ($arr as $v){
//            $where["luobo_cover_img"]=str_replace("http://lxr.rwcym.top/luobo/public/upload/","https://img.loobo.top/upload/",$v["luobo_cover_img"]);
//            $where["luobo_cover_img"]=str_replace("http://lxr.rwcym.top/luobo/public/upload/","https://img.loobo.top/upload/",$v["luobo_cover_img"]);
//            $where["luobo_content"]=str_replace("http://lxr.rwcym.top/luobo/public/upload/","https://img.loobo.top/upload/",$v["luobo_content"]);
//            $where["luobo_content"]=str_replace("http://lxr.rwcym.top/luobo/public/upload/","https://img.loobo.top/upload/",$v["luobo_content"]);
//            Db::name("content")->where(["id" => $v["id"]])->update($where);
//        }

    }
}