<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Follower;

use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Fields\Date;
use MoonShine\Fields\Email;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Switcher;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Fields\Preview;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<Follower>
 */
class FollowerResource extends ModelResource
{
    protected string $model = Follower::class;

    protected string $title = 'Підписники на бота';

    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
            ID::make()->sortable(function (Builder $query, string $column = 'id', string $direction = 'ASC') {
                $query->orderBy('id', 'ASC');
            })
                ->badge('primary')
                ->showOnExport(),
            Block::make('Основне', [
                BelongsTo::make('Роль', 'role', resource: new RoleResource())
                    ->badge(fn($role) => $role->id == 1? 'info' : ($role->id == 2? 'success' : ($role->id == 3? 'error': ($role->id == 4? 'warning': 'gray'))) )
                    ->showOnExport(),
                BelongsTo::make("Ім'я", 'teacher', 'first_name', resource: new RoleResource())
                    ->showOnExport()
                ->showWhen('teacher_id', '>', 0),
                BelongsTo::make("Прізвище", 'teacher', 'last_name', resource: new RoleResource())
                    ->showOnExport()
                    ->showWhen('teacher_id', '>', 0),
                TEXT::make("Нікнейм", 'chat_name')
                    ->sortable()
                    ->showOnExport()
                    ->disabled(),
                Date::make('Дата приєднання', 'created_at')
                    ->format('d.m.Y')
                    ->disabled()
            ])
        ];
    }

    public function filters(): array
    {

        return [
            Date::make('Дата приєднання', 'created_at')
                ->format('d.m.Y'),
            BelongsTo::make('Роль', 'role', resource: new RoleResource())
        ];
    }

    public function getActiveActions(): array
    {
        return ['view', 'delete', 'massDelete', 'filters'];
    }

    /**
     * @param Follower $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
