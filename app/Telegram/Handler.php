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
        $this->chat->message("Яку актуальну інформацію ти хочеш отримати?")
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('📚 Розклад уроків')->action('schedule')->param('type', 'lessons'),
                    Button::make('📋 Графік навчання')->action('schedule')->param('type', 'study'),
                    Button::make('🍽 Меню')->action('schedule')->param('type', 'dinner'),
                    Button::make('🚌 Розклад руху автобусів')->action('schedule')->param('type', 'bus'),
                ])
            )->send();
    }

    public function schedule():void
    {
        $schedule = $this->data->get('type');

        if ($schedule == 'lessons') {
/*            $json = Storage::disk('local')->get('schedule_lessons.json');
            $json = json_decode($json, true);
            dd($json);*/
            $this->chat->message("Сталий розклад на I навчальний семестр.")->photo(Storage::path('\public\data\images\schedule_lessons.jpg'))->send();
        }
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
