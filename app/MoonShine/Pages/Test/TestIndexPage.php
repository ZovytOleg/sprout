<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Test;

use App\MoonShine\Resources\GradeResource;
use MoonShine\Decorations\Block;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Text;
use MoonShine\Pages\Crud\IndexPage;
use MoonShine\Components\MoonShineComponent;
use MoonShine\Fields\Field;
use MoonShine\Pages\ViewPage;
use Throwable;

class TestIndexPage extends IndexPage
{
    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        return [
/*            Block::make([
                BelongsTo::make('Клас', 'grade', resource: new GradeResource())->showOnExport(),
                Text::make('День', 'day')
            ]),*/
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
            ViewPage::make('Розклад уроків', 'lessons')
                ->setContentView('pages.schedule-lessons'),
        ];
    }

    /**
     * @return list<MoonShineComponent>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}
