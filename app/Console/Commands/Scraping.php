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
    protected $crawler;

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
        $this->login();
        $this->yoyaku();
        $this->show_reservation(4);
    }

    private function login()
    {
        $login_page = $this->client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
        $form = $login_page->filter('form')->form();
        $form['action'] = 'Enter';
        $form['txtProcId'] = '/menu/Login';
        $form['txtRiyoshaCode'] = env('txtRiyoshaCode');
        $form['txtPassWord'] = env('txtPassWord');
        $this->crawler = $this->client->submit($form);
    }

    private function yoyaku()
    {
        $form = $this->crawler->filter('form')->form();
        $form['action'] = 'Enter';
        $form['txtProcId'] = '/menu/Menu';
        $form['txtFunctionCode'] = 'YoyakuQuery';
        $this->crawler = $this->client->submit($form);
    }

    private function show_reservation($month)
    {
        $form = $this->crawler->filter('form#formMain')->form();
        $form['txtProcId'] = '/yoyaku/RiyoshaYoyakuList';
//        $form['txtFunctionCode'] = 'YoyakuQuery';
        $form['action'] = 'Setup';
        $form['selectedYoyakuUniqKey'] = '';
        $form['hiddenCorrectRiyoShinseiYM'] = '';
        $form['hiddenCollectDisplayNum'] = '5';
        $form['pageIndex'] = '1';
        $form['printedFlg'] = '';
        $form['riyoShinseiYM']->select(date("Ym"));
        $form['reqDisplayInfoNum']->select('50');
        $this->crawler = $this->client->submit($form);
        var_export($this->crawler->html());
    }
}
