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

use DateTime;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Random\RandomException;

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
                '⚠️ Статус тривоги' => 'alert',
                '🚌 Розклад руху автобусів' => 'bus'
            ),
            'teachers' => array(
                '👤 Чергування' => 'duty',
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

    /**
     * @throws RandomException
     */
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
                            Button::make('🌜 І семестр')->action('scheduleStudy')->param('period', 'first'),
                            Button::make('🌛 ІІ семестр')->action('scheduleStudy')->param('period', 'second')
                        ])
                    )->send();

                break;

            case 'bus':
                $this->chat->message("Обери маршрут, який тебе цікавить")
                    ->keyboard(
                        Keyboard::make()->buttons([
                            Button::make('📍 №1 (Школа - Солониця - Сохинівка)')->action('scheduleBus')->param('route', '1'),
                            Button::make('📍 №2 (Школа - Заруддя - Ревівка)')->action('scheduleBus')->param('route', '2'),
                            Button::make('📍 №3 (Школа - Трудовик - Горбані)')->action('scheduleBus')->param('route', '3'),
                            Button::make('📍 №4 (Школа - Геологія - Геологічна)')->action('scheduleBus')->param('route', '4'),
                            Button::make('📍 №5 (Школа - Машзавод)')->action('scheduleBus')->param('route', '5')                        ])
                    )->send();

                break;

            case 'alert':
                function alertStatus()
                {
                    $headers = array(
                        'accept: application/json',
                        'Authorization: 0469f400:529254bbb50fb701822bb42758595aca'
                    );

                    $ch = curl_init('https://api.ukrainealarm.com/api/v3/alerts/19');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_HEADER, false);

                    $res = curl_exec($ch);
                    curl_close($ch);

                    return json_decode($res, true);
                }

                function messageAlertStatus($data, $chat): void
                {
                    $gifsDeactivateAlert = [
                        'https://media.tenor.com/LoNsnAUGbEwAAAAM/dancing-hedgehog.gif',
                        'https://media.tenor.com/5VHBPuAZrOIAAAAM/ohyeah-cute.gif',
                        'https://c.tenor.com/uryKOd89Z3UAAAAd/tenor.gif',
                        'https://c.tenor.com/r0R0N3dI3kIAAAAd/tenor.gif',
                        'https://c.tenor.com/o_tU_5zczwcAAAAd/tenor.gif',
                        'https://c.tenor.com/IRVIVy9sstQAAAAd/tenor.gif',
                        'https://c.tenor.com/0i9MjAMd6D0AAAAC/tenor.gif',
                        'https://c.tenor.com/gotOLnyvy4YAAAAC/tenor.gif',
                        'https://c.tenor.com/y0zptlFKiYIAAAAC/tenor.gif',
                        'https://c.tenor.com/yaNqkG8o9UcAAAAC/tenor.gif'
                    ];

                    Log::info($data, (array)JSON_UNESCAPED_UNICODE);

                    if (!empty($data[0]['activeAlerts'])){
                        $chat->message("❗<strong>В Полтавській області зараз повітряна тривога.\n\nЗалишайся в безпечному місці! </strong>❗️")
                            ->send();
                    } else {
                        $chat->message('🟢 <strong>Повітряної тривоги в Полтавській області немає</strong> 🟢')
                            ->animation($gifsDeactivateAlert[rand(0, count($gifsDeactivateAlert)-1)])
                            ->send();
                    }
                }

                $data = alertStatus();

                if(empty($data) OR $data == ''){
                    $attempts = 0;

                    while(empty(alertStatus())) {
                        $this->chat->message("Статус тривоги в області не вдалося визначити. 😔 \n\nАвтоматичний повторний запит через 5 секунд...⏳")->send();

                        sleep(5);
                        alertStatus();
                      Log::info(alertStatus(), (array)JSON_UNESCAPED_UNICODE);
                      if (!empty(alertStatus())) {
                          messageAlertStatus($data, $this->chat);
                      }elseif ($attempts > 9){
                          $this->chat->message("Відповідь від офіційного сайту з тривогами не було отримано. 😔 \n\nСпробуйте трішки пізніше.")->send();

                          break;
                      } else {
                          $attempts++;
                        }

                    }

                }else{
                    messageAlertStatus($data, $this->chat);
                }
            break;

            case 'duty':
                $this->chat->message("Графік чергування вчителів та класів по Новогалещинському ліцею на І семестр 2024/2025 н.р.:")
                    ->keyboard(
                        Keyboard::make()->buttons([
                            Button::make('📋 Поточний тиждень')->action('scheduleDuty')->param('type', 'week'),
                            Button::make('📋 Весь семестр')->action('scheduleDuty')->param('type', 'all'),])
                    )->send();

                break;

        }
    }

    public function scheduleStudy(): void
    {
        $period = $this->data->get('period');

        $data = json_decode(Storage::get('\public\data\schedule_study.json'), true);
        $this->chat->message('<strong>'.$data['schedule']['title'].'</strong>')->send();

        foreach ($data['schedule'][$period] as $typeStudents){
            $message = '<strong>🔵 '.$typeStudents['title'].' 🔵</strong>'. "\n".
                "<blockquote>".$typeStudents['classes']."</blockquote>";

            $currentMonthIndex = 0;
            if (is_array($typeStudents['date'])) {
                $currentMonth = array_search(date("F"), array_keys($typeStudents['date']));

                foreach ($typeStudents['date'] as $dates) {
                    if ($currentMonthIndex == $currentMonth) {
                        $message.= "\n\n<blockquote>";
                        $message.= "<strong>".current($dates)."</strong>";
                    }else{
                        $message.= "\n\n<strong>".current($dates)."</strong>";
                    }

                    for($i  = 1; $i < count($dates); $i++) {
                        if ($dates[$i] == date("d.m", strtotime("+1 day"))) {
                            $message.= '<strong>'.$dates[$i].' (завтра)</strong> , ';
                        } elseif ($dates[$i] == last($dates)){
                            $message.= $dates[$i]. ";";
                        } else{
                            $message.= $dates[$i]. ', ';
                        }
                    }

                    if ($currentMonthIndex == $currentMonth) {
                        $message.= "</blockquote>";
                    }
                    $currentMonthIndex++;
                }
                $this->chat->message($message)->send();
            } else{
                $this->chat->message($typeStudents['date'])->send();

                break;
            }

        }
    }

    public function scheduleBus(): void
    {
        $route = $this->data->get('route');

        $data = json_decode(Storage::get('\public\data\schedule_bus.json'), true);

        $route = $data['routes'][$route-1];
        $message  = "<blockquote><strong>📍🗺️ Маршрут №" . $route['routeNumber'] . "</strong></blockquote>\n";
        $message .= "<strong>🚌 Модель автобуса: </strong>" . $route['busModel'] . "\n";
        $message .= "<strong>⭐ Номер: </strong>" . $route['registrationNumber'] . "\n";
        $message .= "<strong>😎 Водій: </strong>" . $route['driver'] . "\n\n";
        $this->chat->message($message)->send();

        $message = '';
        foreach ($route['stops'] as $stop) {
            $message .= "<blockquote><strong>📌 Зупинка: </strong>" . $stop['location'] . "</blockquote>\n";
            $message .= "<strong>🟢 Час прибуття: </strong>" . ($stop['arrivalTime'] ?? '—') . "\n";
            $message .= "<strong>🔴 Час відправлення: </strong>" . ($stop['departureTime'] ?? '—') . "\n";
            $message .= "<strong>⏳ Тривалість зупинки (хв): </strong>" . ($stop['stopDurationMinutes'] ?? '—') . "\n\n";
         }

        $this->chat->message($message)
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('↩️ Повернутися')->action('schedule')->param('type', 'bus')
                ])
            )->send();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function scheduleDuty(): void
    {
        $type = $this->data->get('type');

        $data = json_decode(Storage::get('\public\data\schedule_duty.json'), true);

        // Поточна дата і дата через 7 днів


// Функція для перевірки, чи дата в межах наступного тижня
        function isWithinNextWeek($date): bool
        {
            $currentDate = new DateTime();
            $endDate = (clone $currentDate)->modify('+7 days');
            $checkDate = DateTime::createFromFormat('d.m', $date);

            if($checkDate){
                $checkDate->setDate($currentDate->format("Y"), $checkDate->format('m'), $checkDate->format('d'));

                return $checkDate >= $currentDate && $checkDate <= $endDate;

            }

            return false;

        }

        Log::info(isWithinNextWeek("06.10"), (array)JSON_UNESCAPED_UNICODE);

// Виведення інформації для наступного тижня
        if ($type == 'week'){
            $message = '';
            $filteredDates = [];

            foreach ($data['dutySchedule'] as $duty) {

                foreach ($duty['dates'] as $date) {
                    if (isWithinNextWeek($date)) {
                        $filteredDates[] = $date;

                        $message .= "Клас: " . $duty['class'] . "\n";
                        $message .= "Старший черговий: " . $duty['seniorDuty'] . "\n";
                        $message .= "Чергові на 1 поверсі: " . implode(", ", $duty['firstFloor']) . "\n";
                        $message .= "Чергові на 2 поверсі: " . implode(", ", $duty['secondFloor']) . "\n";
                        $message .= "Чергові на 3 поверсі: " . implode(", ", $duty['thirdFloor']) . "\n";
                        $message .= "Черговий по подвір'ю: " . $duty['yard'] . "\n";
                        $message .= "----------------------\n";
                    }
                }

              # $filteredDates = array_filter($duty['dates'], 'isWithinNextWeek');

/*                if (!empty($filteredDates)) {
                    $message .= "Дати чергування: " . implode(", ", $filteredDates) . "\n";
                    foreach ($filteredDates as $date) {
                        $message .= "Клас: " . $duty['class'] . "\n";
                        $message .= "Старший черговий: " . $duty['seniorDuty'] . "\n";
                        $message .= "Чергові на 1 поверсі: " . implode(", ", $duty['firstFloor']) . "\n";
                        $message .= "Чергові на 2 поверсі: " . implode(", ", $duty['secondFloor']) . "\n";
                        $message .= "Чергові на 3 поверсі: " . implode(", ", $duty['thirdFloor']) . "\n";
                        $message .= "Черговий по подвір'ю: " . $duty['yard'] . "\n";
                        $message .= "----------------------\n";
                    }
                }*/
            }
            Log::info($filteredDates, (array)JSON_UNESCAPED_UNICODE);

            $this->chat->message($message)
                ->keyboard(
                    Keyboard::make()->buttons([
                        Button::make('↩️ Повернутися')->action('schedule')->param('type', 'duty')
                    ])
                )->send();
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
