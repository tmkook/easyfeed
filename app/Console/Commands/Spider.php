<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feed;
use App\Models\News;
use App\Models\Task;

class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:spider {method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'spider fetch url';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $method = $this->argument('method');
        $this->{$method}();
        return 0;
    }

    public function list(){
        $feed = Task::with('feed')->where('mode',TASK::MODE_LIST)->inRandomOrder()->first();
        if(empty($feed)) return;
        $content = new \Tmkook\EasyContent($feed->feed->url);
        $html = new \Tmkook\EasyHTML($feed->url);
        $list = $html->getList();
        foreach($list['list'] as $url){
            $url = $content->url($url);
            $uuid = md5($url);
            $has = Task::where('uuid',$uuid)->withTrashed()->first();
            if($has) continue;
            $task = new Task;
            $task->uuid = $uuid;
            $task->url = $url;
            $task->feed_id = $feed->feed_id;
            $task->mode = TASK::MODE_CONTENT;
            $task->save();
        }
        foreach($list['page'] as $url){
            $url = $content->url($url);
            $uuid = md5($url);
            $has = Task::where('uuid',$uuid)->withTrashed()->first();
            if($has) continue;
            $task = new Task;
            $task->uuid = $uuid;
            $task->url = $url;
            $task->feed_id = $feed->feed_id;
            $task->mode = TASK::MODE_LIST;
            $task->save();
        }
        $feed->delete();
    }

    public function content(){
        $task = Task::with('feed')->where('mode',TASK::MODE_CONTENT)->inRandomOrder()->first();
        $has = News::where('uuid',$task->uuid)->withTrashed()->first();
        if($has) return;
        $html = new \Tmkook\EasyHTML($task->url);
        $content = new \Tmkook\EasyContent($task->feed->url,$html->getContent());
        $news = new News;
        $news->url = $task->url;
        $news->uuid = $task->uuid;
        $news->feed_id = $task->feed_id;
        $news->title = $html->getTitle();
        $news->cover = $content->getImages(3);
        $news->summary = $content->getText(100);
        $news->content = $content->getContent();
        $news->save();
        $task->delete();
    }

    public function feed(){
        $feed = Feed::where('state',1)->where('update_wait','<',time())->first();
        if(empty($feed)) return;
        $content = new \Tmkook\EasyContent($feed->url);
        $html = new \Tmkook\EasyHTML($feed->url);
        $list = $html->getList();
        $hasnew = false;
        foreach($list['list'] as $url){
            $url = $content->url($url);
            $uuid = md5($url);
            $has = Task::where('uuid',$uuid)->withTrashed()->first();
            if($has) continue;
            $hasnew = true;
            $task = new Task;
            $task->uuid = $uuid;
            $task->url = $url;
            $task->feed_id = $feed->id;
            $task->mode = TASK::MODE_CONTENT;
            $task->save();
        }
        foreach($list['page'] as $url){
            $url = $content->url($url);
            $uuid = md5($url);
            $has = Task::where('uuid',$uuid)->withTrashed()->first();
            if($has) continue;
            $hasnew = true;
            $task = new Task;
            $task->uuid = $uuid;
            $task->url = $url;
            $task->feed_id = $feed->id;
            $task->mode = TASK::MODE_LIST;
            $task->save();
        }
        $day = 1;
        if(!$hasnew){
            $ts = $feed->updated_wait - strtotime($feed->updated_at);
            $day += intval($ts / 86400);
        }
        $feed->update_wait = time() + 86400 * $day;
        $feed->save();
    }
}