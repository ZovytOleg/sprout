<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

use MoonShine\Components\CardsBuilder;
use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Fields\Email;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Relationships\HasOne;
use MoonShine\Fields\Text;
use MoonShine\Fields\TinyMce;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<Student>
 */
class StudentResource extends ModelResource
{
    protected string $model = Student::class;

    protected string $title = 'Учні';

    protected string $column = 'first_name';
    protected bool $isAsync = false; // без перезагрузки

    protected int $itemsPerPage = 50;


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

            Grid::make([
                Column::make([
                    Block::make('Основне',[
                        TEXT::make("Прізвище", 'last_name')->sortable()->showOnExport(),
                        TEXT::make("Ім'я", 'first_name')->sortable()->showOnExport(),
                    ])
                ])->columnSpan(8),

                Column::make([
                    Block::make('Додаткове',[
                        Email::make("Пошта", 'email')->showOnExport(),
                        BelongsTo::make('Клас', 'grade', resource: new GradeResource())->showOnExport(),
                    ]),
                 #   TinyMce::make('Опис', 'description'),
                ])->columnSpan(4),

            ]),

        ];
    }

    public function filters(): array
    {

        return [
            Text::make('Прізвище', 'last_name'),
            BelongsTo::make('Клас', 'grade', resource: new GradeResource())
        ];
    }

    public function getActiveActions(): array
    {
        return ['create', 'view', 'update', 'delete', 'massDelete', 'filters'];
    }

    /**
     * @param Student $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
