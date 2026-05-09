<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanFeatureResource\Pages;
use App\Models\PlanFeature;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanFeatureResource extends Resource
{
    protected static ?string $model = PlanFeature::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-circle';
    protected static string | \UnitEnum | null $navigationGroup = 'Subscription';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('pricing_plan_id')
                    ->relationship('plan', 'name')
                    ->required(),
                Forms\Components\TextInput::make('feature')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_available')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('feature')->searchable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pricing_plan_id')
                    ->relationship('plan', 'name')
                    ->label('Pricing Plan'),
                Tables\Filters\TernaryFilter::make('is_available'),
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
            'index' => Pages\ListPlanFeatures::route('/'),
            'create' => Pages\CreatePlanFeature::route('/create'),
            'edit' => Pages\EditPlanFeature::route('/{record}/edit'),
        ];
    }
}
