<?php

namespace App\Console\Commands\Spider;

use Illuminate\Support\Facades\Log;
use App\Console\Commands\Spider\SpiderInterface;


class SpiderCli extends SpiderInterface
{
    
    protected $doc;
    protected $target;

    public function test(){
        $dom = new \PHPHtmlParser\Dom;
        $str = '<div>
                    <p>line1</p>
                    <p>line2</p>
                    <p>line3</p>
                </div>
        ';
        $doc = $dom->loadStr($str);
        $p = $doc->find('div p');
        dd(count($p));
    }
    
    public function load($target){
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $target);
        $content = $response->getBody()->getContents();
        $content = preg_replace('|<\s|','&lt;',$content); //todo:文章中含有><导致无法解析HTML,需转义正文中的符号
        $dom = new \PHPHtmlParser\Dom;
        $this->doc = $dom->loadStr($content);
        $this->target = $target;
    }

    public function clean(string $input) : string
    {
        return preg_replace_callback(
            '/(<p\b[^>]*>)([\s\S]*?)(<\/p>)/mi',
            function ($matches) {
                return sprintf('%s%s%s', $matches[1], str_replace('@@@@', '<', $matches[2]), $matches[3]);
            },
            $input
        );
    }

    public function getMeta(){
        $info = [];
        $title = $this->doc->find('title');
        if(!empty($title)){
            $info['title'] = $title[0]->text;
        }
        $meta = $this->doc->find('meta');
        foreach($meta as $m){
            $k = $m->getAttribute('name');
            $v = $m->getAttribute('content');
            if(empty($k)){
                $k = $m->getAttribute('property');
                if($k=='og:image'){
                    $k = 'icon';
                    $v = $this->url($v);
                }
            }
            if($k){
                $info[$k] = $v;
            }
        }
        $link = $this->doc->find('link');
        foreach($link as $m){
            $k = $m->getAttribute('rel');
            if(strpos($k,'icon') > 0){
                $info['icon'] = $this->url($m->getAttribute('href'));
            }
        }
        return $info;
    }

    public function getList($selector){
        $ret = [];
        $list = $this->doc->find($selector);
        foreach($list as $a){
            $title = trim($a->innerText);
            $url = $a->getAttribute('href');
            if(empty($url)) continue; //空内容
            $ret[] = $this->listItem($this->url($url),$title);
        }
        return $ret;
    }

    public function getMain($selector){
        $art = $this->doc->find($selector);
        $main = $art->innerHtml;
        return empty($main)? null : $this->mainItem($main);
    }

    public function getNext($selector){
        $url = '';
        if($selector){
            $dom = $this->doc->find($selector);
            if(!empty($dom[0])){
                $url = $dom[0]->getAttribute('href');
                $url = $this->url($url);
            }
        }
        if(trim($this->baseurl,'/') == trim($url,'/')){
            $url = '';
        }
        return $url;
    }
}