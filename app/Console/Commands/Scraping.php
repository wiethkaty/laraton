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

    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $crawler = $this->login();
        print $crawler->html();
//        $crawler = $this->yoyaku($crawler);
//        $crawler = $this->show_reservation($crawler, 4);
//        print $this->client->html();
    }

    private function login()
    {
        $login_page = $this->client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
        $form = $login_page->filter('form')->form();
        $form['action'] = 'Enter';
        $form['txtProcId'] = '/menu/Login';
        $form['txtRiyoshaCode'] = env('txtRiyoshaCode');
        $form['txtPassWord'] = env('txtPassWord');
        return $this->client->submit($form);
    }

    private function yoyaku($crawler)
    {
        $crawler->filter('form')->form();
        $form['action'] = 'Enter';
        $form['txtProcId'] = '/menu/Menu';
        $form['txtFunctionCode'] = 'YoyakuQuery';
        return $this->client->submit($form);
    }

    private function show_reservation($crawler, $month)
    {
        $crawler->filter('form')->form();
        $form['action'] = 'Setup';
        $form['txtProcId'] = '/yoyaku/RiyoshaYoyakuList';
        $form['txtFunctionCode'] = 'YoyakuQuery';
        $form['selectedYoyakuUniqKey'] = '';
        $form['hiddenCorrectRiyoShinseiYM'] = '';
        $form['hiddenCollectDisplayNum'] = '5';
        $form['pageIndex'] = '1';
        $form['printedFlg'] = '';
        $form['riyoShinseiYM'] = $month;
        $form['reqDisplayInfoNum'] = '50';
        $a = $this->client->submit($form);
        print $a->html();
    }
}
