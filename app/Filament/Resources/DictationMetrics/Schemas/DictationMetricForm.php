<?php

namespace App\Filament\Resources\DictationMetrics\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DictationMetricForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required(),
                Select::make('exercise_id')
                    ->relationship('exercise', 'id')
                    ->required(),
                TextInput::make('difficulty')
                    ->required(),
                TextInput::make('correct_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('error_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('missing_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('extra_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('accuracy_percent')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('letter_omission_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('letter_insertion_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('letter_substitution_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('transposition_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('split_join_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('punctuation_error_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('capitalization_error_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('error_words'),
                Textarea::make('resolution')
                    ->columnSpanFull(),
            ]);
    }
}
