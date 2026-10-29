<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable()->weight('bold')->wrap(),
            TextColumn::make('author.name')->label('Author')->placeholder('—'),
            TextColumn::make('status')->badge()->colors(['gray'=>'draft','success'=>'published','warning'=>'archived']),
            TextColumn::make('published_at')->date()->label('Published')->placeholder('—'),
            TextColumn::make('comments_count')->counts('comments')->label('Comments'),
            TextColumn::make('updated_at')->since()->label('Updated'),
        ])->defaultSort(Duplicate::sort('updated_at', 'desc'))
          ->filters([
              SelectFilter::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived']),
              TrashedFilter::make(),
          ])
          ->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([
              DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make(),
          ])]);
    }
}
