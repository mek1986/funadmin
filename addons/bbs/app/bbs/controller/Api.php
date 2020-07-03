<?php
/**
 * lemocms
 * ============================================================================
 * 版权所有 2018-2027 lemocms，并保留所有权利。
 * 网站地址: https://www.lemocms.com
 * ----------------------------------------------------------------------------
 * 采用最新Thinkphp6实现
 * ============================================================================
 * Author: yuege
 * Date: 2019/12/5
 */
namespace app\bbs\controller;

use app\common\model\Attach as AttachModel;
use app\common\model\Bbs as BbsModel;
use app\common\model\BbsCollect;
use app\common\model\BbsUserSign;
use lemo\helper\DataHelper;
use think\facade\Request;
class Api extends Comm {

    //上传验证规则
    protected $uploadValidate = [
        'image' => 'filesize:102400|fileExt:jpg,png,gif,jpeg,rar,zip,avi,mp4,rmvb,3gp,flv,mp3,txt,doc,xls,ppt,pdf,xls,docx,xlsx,doc'
    ];
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
    }

    public function upload(){
        $type=input('file','file');
        $path =input('path','uploads');
        //获取上传文件表单字段名
        $file =request()->file('file');

        $file_size = $file->getSize();
        $md5 = $file->md5();
        $sha1 = $file->sha1();;
        $file_mime = $file->getMime();
        $attach = AttachModel::where('md5',$md5)->find();
        if(!$attach){
            try {
                switch ($type=='file'){
                    case 'file':
                        $validate = $this->uploadValidate;
                        break;
                    case 'image':
                        $validate = $this->imageValidate;
                        break;
                    case 'video':
                        $validate = $this->videoValidate;
                        break;
                    case 'voice' :
                        $validate = $this->voiceValidate;
                        break;
                    default:
                        $validate = $this->uploadValidate;

                }
                validate($validate)
                    ->check(DataHelper::objToArray($file));
                $savename = \think\facade\Filesystem::disk('public')->putFile($path, $file);
                $path = '/storage/' . $savename;
            } catch (\think\exception\ValidateException $e) {
                $path = '';
                $error = $e->getMessage();
            }
            $file_ext =  strtolower(substr($savename, strrpos($savename, '.') + 1));
            $file_name = basename($savename);
            $width = $height = 0;
            if (in_array($file_mime, ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($file_ext, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
                $imgInfo = getimagesize($file->getPathname());
                ;
                if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                    $this->error(lang('Uploaded file is not a valid image'));
                }
                $width = isset($imgInfo[0]) ? $imgInfo[0] : $width;
                $height = isset($imgInfo[1]) ? $imgInfo[1] : $height;
            }
            if (!empty($path)) {
                $data = [
                    'admin_id'=>0,
                    'user_id'=>session('user.id'),
                    'name'=>$file_name,
                    'path'=>$path,
                    'thumb'=>$path,
                    'url'=>$this->request->domain().$path,
                    'ext'=>$file_ext,
                    'size'=>$file_size/1024,
                    'width'=>$width,
                    'height'=>$height,
                    'md5'=>$md5,
                    'sha1'=>$sha1,
                    'mime'=>$file_mime,
                    'driver'=>'local',

                ];
                $attach = AttachModel::create($data);
                $result['code'] = 1;
                $result['id'] =$attach->id;
                $result["url"] = $path;
                $result['msg'] = lang('upload success');
                return json($result);
            } else {
                //上传失败获取错误信息
                $result['url'] = '';
                $result['msg'] = $error;
                $result['code'] = 0;
                return json($result);
            }

        }else{
            $result['code'] = 1;
            $result['id'] =$attach->id;
            //分辨是否截图上传，截图上传只能上传一个，非截图上传可以上传多个
            $result["url"] = $attach->path;
            $result['msg'] = lang('upload success');
            return json($result);
        }

    }


    //回帖榜单
    public function topReply(){

        $data = BbsModel::whereWeek('create_time')
            ->cache(3600)
            ->group('user_id')
            ->with(['user' => function($query){
                $query->field('id,username,avatar,level_id');
            }])
            ->withCount('comment')
            ->order('comment_count desc')->limit(12)->select();
        return json(['code'=>1,'msg'=>'成功','data'=>$data]);

    }

    //热议文章
    public function hots(){
        $hots = BbsModel::where('status',1)
            ->where('comment_num','>',0)
            ->whereWeek('create_time')
            ->order('comment_num desc')
            ->select();
        $this->success('成功','',$hots);
    }


    public function refreshToken(){
        $token = $this->request->buildToken('__token__', 'sha1');
        return $token;
    }
}
