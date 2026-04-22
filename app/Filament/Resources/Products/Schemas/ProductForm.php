<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('amount_info'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('deposit_product_id')
                    ->label('Pfand-Produkt')
                    ->placeholder('Kein Pfand')
                    ->options(function () {
                        return Product::whereHas('category', function ($query) {
                            $query->where('is_deposit', true);
                        })->pluck('name', 'id');
                    })
                    ->searchable(),
            ]);
    }
}
