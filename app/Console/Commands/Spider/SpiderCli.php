<?php

namespace App\Console\Commands\Spider;

use Illuminate\Support\Facades\Log;
use App\Console\Commands\Spider\SpiderInterface;


class SpiderCli extends SpiderInterface
{
    
    protected $doc;
    protected $target;
    
    public function load($target){
        $dom = new \PHPHtmlParser\Dom;
        $this->doc = $dom->loadFromUrl($target);
        $this->target = $target;
    }

    public function getMeta(){
        $info = [];
        $meta = $this->doc->find('meta');
        foreach($meta as $m){
            $k = $m->getAttribute('name');
            $v = $m->getAttribute('content');
            if(empty($k)){
                $k = $m->getAttribute('property');
            }
            if($k){
                $info[$k] = $v;
            }
        }
        $title = $this->doc->find('title');
        if(!empty($title)){
            $info['title'] = $title[0]->text;
        }
        return $info;
    }

    public function getList(){
        $ret = [];
        $list = $this->doc->find($this->feed->list_dom);
        foreach($list as $a){
            $title = trim($a->innerText);
            $url = $a->getAttribute('href');
            if(empty($url)) continue; //空内容
            $ret[] = $this->listItem($this->url($url),$title);
        }
        return $ret;
    }

    public function getMain(){
        $art = $this->doc->find($this->feed->main_dom);
        $main = $art->innerHtml;
        return empty($main)? null : $this->mainItem($main);
    }

    public function getNext(){
        $url = '';
        if($this->feed->next_dom){
            $dom = $this->doc->find($this->feed->next_dom);
            if(!empty($dom)){
                $url = $dom[0]->getAttribute('href');
                $url = $this->url($url);
            }
        }
        if(trim($this->feed->url,'/') == trim($url,'/')){
            $url = '';
        }
        return $url;
    }
}





// class SpiderCli22 extends SpiderInterface
// {
//     /**
//      * html parser
//      * 
//      * @var object
//      */
//     protected $dom;
//     protected $doc;

//     /**
//      * constructor
//      * 
//      * @var String url
//      */
//     public function __construct($feed){
//         parent::__construct($feed);
        
//     }

//     public function load($url){
//         $this->dom = new \PHPHtmlParser\Dom;
//         $this->doc = $this->dom->loadFromUrl($target);
//     }

    

//     public function getList(){
//         $ret = [];
//         $list = $this->doc->find($this->feed->list_dom);
        
//         //找不到内容，可能失效需要人工检查
//         if(empty($list)){
//             $this->feed->state = Feed::INVALID;
//             $this->feed->save();
//         }
        
//         //添加任务
//         foreach($list as $a){
//             $title = trim($a->innerText);
//             $url = $a->getAttribute('href');
//             if(empty($url)) continue; //空内容
//             $ret[] = ['title'=>$title,'url'=>$url];
//         }

//         return $ret;
//     }

//     public function getMain(){
//         $art = $doc->find($this->feed->main_dom);
//         $main = $art->innerHtml;
//         if(empty($main)) return false;
//         $summary =  mb_substr(strip_tags($main),0,100);
//         $cover = $this->cover($main);
//         return ['main'=>$main,'cover'=>$cover,'summary'=>$summary];
//     }
    
//     /**
//      * constructor
//      * 
//      * @var String targetddd
//      */
//     public function feeds(String $target){
//         try{
//             $ret = ['total'=>0,'failed'=>0,'next'=>''];
//             $doc = $this->dom->loadFromUrl($target);
//             $list = $doc->find($this->feed->list_dom);
            
//             //找不到内容，可能失效需要人工检查
//             if(empty($list)){
//                 $this->feed->state = Feed::INVALID;
//                 $this->feed->save();
//                 return $ret;
//             }

//             //添加任务
//             foreach($list as $a){
//                 $ret['total']++;
//                 $title = trim($a->innerText);
//                 $url = $a->getAttribute('href');
//                 if(empty($url)){
//                     $ret['failed']++;
//                     continue; //空内容
//                 }

//                 //检查内容是否已存在
//                 $url = $this->url($url);
//                 $uuid = md5($url);
//                 $news = News::where('uuid',$uuid)->where('feed_id',$this->feed->id)->first();
//                 if(empty($news)){
//                     $news = new News;
//                 }
//                 $news->feed_id = $this->feed->id;
//                 $news->uuid = $uuid;
//                 $news->url = $url;
//                 $news->title = $title;
//                 $news->state = News::CHECK;
//                 $news->save();
//             }

//             //翻页
//             if($this->feed->next_dom){
//                 $next = $doc->find($this->feed->next_dom)[0];
//                 $nexturl = $next->getAttribute('href');
//                 if($next && $nexturl){
//                     $nexturl = $this->url($nexturl);
//                     if($nexturl != $target){
//                         $ret['next'] = $nexturl;
//                     }
//                 }
//             }
//             if($this->feed->state != Feed::SUCCESS){
//                 $this->feed->state = Feed::SUCCESS;
//                 $this->feed->save();
//             }
//         //站点无法访问
//         }catch(\Exception $e){
//             $this->feed->state = Feed::FAIL;
//             $this->feed->save();
//             $ret['error'] = $e->getMessage();
//         }
//         Log::info('feeds:'.$target,$ret);
//         return $ret;
//     }

//     /**
//      * constructor
//      * 
//      * @var Object news
//      */
//     public function news($news){
//         $doc = $this->dom->loadFromUrl($news->url);
//         $art = $doc->find($this->feed->main_dom);
//         $main = $art->innerHtml;
//         if(empty($main)){
//             $news->state = News::FAIL;
//             $news->save();
//         }else{
//             $summary =  mb_substr(strip_tags($main),0,100);
//             $cover = $this->cover($main);
//             $news->summary = $summary;
//             $news->cover = $cover;
//             $news->main = $main;
//             $news->state = News::SUCCESS;
//             $news->save();
//         }
//         Log::info('news:'.$news->url,$news->state);
//     }

//     public function cover($main){
//         preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $main, $cover);
//         $cover = array_slice($cover[1],0,3);
//         foreach($cover as $k=>$v){
//             $cover[$k] = $this->url($v);
//         }
//         return $cover;
//     }
// }