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
        $client = new Client();
        $login_page = $client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
        $login_form = $login_page->filter('form')->form();
        $login_form['txtRiyoshaCode'] = env('txtRiyoshaCode');
        $login_form['txtPassWord'] = env('txtPassWord');
        $client->submit($login_form);

        $after_login_page = $client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
//        $login_page->filter('div.news_title')->each(function ($span) {
//            echo 'タイトル：' . $span->text() . "\n";
//        });
    }
}
