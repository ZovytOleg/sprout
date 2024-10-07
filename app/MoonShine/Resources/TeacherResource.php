<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teacher;

use Illuminate\Validation\Rules\In;
use MoonShine\Components\Layout\Flash;
use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Fields\Email;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Switcher;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<Teacher>
 */
class TeacherResource extends ModelResource
{
    protected string $model = Teacher::class;

    protected string $title = 'Вчителі';

    protected string $column = 'first_name';

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
                        Email::make("Пошта", 'email')->showOnExport(),
                    ])
                ])->columnSpan(8),

                Column::make([
                    Block::make('Додаткове',[
                        TEXT::make("Предмет", 'subject')->showOnExport(),
                        BelongsTo::make('Класний керівник', 'grade', resource: new GradeResource())
                            ->showOnExport()
                            ->default('-')
                            ->nullable(),
                        Switcher::make("Активований", 'is_verified')
                            ->disabled()
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
            Switcher::make("Активований", 'is_verified')
        ];
    }

    /**
     * @param Teacher $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
