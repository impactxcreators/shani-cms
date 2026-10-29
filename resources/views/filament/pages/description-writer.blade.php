@php
    $total = $jobs->count();
    $pending = $counts['pending'] ?? 0;
    $finished = $total - $pending;
    $pct = $total ? round($finished / $total * 100) : 0;
    $badge = ['done' => ['Written', '#16a34a'], 'skipped' => ['Skipped', '#6b7280'], 'failed' => ['Failed', '#dc2626'], 'undone' => ['Undone', '#6b7280'], 'pending' => ['Waiting', '#d97706']];
@endphp
<x-filament-panels::page>
    @if($batch && $total)
        <x-filament::section>
            <div @if($pending) wire:init="step" wire:poll.1500ms="step" @endif>
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                    <div>
                        <strong>{{ $finished }} of {{ $total }} done</strong>
                        · {{ $counts['done'] ?? 0 }} written · {{ $counts['skipped'] ?? 0 }} skipped · {{ $counts['failed'] ?? 0 }} failed
                        @if($pending)<span style="color:#d97706"> · working… keep this page open</span>@endif
                    </div>
                    @if(($counts['done'] ?? 0) && ! $pending)
                        <x-filament::button color="gray" icon="heroicon-o-arrow-uturn-left" wire:click="undoAll" wire:confirm="Put back the previous descriptions for every item in this run?">Undo all</x-filament::button>
                    @endif
                </div>
                <div style="height:8px;background:rgba(128,128,128,.2);border-radius:99px;margin-top:10px;overflow:hidden">
                    <div style="height:100%;width:{{ $pct }}%;background:#f59e0b;transition:width .4s"></div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <style>.dw-new p{margin:.25rem 0}.dw-new ul{list-style:disc;padding-left:1.2rem}</style>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                    <thead><tr style="text-align:left;border-bottom:1px solid rgba(128,128,128,.3)">
                        <th style="padding:8px">Item</th><th style="padding:8px">Status</th><th style="padding:8px">New description</th><th style="padding:8px"></th>
                    </tr></thead>
                    <tbody>
                        @foreach($jobs as $job)
                            <tr style="border-bottom:1px solid rgba(128,128,128,.15);vertical-align:top">
                                <td style="padding:8px;font-weight:600;min-width:160px">{{ $job->title }}<div style="font-weight:400;color:#6b7280;font-size:.75rem">{{ class_basename($job->model_type) }}</div></td>
                                <td style="padding:8px;white-space:nowrap">
                                    <span style="color:{{ $badge[$job->status][1] }};font-weight:600">{{ $badge[$job->status][0] }}</span>
                                    @if($job->note)<div style="color:#6b7280;font-size:.75rem;max-width:220px;white-space:normal">{{ $job->note }}</div>@endif
                                </td>
                                <td style="padding:8px" class="dw-new">
                                    @if($job->new_description)
                                        @if(str_contains($job->new_description, '<p>')) {!! $job->new_description !!} @else {!! nl2br(e($job->new_description)) !!} @endif
                                    @endif
                                </td>
                                <td style="padding:8px;white-space:nowrap">
                                    @if($job->status === 'done')
                                        <x-filament::link tag="button" wire:click="undo({{ $job->id }})" color="gray" icon="heroicon-o-arrow-uturn-left">Undo</x-filament::link>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif

    <x-filament::section heading="Recent runs">
        @forelse($recent as $run)
            <div style="padding:6px 0;border-bottom:1px solid rgba(128,128,128,.15)">
                <x-filament::link :href="static::getUrl(['batch' => $run->batch])">{{ \Illuminate\Support\Carbon::parse($run->started)->format('M j, H:i') }}</x-filament::link>
                · {{ $run->done }} of {{ $run->total }} written
            </div>
        @empty
            <p style="color:#6b7280">No runs yet.</p>
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
