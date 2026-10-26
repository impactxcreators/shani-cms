<?php

namespace App\Filament\Resources\Comments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('author_name')->required(),
            TextInput::make('author_email')->email(),
            Textarea::make('body')->required()->rows(4)->columnSpanFull(),
            Select::make('status')->options([
                'pending' => 'Pending', 'approved' => 'Approved', 'spam' => 'Spam', 'rejected' => 'Rejected',
            ])->required(),
        ])->columns(2);
    }
}
