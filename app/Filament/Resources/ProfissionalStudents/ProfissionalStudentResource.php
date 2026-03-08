<?php

namespace App\Filament\Resources\ProfissionalStudents;

use App\Filament\Resources\ProfissionalStudents\Pages\CreateProfissionalStudent;
use App\Filament\Resources\ProfissionalStudents\Pages\EditProfissionalStudent;
use App\Filament\Resources\ProfissionalStudents\Pages\ListProfissionalStudents;
use App\Filament\Resources\ProfissionalStudents\Pages\ViewProfissionalStudent;
use App\Filament\Resources\ProfissionalStudents\Schemas\ProfissionalStudentForm;
use App\Filament\Resources\ProfissionalStudents\Schemas\ProfissionalStudentInfolist;
use App\Filament\Resources\ProfissionalStudents\Tables\ProfissionalStudentsTable;
use App\Models\ProfissionalStudent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ProfissionalStudentResource extends Resource
{
    protected static ?string $model = ProfissionalStudent::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Profissional-Alunos';

    protected static ?string $modelLabel = 'Relação Profissional-Aluno';

    protected static ?string $pluralModelLabel = 'Relações Profissional-Aluno';

    protected static UnitEnum|string|null $navigationGroup = 'Gestão de Usuários';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ProfissionalStudentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfissionalStudentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfissionalStudentsTable::configure($table);
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
            'index' => ListProfissionalStudents::route('/'),
            'create' => CreateProfissionalStudent::route('/create'),
            'view' => ViewProfissionalStudent::route('/{record}'),
            'edit' => EditProfissionalStudent::route('/{record}/edit'),
        ];
    }
}
