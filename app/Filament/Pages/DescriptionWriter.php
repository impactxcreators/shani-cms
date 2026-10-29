<?php

namespace App\Filament\Pages;

use App\Models\DescriptionJob;
use App\Support\Describer\Describer;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use UnitEnum;

/** Runs bulk "write descriptions" jobs a couple at a time while the page is open, with undo. */
class DescriptionWriter extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Tools';

    protected static ?string $navigationLabel = 'Description Writer';

    protected static ?string $title = 'Description Writer';

    protected string $view = 'filament.pages.description-writer';

    #[Url]
    public ?string $batch = null;

    public function getSubheading(): ?string
    {
        return 'To write descriptions in bulk: open Products, Stores, Deals or Coupons, tick the items, then choose "Write descriptions from links" from the bulk actions.';
    }

    public function step(): void
    {
        if (! $this->batch) {
            return;
        }
        $jobs = DescriptionJob::where('batch', $this->batch)->where('status', 'pending')->orderBy('id')->limit(2)->get();
        foreach ($jobs as $job) {
            $this->runJob($job);
        }
    }

    private function runJob(DescriptionJob $job): void
    {
        $item = class_exists($job->model_type) ? $job->model_type::find($job->model_id) : null;
        if (! $item || ! Describer::supports($item)) {
            $job->update(['status' => 'failed', 'note' => 'This item no longer exists.']);

            return;
        }

        if (! $job->overwrite && trim(strip_tags((string) $item->description)) !== '') {
            $original = $item->copied_from_id ? $item->newQuery()->find($item->copied_from_id)?->description : null;
            if ($original === null || trim((string) $original) !== trim((string) $item->description)) {
                $job->update(['status' => 'skipped', 'note' => 'Already has its own description (turn on "Also replace…" to rewrite it).']);

                return;
            }
        }

        try {
            $result = app(Describer::class)->describe($item, $job->length);
        } catch (\Throwable $e) {
            report($e);
            $job->update(['status' => 'failed', 'note' => 'Something went wrong while writing this one.']);

            return;
        }
        if ($result['text'] === '') {
            $job->update(['status' => 'failed', 'note' => $result['note'] ?: 'Nothing could be written.']);

            return;
        }

        $old = $item->description;
        $item->description = $result['text'];
        $item->save();
        $job->update([
            'status' => 'done',
            'old_description' => $old,
            'new_description' => $result['text'],
            'note' => $result['site']['ok'] ? 'Read from '.$result['site']['shop'].'.'.($result['note'] ? ' '.$result['note'] : '') : $result['note'],
        ]);
    }

    public function undo(int $id): void
    {
        $job = DescriptionJob::where('batch', $this->batch)->where('status', 'done')->find($id);
        if ($job && ($item = $job->model_type::find($job->model_id))) {
            $item->description = $job->old_description;
            $item->save();
            $job->update(['status' => 'undone']);
        }
    }

    public function undoAll(): void
    {
        foreach (DescriptionJob::where('batch', $this->batch)->where('status', 'done')->pluck('id') as $id) {
            $this->undo($id);
        }
    }

    protected function getViewData(): array
    {
        $jobs = $this->batch ? DescriptionJob::where('batch', $this->batch)->orderBy('id')->get() : collect();

        return [
            'jobs' => $jobs,
            'counts' => $jobs->countBy('status'),
            'recent' => DescriptionJob::select('batch', DB::raw('count(*) as total'), DB::raw("sum(case when status = 'done' then 1 else 0 end) as done"), DB::raw('max(created_at) as started'))
                ->groupBy('batch')->orderByDesc('started')->limit(10)->get(),
        ];
    }
}
