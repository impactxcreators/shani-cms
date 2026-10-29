<?php

namespace App\Filament\Support;

use Closure;
use Filament\Actions\ReplicateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A reusable "Duplicate" row action. Clones a record, marks the copy clearly,
 * regenerates its slug, and keeps it out of public view until the user edits it.
 */
class Duplicate
{
    public static function make(): ReplicateAction
    {
        return ReplicateAction::make('duplicate')
            ->label('Duplicate')
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            // drop unique/identity fields so the copy gets fresh ones
            ->excludeAttributes(['slug', 'external_id', 'created_at', 'updated_at', 'deleted_at', 'copied_from_id'])
            ->beforeReplicaSaved(function (Model $replica, Model $record): void {
                $attrs = $replica->getAttributes();

                // mark the copy in its title/name
                foreach (['title', 'name'] as $field) {
                    if (array_key_exists($field, $attrs) && filled($replica->{$field})) {
                        $replica->{$field} = $replica->{$field} . ' (copy)';
                    }
                }

                // keep the duplicate out of public view until it is edited
                if (array_key_exists('status', $attrs)) {
                    $replica->status = 'draft';
                }
                if (array_key_exists('is_active', $attrs)) {
                    $replica->is_active = false;
                }
                if (array_key_exists('is_featured', $attrs)) {
                    $replica->is_featured = false;
                }
                if (array_key_exists('published_at', $attrs)) {
                    $replica->published_at = null;
                }

                // place the copy right below the original in admin lists (see sort())
                $replica->copied_from_id = $record->copied_from_id ?? $record->getKey();
                if ($record->usesTimestamps() && $record->{$record->getUpdatedAtColumn()}) {
                    $replica->{$replica->getUpdatedAtColumn()} = $record->{$record->getUpdatedAtColumn()};
                }
            })
            ->successNotificationTitle('Duplicated — edit the copy and publish when ready');
    }

    /**
     * Default table sort that keeps copies directly below their original.
     * Use as ->defaultSort(Duplicate::sort('updated_at', 'desc')).
     */
    public static function sort(?string $column = null, string $direction = 'asc'): Closure
    {
        return function (Builder $query) use ($column, $direction): Builder {
            $t = $query->getModel()->getTable();

            if ($column) {
                $query->orderBy("{$t}.{$column}", $direction);
            }

            return $query
                ->orderByRaw("COALESCE({$t}.copied_from_id, {$t}.id) {$direction}")
                ->orderBy("{$t}.id");
        };
    }
}
