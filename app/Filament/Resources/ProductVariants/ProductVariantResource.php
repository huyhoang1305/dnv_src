<?php

namespace App\Filament\Resources\ProductVariants;

use App\Filament\Resources\ProductVariants\Pages\CreateProductVariant;
use App\Filament\Resources\ProductVariants\Pages\EditProductVariant;
use App\Filament\Resources\ProductVariants\Pages\ListProductVariants;
use App\Filament\Resources\ProductVariants\Schemas\ProductVariantForm;
use App\Filament\Resources\ProductVariants\Tables\ProductVariantsTable;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $modelLabel = 'Biến thể sản phẩm';
    protected static ?string $pluralModelLabel = 'Biến thể sản phẩm';
    protected static string|\UnitEnum|null $navigationGroup = 'Cửa hàng';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-square-3-stack-3d';
    protected static bool $shouldRegisterNavigation = false;

public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return ProductVariantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductVariantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductVariants::route('/'),
            'create' => CreateProductVariant::route('/create'),
            'edit' => EditProductVariant::route('/{record}/edit'),
        ];
    }
}
