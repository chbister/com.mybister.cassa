<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Toggle::make('is_deposit')
                    ->label('Ist Pfand-Kategorie')
                    ->helperText('Es kann nur eine Kategorie als Pfand-Kategorie markiert sein.')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $record) {
                        if ($state) {
                            $existing = Category::where('is_deposit', true)
                                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                ->exists();

                            if ($existing) {
                                $set('is_deposit', false);
                                throw ValidationException::withMessages([
                                    'data.is_deposit' => 'Es existiert bereits eine Pfand-Kategorie.',
                                ]);
                            }
                        }
                    }),
            ]);
    }
}
