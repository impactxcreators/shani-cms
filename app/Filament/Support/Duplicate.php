<?php

namespace App\Filament\Support;

use Filament\Actions\ReplicateAction;
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
            ->excludeAttributes(['slug', 'external_id', 'created_at', 'updated_at', 'deleted_at'])
            ->beforeReplicaSaved(function (Model $replica): void {
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
            })
            ->successNotificationTitle('Duplicated — edit the copy and publish when ready');
    }
}
