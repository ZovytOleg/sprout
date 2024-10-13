<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Follower;
use App\Models\Student;
use App\Models\Teacher;
use MoonShine\Components\Badge;
use MoonShine\Decorations\Block;
use MoonShine\Decorations\Column;
use MoonShine\Decorations\Divider;
use MoonShine\Decorations\Grid;
use MoonShine\Decorations\LineBreak;
use MoonShine\Menu\MenuDivider;
use MoonShine\Metrics\DonutChartMetric;
use MoonShine\Metrics\LineChartMetric;
use MoonShine\Metrics\ValueMetric;
use MoonShine\Pages\Page;
use MoonShine\Components\MoonShineComponent;

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function breadcrumbs(): array
    {
        return [
            '#' => $this->title()
        ];
    }

    public function title(): string
    {
        return $this->title ?: 'Головна';
    }

    /**
     * @return list<MoonShineComponent>
     */
    public function components(): array
    {
        return [
            Divider::make('Загальна інформація'),
            Grid::make([
                Column::make([
                    ValueMetric::make('Учнів')
                        ->value(Student::count())
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Вчителів')
                        ->value(Teacher::count())
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Фоловерів')
                        ->value(Follower::count())
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Фідбек')
                        ->value(1000)
                ])->columnSpan(3),
            ]),

            Divider::make('Статистика'),

            Grid::make([
                LineChartMetric::make('Фоловери')
                    ->line([
                        'Зареєструвалось' => Follower::query()
                            ->selectRaw('COUNT(id) as count, DATE_FORMAT(created_at, "%d.%m.%Y") as date')
                            ->groupBy('date')
                            ->pluck('count', 'date')
                            ->toArray(),
                    ], '#14A44D')
                    ->columnSpan(6),

                DonutChartMetric::make('Фоловери')
                    ->values([
                        'Учні' => Follower::where('role_id', '=', 1)->count('id'),
                        'Вчителі' => Follower::where('role_id', '=', 2)->count('id'),
                        'Адміністрація' => Follower::where('role_id', '=', 3)->count('id'),
                        'Батьки' => Follower::where('role_id', '=', 4)->count('id'),
                        'Гості' => Follower::where('role_id', '=', 5)->count('id'),
                    ])
                    ->columnSpan(6)

            ]),

        ];

    }
}
