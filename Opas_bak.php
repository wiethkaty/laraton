<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class OpasBak extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'date', // 利用日
        'sc', // スポーツセンター
        'room', // 体育場名
        'timeframe', // 時間帯
        'jenre', // ジャンル（バドミントン）
        'fee', // 利用料金
        'checked_in', // 打ち合わせ済み
        'canceled', // キャンセル
    ];
}
