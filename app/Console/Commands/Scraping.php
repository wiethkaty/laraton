<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;

class Scraping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scraping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        echo "hello";
        $client = new Client();
        $goutte = $client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
        $goutte->filter('div#news_list ul')->each(function ($ul) {
            $ul->filter('li')->each(function ($li) {
                echo "-------------\n";
                echo 'タイトル：' . $li->filter('span')->text() . "\n";
//                echo 'タイトル：' . $li->filter('.s-color-twister-title-link')->attr('title') . "\n";
//                echo '参考価格：' . $li->filter('.s-price')->text() . "\n";
//                echo 'ASIN：' . $li->attr('data-asin') . "\n";
                echo "-------------\n";
            });
        });
    }
}
