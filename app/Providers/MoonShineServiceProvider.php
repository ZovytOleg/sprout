<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Follower;
use App\Models\ScheduleDuty;
use App\Models\Student;
use App\Models\Teacher;
use App\MoonShine\Pages\Test\TestIndexPage;
use App\MoonShine\Resources\FollowerResource;
use App\MoonShine\Resources\PostResource;
use App\MoonShine\Resources\ScheduleDutyResource;
use App\MoonShine\Resources\ScheduleLessonsResource;
use App\MoonShine\Resources\StudentResource;
use App\MoonShine\Resources\SubjectResource;
use App\MoonShine\Resources\TeacherResource;
use App\MoonShine\Resources\TestResource;
use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\MoonShine;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Pages\Page;
use Closure;

class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    /**
     * @return list<ResourceContract>
     */
    protected function resources(): array
    {
        return [];
    }

    /**
     * @return list<Page>
     */
    protected function pages(): array
    {
        return [];
    }

    /**
     * @return Closure|list<MenuElement>
     */
    protected function menu(): array
    {
        return [
            MenuGroup::make(static fn() => __('moonshine::ui.resource.system'), [
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.admins_title'),
                    new MoonShineUserResource()
                ),
                MenuItem::make(
                    static fn() => __('moonshine::ui.resource.role_title'),
                    new MoonShineUserRoleResource()
                ),
            ]),

            MenuGroup::make(static fn() => __('Користувачі'), [
                MenuItem::make(
                    static fn() => __('Учні'), new StudentResource())
                    ->icon('heroicons.outline.book-open'),
                MenuItem::make(
                    static fn() => __('Вчителі'), new TeacherResource())
                    ->icon('heroicons.outline.academic-cap'),
                MenuItem::make(
                    static fn() => __('Фоловери'), new FollowerResource())
                    ->badge(fn() => Follower::query()->count())
                    ->icon('heroicons.outline.paper-airplane'),
            ])->icon('heroicons.user-group'),

            MenuGroup::make(static fn() => __('Розклад'), [
                MenuItem::make(
                    static fn() => __('Уроків'), new ScheduleLessonsResource())
                    ->icon('heroicons.outline.list-bullet'),
                MenuItem::make(
                    static fn() => __('Чергування'), new ScheduleDutyResource())
                    ->icon('heroicons.outline.list-bullet'),
            ])->icon('heroicons.clipboard-document-list'),

            MenuItem::make(
                static fn() => __('Кастомна'), new TestResource())
                ->icon('heroicons.outline.academic-cap'),
        ];
    }

    /**
     * @return Closure|array{css: string, colors: array, darkColors: array}
     */
    protected function theme(): array
    {
        return [];
    }
}
