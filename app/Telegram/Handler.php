<?php
/**
 * Handler.php *  version 7.4.1
 *
 * @category
 * @package  #path
 * @author   Oleg Chingaev <ochingaev@sbase.team>
 * @version  GIT:<v.0.0.0>
 * @datetime 12.09.2024
 **/

namespace App\Telegram;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserTG;
use DateTime;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Telegraph;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Stringable;

class Handler extends WebhookHandler
{
    public function start(): void
    {
        $user_name = $this->message->from()->firstName();

        $this->chat->message("Привіт, $user_name! 👋")->send();
        #sleep(1);
        $this->chat->message("Мене звати — <strong>СПРАУТ, і я бот-асистент Новогалещинського ліцею!</strong> \n\nЯ допоможу тобі отримати доступ до актуальної інформації, важливих новини та ресурсів для навчання в нашому ліцеї.")->send();
        #sleep(1);
        $this->chat->message("Оберіть, будь ласка, свій статус користувача!")
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('📚 Учень')->action('login')->param('status', 'student'),
                    Button::make('🎓 Учитель')->action('login')->param('status', 'teacher'),
                    Button::make('💼 Адміністрація')->action('login')->param('status', 'admin'),
                    Button::make('👨‍👩‍👧‍👦 Батьки')->action('login')->param('status', 'family'),
                    Button::make('🕵🏻‍♀️ Гість')->action('login')->param('status', 'guest'),
                ])
            )->send();
    }

    public function login(): void
    {
        $status = $this->data->get('status');
        $ex = '';

        if ($status == 'student' || $status == 'teacher' || $status == 'admin') {
            $cmd = '/user';

            if ($status == 'student') {
                $ex = '/user pupil007 s8kP7LTbvt';
            }
            if ($status == 'teacher') {
                $ex = '/user cool.teacher 9U18YXUbyivgQ990Zf7d';
            }
            if ($status == 'admin') {
                $cmd = '/admin';
                $ex = '/admin the.best.admin 9U18YXUbyivgQ990Zf7d';
            }

            $this->chat->message("Напиши команду $cmd та через пробіли першу частину корпоративної електронної адреси (до символа @) і особистий токен-ключ\n\nНаприклад: $ex")
                ->send();
        } else {
            $this->userAuth("", "", "", $status, $this->chat);
        }
    }

    public function userAuth($db = '', $email = '', $token = '', $status, $chat)
    {
        function createUser($user = '', $status, $chat): void
        {
            $roles = [
                'student' => 1,
                'teacher' => 2,
                'admin' => 3,
                'family' => 4,
                'guest' => 5
            ];

            if ($status == "student" || $status == 'teacher' || $status == 'admin') {
                $field = $status . '_id';

                DB::table('users_tg')
                    ->insert([
                        'user_role' => $roles[$status],
                        $field => $user->id,
                        'chat_id' => $chat->chat_id,
                        'chat_name' => $chat->name,
                    ]);

                DB::table($status . "s")
                    ->where('email', $user->email)
                    ->update([
                        'is_verified' => true
                    ]);
            } else {
                DB::table('users_tg')
                    ->insert([
                        'user_role' => $roles[$status],
                        'chat_id' => $chat->chat_id,
                        'chat_name' => $chat->name,
                    ]);
            }

            $currentRole = DB::table('roles')->where('id', $roles[$status])->first();
            $chat->message("Вам було встановлено роль <strong>" . $currentRole->role_name . "</strong>\n\nВикористайте команду /menu, щоб переглянути доступні вам можливості")->send();
        }

        if (DB::table('users_tg')->where('chat_id', $chat->chat_id)->exists()) {
            $chat->message("Ви вже зареєстровані в системі")->send();
        } else {
            if ($status == 'student' || $status == 'teacher' || $status == 'admin') {
                $user = DB::table($db)
                    ->where('email', "$email")
                    ->first();
                if ($user) {
                    if ($user->token == $token) {
                        createUser($user, $status, $this->chat);
                    } else {
                        $chat->message("Помилка в токені")->send();
                    }
                } else {
                    $chat->message("Помилка в email")->send();
                }
            } else {
                createUser("", $status, $this->chat);
            }
        }

        return false;
    }

    public function user($data): void
    {
        /*        Log::info(json_encode($data, JSON_UNESCAPED_UNICODE));*/
        $data = explode(" ", $data);
        if (count($data) == 2) {
            $email = mb_strtolower($data[0]) . "@galeshchynalitsey.ukr.education";
            $token = $data[1];

            if (str_contains($email, 'pupil')) {
                $db = 'students';
                $status = 'student';
            } else {
                $db = 'teachers';
                $status = 'teacher';
            }

            $this->userAuth($db, $email, $token, $status, $this->chat);
        } else {
            $this->chat->message("Ви не вказали додаткову інформацію (пошту/токен)")
                ->send();
        }
    }

    public function admin($data): void
    {
        $data = explode(" ", $data);
        if (count($data) == 2) {
            $email = mb_strtolower($data[0]) . "@galeshchynalitsey.ukr.education";
            $token = $data[1];

            $this->userAuth('admins', $email, $token, "admin", $this->chat);
        } else {
            $this->chat->message("Ви не вказали додаткову інформацію (пошту/токен)")
                ->send();
        }
    }

    public function menu(): void
    {
        if (UserTG::where('chat_id', $this->chat->chat_id)->exists()) {
            $user = UserTG::where('chat_id', $this->chat->chat_id)->first();
            $role = $user->role->role_name;

            $student = array(
                'Учень' => array(
                    '📚 Розклад уроків' => 'lessons',
                    '📋 Графік навчання' => 'study',
                    '🍽 Меню' => 'dinner',
                    '⚠️ Статус тривоги' => 'alert',
                    '🚌 Розклад руху автобусів' => 'bus',
                    '👤 Чергування' => 'duty',
                )
            );
            $teacher = array(
                'Вчитель' => array(
                    '⚙️ Команди Вчителя' => 'settings',
                )
            );

            $admin = array(
                'Адміністрація' => array(
                    '⚙️ Команди Адміністратора' => 'settings',
                )
            );

            $menu = [];
            if ($role == 'Учень' || $role == 'Вчитель' || $role == 'Адміністрація') {
                $menu[] = $student;

                if ($role == 'Вчитель') {
                    $menu[] = $teacher;
                }
                if ($role == 'Адміністрація') {
                    $menu[] = $teacher;
                    $menu[] = $admin;
                }
            }

            # Log::info(json_encode($menu, JSON_UNESCAPED_UNICODE));

            $buttons = [];
            foreach ($menu as $roles) {
                foreach ($roles as $role => $types) {
                    foreach ($types as $type => $command) {
                        $buttons[] = Button::make($type)->action('command')->param('type', $command);
                    }
                }
            }
            $role == 'Адміністрація'?$buttons[] = Button::make('🌐 Увійти на сайті')->url("https://www.google.com/"):"";

            $this->chat->message("Яку актуальну інформацію бажаєш отримати?")
                ->keyboard(Keyboard::make()->buttons($buttons))->send();
        } else {
            $this->chat->message("Перед тим, як дізнатися якусь інформацію, мені потрібно знати твою роль")->send();
        }
    }

    /**
     * @throws RandomException
     */
    public function command(): void
    {
        $schedule = $this->data->get('type');
        $user = UserTG::where('chat_id', $this->chat->chat_id)->first();
        $role = $user->role->role_name;

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
                            Button::make('📍 №5 (Школа - Машзавод)')->action('scheduleBus')->param('route', '5')])
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

                    if (!empty($data[0]['activeAlerts'])) {
                        $chat->message("❗<strong>В Полтавській області зараз повітряна тривога.\n\nЗалишайся в безпечному місці! </strong>❗️")
                            ->send();
                    } else {
                        $chat->message('🟢 <strong>Повітряної тривоги в Полтавській області немає</strong> 🟢')
                            ->animation($gifsDeactivateAlert[rand(0, count($gifsDeactivateAlert) - 1)])
                            ->send();
                    }
                }

                $data = alertStatus();

                if (empty($data) or $data == '') {
                    $attempts = 0;

                    while (empty(alertStatus())) {
                        $this->chat->message("Статус тривоги в області не вдалося визначити. 😔 \n\nАвтоматичний повторний запит через 5 секунд...⏳")->send();
                        sleep(5);
                        alertStatus();
                        Log::info(alertStatus(), (array)JSON_UNESCAPED_UNICODE);
                        if (!empty(alertStatus())) {
                            messageAlertStatus($data, $this->chat);
                        }

                        if ($attempts >= 5) {
                            $this->chat->message("Відповідь від офіційного сайту з тривогами не було отримано. 😔 \n\nСпробуйте трішки пізніше.")->send();
                            break;
                        }
                        $attempts++;
                    }

                } else {
                    messageAlertStatus($data, $this->chat);
                }
                break;

            case 'duty':
                $this->chat->message("Графік чергування вчителів та класів по Новогалещинському ліцею на І семестр 2024/2025 н.р.:")
                    ->keyboard(
                        Keyboard::make()->buttons([
                            Button::make('📌 Сьогодні/Завтра')->action('scheduleDuty')->param('type', 'tomorrow'),
                            Button::make('📅 Поточний тиждень')->action('scheduleDuty')->param('type', 'week'),
                            Button::make('📋 Семестр')->action('scheduleDuty')->param('type', 'all'),])
                    )->send();

                break;

            case 'settings':
                $commands = array(
                    'Вчитель' => array(
                        '/send_message [повідомлення] - надіслати повідомлення для всіх учнів свого класу'
                    ),
                    'Адміністрація' => array(
                        '/send_message [учням/вчителям/всім] [повідомлення] - надіслати повідомлення',
                        '/feedback_reports [кількість останніх/за замов. всі] - переглянути скарги, пропозиції, ідеї',
                        "/add_teacher [ім'я] [прізвище] [пошта] [токен] [предмет] [класний керівник?] – додати вчителя",
                    )
                );
                $message = '';
                foreach ($commands[$role] as $command) {
                    $message.= $command."\n";
                }

                $this->chat->message($message)->send();

                break;

        }
    }

    public function scheduleStudy(): void
    {
        $period = $this->data->get('period');

        $data = json_decode(Storage::get('\public\data\schedule_study.json'), true);
        $this->chat->message('<strong>' . $data['schedule']['title'] . '</strong>')->send();

        foreach ($data['schedule'][$period] as $typeStudents) {
            $message = '<strong>👨‍🏫 ' . $typeStudents['title'] . ' 👩‍🏫</strong>' . "\n" .
                "<blockquote>" . $typeStudents['classes'] . "</blockquote>";

            $currentMonthIndex = 0;
            if (is_array($typeStudents['date'])) {
                $currentMonth = array_search(date("F"), array_keys($typeStudents['date']));

                foreach ($typeStudents['date'] as $dates) {
                    if ($currentMonthIndex == $currentMonth) {
                        $message .= "\n\n<blockquote>";
                        $message .= "<strong>" . current($dates) . "</strong>";
                    } else {
                        $message .= "\n\n<strong>" . current($dates) . "</strong>";
                    }

                    for ($i = 1; $i < count($dates); $i++) {
                        if ($dates[$i] == date("d.m", strtotime("+1 day"))) {
                            $message .= '<strong>' . $dates[$i] . ' (завтра)</strong> , ';
                        } elseif ($dates[$i] == last($dates)) {
                            $message .= $dates[$i] . ";";
                        } else {
                            $message .= $dates[$i] . ', ';
                        }
                    }

                    if ($currentMonthIndex == $currentMonth) {
                        $message .= "</blockquote>";
                    }
                    $currentMonthIndex++;
                }
                $this->chat->message($message)->send();
            } else {
                $this->chat->message($typeStudents['date'])->send();

                break;
            }

        }
    }

    public function scheduleBus(): void
    {
        $route = $this->data->get('route');

        $data = json_decode(Storage::get('\public\data\schedule_bus.json'), true);

        $route = $data['routes'][$route - 1];
        $message = "<blockquote><strong>📍🗺️ Маршрут №" . $route['routeNumber'] . "</strong></blockquote>\n";
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
        date_default_timezone_set('UTC');

        $type = $this->data->get('type');
        $data = json_decode(Storage::get('\public\data\schedule_duty.json'), true);

        // Функція для перевірки, чи дата в межах наступного тижня
        function getCorrectDate($date, $type): bool
        {
            // Поточна дата і дата через 7 днів
            $currentDate = new DateTime();
            $checkDate = DateTime::createFromFormat('d.m', $date);
            $endDate = '';

            if ($type == 'week') {
                $endDate = (clone $currentDate)->modify('+7 days');
            }
            if ($type == 'tomorrow') {
                $endDate = (clone $currentDate)->modify('+1 day');
            }

            if ($checkDate) {
                $checkDate->setDate($currentDate->format("Y"), $checkDate->format('m'), $checkDate->format('d'));

                return $checkDate >= $currentDate->setTime(0, 0, 0) && $checkDate <= $endDate;
            }

            return false;
        }

        function getDuty($array, $chat): void
        {
            foreach ($array as $duty) {
                $message = "<blockquote><strong>🗓 Дата: " . implode(", ", $duty['dates']) . "</strong></blockquote>\n\n";
                $message .= "💼 Старший черговий: <strong>" . $duty['seniorDuty'] . "</strong>\n";
                $message .= "🎓 Черговий клас: <strong>" . $duty['class'] . "</strong>\n\n";
                $message .= "1️⃣ поверх: <strong>" . implode(", ", $duty['firstFloor']) . "</strong>\n";
                $message .= "2️⃣ поверх: <strong>" . implode(", ", $duty['secondFloor']) . "</strong>\n";
                $message .= "3️⃣ поверх: <strong>" . implode(", ", $duty['thirdFloor']) . "</strong>\n";
                $message .= "🏡 Подвір'я: <strong>" . $duty['yard'] . "</strong>\n";

                $chat->message($message)->send();
            }
        }

        switch ($type) {
            case 'tomorrow':
                $groupedDuties = [];
                $foundTomorrow = 0;

                foreach ($data['dutySchedule'] as $duty) {
                    foreach ($duty['dates'] as $date) {
                        if ($foundTomorrow < 2) {
                            if (getCorrectDate($date, $type)) {
                                $groupedDuties[] = [
                                    'dates' => [$date],
                                    'class' => $duty['class'],
                                    'seniorDuty' => $duty['seniorDuty'],
                                    'firstFloor' => $duty['firstFloor'],
                                    'secondFloor' => $duty['secondFloor'],
                                    'thirdFloor' => $duty['thirdFloor'],
                                    'yard' => $duty['yard']
                                ];

                                $foundTomorrow++;
                            }
                        }
                    }
                }
                getDuty($groupedDuties, $this->chat);

                break;
            case 'week':
                $groupedDuties = [];
                // Виведення інформації для наступного тижня
                foreach ($data['dutySchedule'] as $duty) {
                    $filteredDates = [];

                    // Перевіряємо кожну дату
                    foreach ($duty['dates'] as $date) {
                        if (getCorrectDate($date, $type)) {
                            $filteredDates[] = $date;
                        }
                    }
                    // Групуємо дати за унікальним набором чергових
                    if (!empty($filteredDates)) {
                        $key = md5(serialize([
                            'class' => $duty['class'],
                            'seniorDuty' => $duty['seniorDuty'],
                            'firstFloor' => $duty['firstFloor'],
                            'secondFloor' => $duty['secondFloor'],
                            'thirdFloor' => $duty['thirdFloor'],
                            'yard' => $duty['yard']
                        ]));

                        // Додаємо дати до відповідної групи
                        if (!isset($groupedDuties[$key])) {
                            $groupedDuties[$key] = [
                                'dates' => [],
                                'class' => $duty['class'],
                                'seniorDuty' => $duty['seniorDuty'],
                                'firstFloor' => $duty['firstFloor'],
                                'secondFloor' => $duty['secondFloor'],
                                'thirdFloor' => $duty['thirdFloor'],
                                'yard' => $duty['yard']
                            ];
                        }
                        $groupedDuties[$key]['dates'] = array_merge($groupedDuties[$key]['dates'], $filteredDates);
                    }
                }

                // Виведення згрупованої інформації
                getDuty($groupedDuties, $this->chat);
                break;

            case 'all':
                // Виведення всієї інформації
                getDuty($data['dutySchedule'], $this->chat);
                break;
        }
    }

    public function handleChatMessage(Stringable|\Illuminate\Support\Stringable $text): void
    {
        $this->chat->message("Спілкуватися я можу лише за допомогою команд. Використовуй це :)")->send();
    }

    public function handleUnknownCommand(Stringable|\Illuminate\Support\Stringable $text): void
    {
        $this->chat->message("Такої команди я не знаю")->send();
    }

}
