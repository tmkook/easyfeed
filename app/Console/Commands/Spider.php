<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feed;
use App\Models\News;


class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:spider {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update feeds';

    /**
     * guzzle http client
     * http request handle
     * 
     * @var object
     */
    // protected $client;

    /**
     * html parser
     * 
     * @var object
     */
    protected $dom;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // $this->client = new \GuzzleHttp\Client;
        $this->dom = new \PHPHtmlParser\Dom;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        $this->{$type}();
        return 0;
    }

    public function feeds(){
        $today = date('Ymd');
        $feeds = Feed::where('update_next','<',$today)->where('state',Feed::SUCCESS)->orderBy('feed_count','desc');
        foreach($feeds->cursor() as $item){
            $this->feedNews($item,$item->url);
        }
    }

    public function feedNews($item,$target){
        try{
            $doc = $this->dom->loadFromUrl($target);
            $list = $doc->find($item->list_dom);
            //找不到内容，可能失效需要人工检查
            if(empty($list)){
                $item->state = Feed::INVALID;
                return $item->save();
            }

            //添加任务
            foreach($list as $a){
                $title = $a->text();
                $url = $a->getAttribute('href');

                //内容有误，可能失效需要人工检查
                if(empty($title) || empty($url)){
                    $item->state = Feed::INVALID;
                    return $item->save();
                }

                //检查内容是否已存在
                $url = $this->makeUrl($item,$url);
                $uuid = md5($url);
                $news = News::where('feed_id',$item->id)->where('uuid',$uuid)->first();
                if($news) return;

                //内容不存在
                $news = new News;
                $news->feed_id = $item->id;
                $news->uuid = $uuid;
                $news->url = $url;
                $news->title = $title;
                $news->state = News::SUCCESS;
                $news->save();
            }

            //翻页
            if($item->next_dom){
                $next = $doc->find($item->next_dom)[0];
                $nexturl = $next->getAttribute('href');
                if($next && $nexturl){
                    $nexturl = $this->makeUrl($nexturl);
                    if($nexturl != $target){
                        unset($doc,$list,$news);
                        $this->feedNews($item,$nexturl);
                    }
                }
            }
        
        //站点无法访问
        }catch(\Exception $e){
            echo $e->getMessage();
            $item->state = Feed::FAIL;
            return $item->save();
        }

    }

    public function news(){
        
    }

    protected function makeUrl($item,$url){
        if(empty($this->urlinfo)){
            $this->urlinfo = parse_url($item->url);
        }
        if(strpos($url,'http') !== 0){
            $url = $this->urlinfo['scheme'].'://'.$this->urlinfo['host'].$url;
        }
        return $url;
    }
}
