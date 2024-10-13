<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\ScheduleDuty;

use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Fields\Date;
use MoonShine\Fields\Json;
use MoonShine\Fields\Position;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Relationships\BelongsToMany;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<ScheduleDuty>
 */
class ScheduleDutyResource extends ModelResource
{
    protected string $model = ScheduleDuty::class;

    protected string $title = 'Розклад';

    /**
     * @return list<MoonShineComponent|Field>
     * @throws \Throwable
     */
    public function fields(): array
    {
        return [
            Grid::make([
                Column::make([
                    Block::make('Основна інформація', [
                        ID::make()->sortable(function (Builder $query, string $column = 'id', string $direction = 'ASC') {
                            $query->orderBy('id', 'ASC');
                        })
                            ->badge('primary')
                            ->showOnExport(),
                        BelongsTo::make('Клас', 'grade', resource: new GradeResource())->showOnExport(),
                        BelongsTo::make('Головний черговий', 'senior', resource: new TeacherResource())
                            ->showOnExport(),
                        BelongsTo::make("Подвір'я", 'yard', resource: new TeacherResource())
                            ->hint('Черговий на території ліцею')
                            ->showOnExport(),
                        Json::make('І поверх', 'firstf')
                            ->asRelation(new TeacherResource())
                            ->fields([
                                BelongsTo::make(" ", 'teacher', resource: new ScheduleDutyResource())
                                    ->setColumn('first_floor_id')
                            ])
                    ])
                ])->columnSpan(8),

                Column::make([
                    Block::make('Додаткова інформація', [
                        Json::make('Дати', "dates")->fields([
                            Date::make('День', 'date')
                                ->format("d.m"),
                        ])->removable()
                    ])
                ])->columnSpan(4),

            ])
        ];
    }

    /**
     * @param ScheduleDuty $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
