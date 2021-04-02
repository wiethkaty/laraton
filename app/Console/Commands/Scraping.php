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
        $this->get_reservations();
    }

    private function login()
    {
        try {
            $login_page = $this->client->request('GET', 'https://reserve.opas.jp/osakashi/Welcome.cgi');
            $form = $login_page->filter('form')->form();
            $form['action'] = 'Enter';
            $form['txtProcId'] = '/menu/Login';
            $form['txtRiyoshaCode'] = env('txtRiyoshaCode');
            $form['txtPassWord'] = env('txtPassWord');
            $this->crawler = $this->client->submit($form);
        } catch (Exception $e) {
            error_log(__METHOD__ . ' Exception was encountered: ' . get_class($e) . ' ' . $e->getMessage());
        }
    }

    private function yoyaku()
    {
        try {
            $form = $this->crawler->filter('form')->form();
            $form['action'] = 'Enter';
            $form['txtProcId'] = '/menu/Menu';
            $form['txtFunctionCode'] = 'YoyakuQuery';
            $this->crawler = $this->client->submit($form);
        } catch (Exception $e) {
            error_log(__METHOD__ . ' Exception was encountered: ' . get_class($e) . ' ' . $e->getMessage());
        }
    }

    private function show_reservation($month)
    {
        try {
            $form = $this->crawler->filter('form#formMain')->form();
            $form['txtProcId'] = '/yoyaku/RiyoshaYoyakuList';
            $form['action'] = 'Setup';
            $form['selectedYoyakuUniqKey'] = '';
            $form['hiddenCorrectRiyoShinseiYM'] = '';
            $form['hiddenCollectDisplayNum'] = '5';
            $form['pageIndex'] = '1';
            $form['printedFlg'] = '';
            $form['riyoShinseiYM']->select(date("Ym", strtotime("1 month")));
            $form['reqDisplayInfoNum']->select('50');
            $this->crawler = $this->client->submit($form);
        } catch (Exception $e) {
            error_log(__METHOD__ . ' Exception was encountered: ' . get_class($e) . ' ' . $e->getMessage());
        }
    }

    private function get_reservations()
    {
        try {
            $this->crawler->filter('div.tablebox table tr')->each(function ($tr) {
                if (strpos($tr->text(), 'バドミントン') !== false) {
                    $children = $tr->children();
                    $date = $children->eq(0)->text();
                    $gym = $children->eq(1)->text();
                    $timeframe = $children->eq(2)->text();
                    $fee = $children->eq(4)->text();
                    echo 'date: '.$date."\n";
                    echo 'gym: '.$gym."\n";
                    echo 'timeframe: '.$timeframe."\n";
                    echo 'fee: '.$fee."\n";
                    echo "\n";
                }
            });
        } catch (Exception $e) {
            error_log(__METHOD__ . ' Exception was encountered: ' . get_class($e) . ' ' . $e->getMessage());
        }
    }
}
