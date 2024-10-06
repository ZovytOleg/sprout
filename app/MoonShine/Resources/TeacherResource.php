<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Teacher;

use MoonShine\Fields\Email;
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
            Block::make([
                ID::make()->sortable(),
                TEXT::make("Ім'я", 'first_name'),
                TEXT::make("Прізвище", 'last_name'),
                Email::make("Пошта", 'email'),
                TEXT::make("Предмет", 'subject'),
                TEXT::make("Класний керівник", 'class_leader'),
            ]),
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
