<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\ScheduleLessons;

use MoonShine\Components\TableBuilder;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Select;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\ID;
use MoonShine\Fields\Field;
use MoonShine\Components\MoonShineComponent;

/**
 * @extends ModelResource<ScheduleLessons>
 */
class ScheduleLessonsResource extends ModelResource
{
    protected string $model = ScheduleLessons::class;

    protected string $title = 'Розклад уроків';

    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
            Block::make([
                BelongsTo::make('Клас', 'grade', resource: new GradeResource())->showOnExport(),
            ]),
        ];
    }

    /**
     * @param ScheduleLessons $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
