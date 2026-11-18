<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DnvInfoWidget extends Widget
{
    protected string $view = 'filament.widgets.dnv-info-widget';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 1;
}
