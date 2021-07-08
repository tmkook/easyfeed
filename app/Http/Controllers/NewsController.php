<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feed;
use App\Models\News;

class NewsController extends Controller
{
    public function discover(){
        $news = News::with('feed')->where('state',News::SUCCESS)->orderBy('updated_at','desc')->paginate(10);
        return view('discover',['news'=>$news]);
    }

    public function read($uuid){
        $news = News::with('feed')->where('uuid',$uuid)->firstOrFail();
        return view('read',['news'=>$news]);
    }
}
