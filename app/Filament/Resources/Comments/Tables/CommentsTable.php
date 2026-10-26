<?php

namespace App\Filament\Resources\Comments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('author_name')->searchable()->weight('bold'),
            TextColumn::make('body')->limit(70)->wrap(),
            TextColumn::make('commentable_type')->label('On')->formatStateUsing(fn ($state) => class_basename($state))->badge(),
            TextColumn::make('status')->badge()->colors([
                'warning' => 'pending', 'success' => 'approved', 'danger' => 'spam', 'gray' => 'rejected',
            ]),
            TextColumn::make('created_at')->since()->label('When'),
        ])->defaultSort('created_at', 'desc')
          ->filters([
              SelectFilter::make('status')->options([
                  'pending'=>'Pending','approved'=>'Approved','spam'=>'Spam','rejected'=>'Rejected',
              ])->default('pending'),
          ])
          ->recordActions([
              Action::make('approve')->icon('heroicon-o-check')->color('success')
                  ->visible(fn ($record) => $record->status !== 'approved')
                  ->action(fn ($record) => $record->update(['status' => 'approved'])),
              Action::make('spam')->icon('heroicon-o-no-symbol')->color('danger')
                  ->visible(fn ($record) => $record->status !== 'spam')
                  ->action(fn ($record) => $record->update(['status' => 'spam'])),
              EditAction::make(),
          ])
          ->toolbarActions([BulkActionGroup::make([
              BulkAction::make('approve_selected')->label('Approve selected')->icon('heroicon-o-check')->color('success')
                  ->action(fn (Collection $records) => $records->each->update(['status' => 'approved'])),
              DeleteBulkAction::make(),
          ])]);
    }
}
