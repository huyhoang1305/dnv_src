<?php

namespace App\Filament\Resources\ContentTypes\Pages;

use App\Filament\Resources\Contents\ContentResource;
use App\Filament\Resources\ContentTypes\ContentTypeResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentTypes extends ListRecords
{
    protected static string $resource = ContentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_contents')
                ->label('Quay lại Nội dung')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ContentResource::getUrl('index')),

            CreateAction::make()
                ->label('Thêm phân loại'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            ContentResource::getUrl('index') => 'Nội dung',
            'Phân loại',
        ];
    }
}
