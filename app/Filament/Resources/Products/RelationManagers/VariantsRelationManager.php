<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    protected static ?string $title = 'Các Biến thể Sản Phẩm';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->label('Mã SKU biến thể')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Mã SKU biến thể này đã tồn tại, vui lòng nhập mã khác.',
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label('Giá bán biến thể (VND)')
                    ->helperText('Bắt buộc lớn hơn 0đ (hoặc để trống để dùng giá gốc của sản phẩm)')
                    ->numeric()
                    ->minValue(1)
                    ->gt(0)
                    ->prefix('₫')
                    ->validationMessages([
                        'min' => 'Giá biến thể phải lớn hơn 0đ.',
                        'gt' => 'Giá biến thể phải lớn hơn 0đ.',
                    ]),
                Forms\Components\TextInput::make('stock')
                    ->label('Tồn kho')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Thứ tự sắp xếp')
                    ->numeric()
                    ->default(0),
                CuratorPicker::make('image_url')
                    ->label('Hình ảnh biến thể')
                    ->directory('san-pham')
                    ->columnSpanFull(),
                Forms\Components\Select::make('attributeValues')
                    ->label('Thuộc tính đi kèm')
                    ->multiple()
                    ->relationship('attributeValues', 'value', modifyQueryUsing: fn ($query) => $query->with('attributeType'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => ($record->attributeType?->name ? $record->attributeType->name . ': ' : '') . $record->value)
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\Select::make('attribute_type_id')
                            ->label('Loại Thuộc Tính (vd: Dung tích, Quy cách, Màu...)')
                            ->relationship('attributeType', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên loại thuộc tính')
                                    ->placeholder('VD: Dung tích')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, $set) => $set('slug', \Illuminate\Support\Str::slug($state ?? ''))),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Mã (Slug)')
                                    ->required()
                                    ->unique('attribute_types', 'slug'),
                            ]),
                        Forms\Components\TextInput::make('value')
                            ->label('Giá trị (vd: 500ml, 1 Lít, Thùng 12 chai...)')
                            ->required(),
                        Forms\Components\ColorPicker::make('color_code')
                            ->label('Mã màu (nếu có)'),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('Thứ tự')->numeric()->sortable(),
                CuratorColumn::make('image_url')->label('Hình ảnh')->size(40),
                Tables\Columns\TextColumn::make('sku')->label('Mã SKU'),
                Tables\Columns\TextColumn::make('attributeValues.value')
                    ->label('Thuộc tính')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')->label('Giá')->money('VND'),
                Tables\Columns\TextColumn::make('stock')->label('Tồn kho'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()->label('Thêm biến thể'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                 DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc');
    }
}
