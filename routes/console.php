<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('base', function () {
    /** @var \DefStudio\Telegraph\Models\TelegraphBot $sprout */
    $sprout = \DefStudio\Telegraph\Models\TelegraphBot::find(1);

    $sprout->registerCommands([
        'menu' => 'Головне меню',
        'feedback' => 'Пропозиції, відгуки, скарги',
        'help' => 'Інструкція та перелік команд бота',
        'support' => 'Звернутися до техпідтримки'
    ])->send();

});
