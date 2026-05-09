<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingPlanResource\Pages;
use App\Models\PricingPlan;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PricingPlanResource extends Resource
{
    protected static ?string $model = PricingPlan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string | \UnitEnum | null $navigationGroup = 'Subscription';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated(false)
                    ->maxLength(255)
                    ->helperText('Auto-generated based on name.'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('original_price')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Toggle::make('is_popular')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_price')
                    ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_popular'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPricingPlans::route('/'),
            'create' => Pages\CreatePricingPlan::route('/create'),
            'edit' => Pages\EditPricingPlan::route('/{record}/edit'),
        ];
    }
}
