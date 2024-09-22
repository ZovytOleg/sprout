<?php
/**
 * Handler.php
 * php version 7.4.1
 *
 * @category
 * @package  #path
 * @author   Oleg Chingaev <ochingaev@sbase.team>
 * @version  GIT:<v.0.0.0>
 * @datetime 12.09.2024
 **/

namespace App\Telegram;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Handler extends WebhookHandler
{
    public function start():void
    {
        $user_name = $this->message->from()->firstName();

        $this->chat->message("Привіт, $user_name! 👋")->send();
        #sleep(1);
        $this->chat->message("Мене звати — <strong>СПРАУТ, і я бот-асистент Новогалещинського ліцею!</strong> \n\nЯ допоможу тобі отримати доступ до актуальної інформації, важливих новини та ресурсів для навчання в нашому ліцеї.")->send();
        #sleep(1);
        $this->chat->message("Оберіть, будь ласка, свій статус користувача!")
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('📚 Учень')->action('status')->param('status', 'pupil'),
                    Button::make('🎓 Учитель')->action('status')->param('status', 'teacher'),
                    Button::make('💼 Адміністрація')->action('admin'),
                    Button::make('👨‍👩‍👧‍👦 Батьки')->action('family'),
                ])
            )->send();
    }

    public function menu(): void
    {
        $menu = array(
            'students' => array(
                '📚 Розклад уроків' => 'lessons',
                '📋 Графік навчання' => 'study',
                '🍽 Меню' => 'dinner',
                '🚌 Розклад руху автобусів' => 'bus'
            ),
            'teachers' => array(
                '👤 Чергування' => 'alternation',
            ),
        );

        $test = 'teachers';
        $buttons = [];
        foreach ($menu as $status => $types) {
            foreach ($types as $type => $subtype) {
                $buttons[] = Button::make($type)->action('schedule')->param('type', $subtype);
            }
            if ($test == 'pupil'){
                break;
            }
        }

        $this->chat->message("Яку актуальну інформацію бажаєш отримати?")
            ->keyboard(Keyboard::make()->buttons($buttons))->send();
    }

    public function schedule():void
    {
        $schedule = $this->data->get('type');

        switch ($schedule) {
            case 'lessons':
                $this->chat->message("Сталий розклад уроків на I навчальний семестр.")
                    ->photo(Storage::path('\public\data\images\schedule_lessons.jpg'))
                    ->send();
                break;
            case 'study':
                $this->chat->message("Який період навчання тебе цікавить?")
                    ->keyboard(
                        Keyboard::make()->buttons([
                            Button::make('🌜 І семестр')->action('periodStudy')->param('period', 'first'),
                            Button::make('🌛 ІІ семестр')->action('periodStudy')->param('period', 'second'),
                            Button::make('🌝 Разом')->action('periodStudy')->param('period', 'both'),
                        ])
                    )->send();
                break;
            case 'bus':
                $imgCount = count(glob(Storage::path("\public\data\images\schedule-bus\*.jpg")));

/*                $images = [];
                for($i = 1; $i < $imgCount; $i++){

                }*/
                $this->chat->message("Сталий розклад руху автобусів на I навчальний семестр.")
                    ->mediaGroup([
                        [
                            'type' => 'photo',
                            'media' => 'https://cdn.motor1.com/images/mgl/P3nO74/s1/2000-nissan-skyline-r34-gt-r-by-kaizo-industries-driven-by-paul-walker-in-fast-and-furious-bonham-s-auction.jpg',
                        ],
                        [
                            'type' => 'photo',
                            'media' => 'https://cdn.motor1.com/images/mgl/P3nO74/s1/2000-nissan-skyline-r34-gt-r-by-kaizo-industries-driven-by-paul-walker-in-fast-and-furious-bonham-s-auction.jpg',
                        ]
                    ])
                    ->send();
                break;

        }
    }

    public function periodStudy(): void
    {
        $period = $this->data->get('period');
        $data = json_decode(Storage::get('\public\data\schedule_study.json'), true);
       # Log::info(json_decode(Storage::get('\public\data\schedule_study.json'), JSON_UNESCAPED_UNICODE));

        $this->chat->message('<strong>'.$data['schedule']["title"].'</strong>')->send();

/*        $message = '
                    <strong>'.$data['schedule'][$period]['pair']['title'].'</strong>'. "\n".
                    $data['schedule'][$period]['pair']['classes']. "\n\n".
                    $data['schedule'][$period]['pair']['date']['september'][0]
        ;*/

        $message = '';
        /*for ($i = 0; $i < 2; $i++){
            for ($j = 0; $j < 3; $i++){
                foreach ($j as $item){
                    $message.= ;
                }
            }
        }*/

        foreach ($data['schedule']["first"] as $item){
            Log::info($item, JSON_UNESCAPED_UNICODE);
        }
       # Log::info($message, JSON_UNESCAPED_UNICODE));

      #  $this->chat->message($message)->send();

/*        foreach ($data['schedule'][$period] as $students) {
            Log::info(json_decode($this->chat->message($students['classes'])->send(), JSON_UNESCAPED_UNICODE));
            $this->chat->message($students['first']['pair'][$type])->send();
        }*/

       #$this->chat->message($data['schedule']['first']['pair']['classes'])->send();


    #    Log::info(json_decode(Storage::get('\public\data\schedule_study.json'), JSON_UNESCAPED_UNICODE));

    }


    public function status(): void
    {
        $status = $this->data->get('status');

        if ($status == 'pupil'){
            $this->chat->message("Напиши свою учнівську електронну адресу, щоб я розумів, з ким спілкуюсь")->send();
        }
        if ($status == 'teacher'){
            $this->chat->message("Напишіть свою корпоративну електронну адресу, щоб я розумів, з ким спілкуюсь")->send();
        }
    }

    public function handleChatMessage(Stringable|\Illuminate\Support\Stringable $text): void
    {
        switch (true) {
            case str_contains($text, 'pupil') AND str_contains($text, '@galeshchynalitsey.ukr.education'):
                $this->login($text,'pupil');
                break;
            case str_contains($text, '@galeshchynalitsey.ukr.education'):
                $this->login('teacher');
                break;
            case str_contains($text, '@gmail.com'):
                $this->chat->message("Я працюю лише зі шкільною електронною адресою")->send();
                break;
        }
    }

    public function login($text, $status): void
    {
        switch ($status) {
            case 'pupil':
                if ($text == 'pupil34@galeshchynalitsey.ukr.education'){
                    $this->chat->message("Доступ отримано")->send();
                }else{
                    $this->chat->message("Вашу ел.адресу не знайдено. Спробуйте ще раз!")->send();
                }
                break;
            case 'teacher':
                $this->chat->message("Учитель")->send();
                break;
            case 'personal':
                $this->chat->message("Особиста")->send();
                break;
        }
    }

}
