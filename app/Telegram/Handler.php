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
                $this->login('pupil');
                break;
            case str_contains($text, '@galeshchynalitsey.ukr.education'):
                $this->login('teacher');
                break;
            case str_contains($text, '@gmail.com'):
                $this->chat->message("Я працюю лише зі шкільною електронною адресою")->send();
                break;
        }
    }

    public function login($status): void
    {
        switch ($status) {
            case 'pupil':
                $this->chat->message("Учень")->send();
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
