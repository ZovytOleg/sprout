<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Test;
use App\MoonShine\Pages\Test\TestIndexPage;
use App\MoonShine\Pages\Test\TestFormPage;
use App\MoonShine\Pages\Test\TestDetailPage;

use MoonShine\Pages\ViewPage;
use MoonShine\Resources\ModelResource;
use MoonShine\Pages\Page;

/**
 * @extends ModelResource<Test>
 */
class TestResource extends ModelResource
{
    protected string $model = Test::class;

    protected string $title = 'Розклад уроків';
    //protected ?string $alias = 'schedule';

    /**
     * @return list<Page>
     */
    public function pages(): array
    {
        return [
            TestIndexPage::make($this->title()),
            TestFormPage::make(
                $this->getItemID()
                    ? __('moonshine::ui.edit')
                    : __('moonshine::ui.add')
            ),
            TestDetailPage::make(__('moonshine::ui.show')),
        ];
    }

    /**
     * @param Test $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    public function rules(Model $item): array
    {
        return [];
    }
}
