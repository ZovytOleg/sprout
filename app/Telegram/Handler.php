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

use App\Models\User;
use DefStudio\Telegraph\Facades\Telegraph;
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
                    Button::make('🎓 Учитель')->action('teacher'),
                    Button::make('💼 Адміністрація')->action('admin'),
                    Button::make('👨‍👩‍👧‍👦 Батьки')->action('family'),
                    Button::make('😎 Гість')->action('guest'),
                ])
            )->send();
    }
    public function status(): void
    {
        $status = $this->data->get('status');

        if ($status == 'pupil'){
            $this->chat->message("Напиши свою учнівську електронну адресу, щоб я розумів, з ким спілкуюсь")->send();

/*            $attemps = 3;
            for ($i = 0; $i < $attemps; $i++) {
                if ($this->message->text() == 'pupil@gal'){
                    $this->reply('tak');
                } else {
                    $this->reply('ni');
                    $attemps++;
                }
            }*/

/*            $this->chat->message("Оберіть свій клас, щоб отримувати оголошення, сповіщення та іншу важливу інформацію.")
                ->keyboard(
                    Keyboard::make()->buttons([
                        Button::make('5-9 клас')->action('login'),
                        Button::make('10-11 клас')->action('login'),
                    ])
                )->send();*/
        }

    }

    public function handleChatMessage(Stringable|\Illuminate\Support\Stringable $text): void
    {
        switch (true) {
            case strpos($text, 'pupil'):
                $this->login('pupil');
                break;
            case strpos($text, '@galeshchynalitsey.ukr.education'):
                $this->login('teacher');
                break;
            case strpos($text, '@gmail.com'):
                $this->chat->message("Особиста")->send();
                break;
        }
    }


    public function login($status): void
    {
        switch (true) {
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
