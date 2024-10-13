<?php

declare(strict_types=1);

namespace App\MoonShine\Controllers;

use App\Models\ScheduleLessons;
use MoonShine\MoonShineRequest;
use MoonShine\Http\Controllers\MoonShineController;
use MoonShine\Pages\Page;

final class ScheduleLessonsController extends MoonShineController
{
    public function __invoke(MoonShineRequest $request): Page
    {
/*        $lessons = ScheduleLessons::query()
            ->with(['grade_id', 'day'])
            ->get();*/

        return $this->view('pages.schedule-lessons');
    }
}
