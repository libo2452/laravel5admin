<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Article;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Readability\Readability;
use GuzzleHttp\Client;
use Log;
use Cache;

class GetUrlContentAndSaveToArticle extends Job implements ShouldQueue {
    use InteractsWithQueue , SerializesModels;
    private $uri;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uri) {
        //
        $this->uri = $uri;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        if ( Article::where('link' , $this->uri)->count() > 0 ) {
            Log::info('文章已存在' . $this->uri);
            return false;
        }
        $contentCacheKey = __CLASS__.md5($this->uri);
        $uri = $this->uri;
        $htmlContent = Cache::get($contentCacheKey,function() use($contentCacheKey,$uri){
            $client = new Client();
            $response = $client->get($uri);
            $htmlContent = $response->getBody()->getContents();
            Cache::put($contentCacheKey,$htmlContent,60*24*30);
            return $htmlContent;
        });
        $readability = new Readability($htmlContent);
        $result = $readability->init();
        if ( !$result ) {
            Log::info('解析uri正文失败' . $this->uri);
            return false;
        }
        $title = $readability->getTitle()->textContent;
        $content = $readability->getContent()->textContent;

        $data = [
            'link'    => $this->uri ,
            'title'   => $title ,
            'content' => $content ,
        ];
        Article::create($data);
        return true;
    }
}
