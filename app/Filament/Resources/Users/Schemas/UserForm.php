<?php
namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')->password()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create')
                ->helperText('Leave blank to keep the current password.'),
            Select::make('roles')->relationship('roles', 'name')->multiple()->preload()
                ->helperText('Admin = full access. Editor = publish anything. Author = own drafts. Viewer = read-only.'),
            Toggle::make('is_active')->default(true)->label('Active (can log in)'),
        ])->columns(2);
    }
}
