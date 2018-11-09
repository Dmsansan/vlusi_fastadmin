<?php

namespace app\api\controller;

use app\admin\model\article\Article;
use app\admin\model\article\Category;
use app\common\controller\Api;
use think\Db;

/**
 * 首页接口
 */
class Found extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $pagesize= 10;

    /**
     * 发现分类
     *
     * @ApiTitle    (发现的分类)
     * @ApiSummary  (article)
     * @ApiSector   (测试分组)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/found/category)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function category()
    {
        //分类数据
        $cate=db('article_category')->select();
        $this->success("返回成功",$cate);
    }

    /**
     * 推荐文章
     *
     * @ApiTitle    (推荐文章)
     * @ApiSummary  (article)
     * @ApiSector   (测试分组)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/found/category)
     * @ApiParams   (name="page", type="integer", required=true, description="页码")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function recommend()
    {
        $page =   (int)$this->request->request("page");
        //分类数据
        $list=db('article')->order('flag desc,views desc')
//            ->field('id,title,coverimage,content,videfile,views,comments,auth,createtime')
            ->limit($page*$this->pagesize,$this->pagesize)->select();
        $this->success("返回成功",$list);
    }

    /**
     * 获取分类文章
     *
     * @ApiTitle    (推荐文章)
     * @ApiSummary  (article)
     * @ApiSector   (测试分组)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/found/category)
     * @ApiParams   (name="type_id", type="integer", required=true, description="分类id")
     * @ApiParams   (name="page", type="integer", required=true, description="页码")
     * @ApiParams   (name="title", type="integer", required=false, description="搜索的标题")
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'mesg':'返回成功'
     * })
     */
    public function article()
    {
        $page   =   (int)$this->request->request("page");
        $typeid =  (int)$this->request->request("type_id");

        $search = $this->request->request('title');

        //分类数据
        $list=db('article')->order('flag desc,views desc');
        $list->where(['article_category_id'=>$typeid]);
        if($search){
            $list->where(['title'=>['like','%'.$search.'%']]);
        };
//            ->field('id,title,coverimage,content,videfile,views,comments,auth,createtime')
        $list->limit($page*$this->pagesize,$this->pagesize)->select();

        $this->success("返回成功",$list);
    }


    /**
     * 获取文章详情及评论接口
     */
    public function detail()
    {
        $userid=1;

        $page   =  (int)$this->request->request("page");
        $article_id  =  (int)$this->request->request("article_id");

        if($page>0){
            $data['detail'] =db('article')->where(['id'=>$article_id])->find();
        }

        $date['comment']=db('article_comment')->alias('a')->join('user','user.id=a.user_id')
                ->field('user.nickname,avatar,a.*')
                ->where(['article_id'=>$article_id])
                ->limit($page*$this->pagesize,$this->pagesize)
                ->select();

        //TODO 获取用户信息 判断用户是否点赞
        $collection=db('article_collection')->where(['user_id'=>$userid,'article_id'=>$article_id])->find();
        $data['is_collection']=$collection?1:0;




        $this->success("返回成功",$date);
    }


    /**
     * 提交评论接口
     */
    public function comment()
    {
        $userid=47;
        $article_id  =  (int)$this->request->request("article_id");
        $content=$this->request->request("content");

        $insert['article_id']=$article_id;
        $insert['content']=$content;
        $insert['user_id']=$userid;
        $insert['createtime']=time();
        $res=db('article_comment')->insert($insert);
        if($res){
            $this->success("评论成功");
        }else{
            $this->error('评论失败');
        }
    }


    /**
     * 收藏接口
     */
    public function collection()
    {
        $userid=47;

        $article_id= (int)$this->request->request("article_id");
        $is_have=db('article_collection')->where(['article_id'=>$article_id,'user_id'=>$userid])->find();
        if(!$is_have){
            $insert['article_id']=$article_id;
            $insert['user_id']=$userid;
            $insert['createtime']=time();
            $res=db('article_collection')->insert($insert);
            if(!$res){
                $this->error('收藏失败',[]);
            }
        }

        $this->success("收藏成功",[]);
    }



}
