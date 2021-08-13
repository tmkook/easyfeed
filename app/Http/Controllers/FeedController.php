<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feed;

class FeedController extends Controller
{

    public function add(){
        return view('feeds.add');
    }

    public function verify(Request $request){
        $url = trim(rawurldecode($request->url));
        $esh = new \Tmkook\EasyHTML($url);
        $esc = new \Tmkook\EasyContent($url);

        $data = ['url'=>$url,'verify'=>true,'content'=>''];
        $data['list'] = $esh->getList();
        if(empty($data['list']['list'])){
            $data['verify'] = false;
        }

        $data['title'] = $esh->getTitle();
        $data['logo'] = $esc->url($esh->getLogo());
        $data['description'] = $esh->getMeta('description');

        if($data['verify']){
            $purl = $esc->url($data['list']['list'][0]);
            $esh->loadURL($purl);
            $data['content'] = $esh->getContent();
        }
        
        return view('feeds.verify',$data);
    }

    public function created(Request $request){
        $data = [
            'success'=>true,
            'title'=>'保存成功',
            'info' => '站点已收录，稍后自动更新。'
        ];
        $has = Feed::withTrashed()->where('uuid',md5($request->url))->first();
        if($has){
            $data = [
                'success'=>false,
                'title'=>'保存失败',
                'info' => '该站点已存在，请勿重复提交。'
            ];
        }else{
            $feed = new Feed;
            $feed->url = $request->url;
            $feed->uuid = md5($request->url);
            $feed->icon = $request->logo;
            $feed->title = $request->title;
            $feed->description = $request->description;
            $feed->update_wait = 0;
            $feed->state = 1;
            $feed->save();
        }

        return view('feeds.finish',$data);
    }
}
