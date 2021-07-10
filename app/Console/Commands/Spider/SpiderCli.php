<?php

namespace App\Console\Commands\Spider;

use Illuminate\Support\Facades\Log;
use App\Console\Commands\Spider\SpiderInterface;


class SpiderCli extends SpiderInterface
{
    
    protected $doc;
    protected $target;

    public function __construct($baseurl){
        parent::__construct($baseurl);
        $this->client = new \GuzzleHttp\Client();
    }

    public function load($target){
        $headers = ['User-Agent'=>'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50'];
        $response = $this->client->request('GET',$target,['headers'=>$headers]);
        $content = $response->getBody()->getContents();
        $content = preg_replace('|<\s|','&lt;',$content); //todo:文章中含有><导致无法解析HTML,需转义正文中的符号
        $dom = new \PHPHtmlParser\Dom;
        $this->doc = $dom->loadStr($content);
        $this->target = $target;
    }

    public function getTitle(){
        $title = $this->doc->find('title')[0];
        return empty($title)? '' : trim($title->text);
    }

    public function getIcon(){
        $icon = '';
        $link = $this->doc->find('link');
        foreach($link as $m){
            $k = $m->getAttribute('rel');
            if(strpos($k,'icon') > 0){
                $icon = $this->url($m->getAttribute('href'));
                break;
            }
        }
        if(empty($icon)){
            $meta = $this->doc->find('meta');
            foreach($meta as $m){
                $k = $m->getAttribute('property');
                if($k=='og:image'){
                    $icon = $this->url($m->getAttribute('content'));
                    break;
                }
            }
        }
        if(empty($icon)){
            $icon = trim($this->baseurl,'/').'/favicon.ico';
        }
        return $icon;
    }

    public function getMeta($name){
        if(empty($this->metas)){
            $this->metas = $this->getMetas();
        }
        $ret = isset($this->metas[$name])? $this->metas[$name] : '';
        return $ret;
    }

    public function getList($selector){
        $ret = [];
        $list = $this->doc->find($selector);
        foreach($list as $a){
            $title = mb_substr(trim($a->innerText),0,100);
            $url = $a->getAttribute('href');
            if(empty($url)) continue; //空内容
            $ret[] = $this->listItem($this->url($url),$title);
        }
        return $ret;
    }

    public function getMain($selector,$del){
        $art = $this->doc->find($selector)[0];
        if($art){
            if($del){
                $del = explode(',',$del);
                foreach($del as $ad){
                    $deldom = $art->find($ad);
                    if(!empty($deldom[0])){
                        $deldom->delete();
                    }
                }
            }
            $main = $art->innerHtml;
        }
        return empty($main)? null : $this->mainItem($main);
    }

    public function getNext($selector){
        $url = '';
        if($selector){
            $dom = $this->doc->find($selector)[0];
            if(!empty($dom)){
                $url = $dom->getAttribute('href');
                $url = $this->url($url);
            }
        }
        if($url && trim($this->baseurl,'/') == trim($url,'/')){
            $url = '';
        }
        return $url;
    }

    protected function getMetas(){
        $info = [];
        $meta = $this->doc->find('meta');
        foreach($meta as $m){
            $k = $m->getAttribute('name');
            $v = $m->getAttribute('content');
            if($k){
                $info[$k] = $v;
            }
        }
        return $info;
    }
}