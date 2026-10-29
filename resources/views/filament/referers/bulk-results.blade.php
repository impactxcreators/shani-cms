@php
    $rows = $this->rows();
    $invalid = $this->invalidLines();
    $shorts = collect($rows)->pluck('short')->unique()->values();
    $dupes = collect($rows)->filter(fn ($r) => $r['existing'] !== '')->count();
@endphp
<x-filament-panels::page>
    <x-filament::section>
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
            <div>
                <strong>{{ $shorts->count() }} short link(s) created</strong>
                @if($dupes) · <span style="color:#d97706">{{ $dupes }} destination(s) already had a short link (shown below)</span>@endif
                @if(count($invalid)) · <span style="color:#dc2626">{{ count($invalid) }} line(s) skipped</span>@endif
            </div>
            <div x-data="{ copied: false }">
                <textarea x-ref="all" style="position:absolute;left:-9999px" readonly>{{ $shorts->implode("\n") }}</textarea>
                <x-filament::button icon="heroicon-o-clipboard-document" x-on:click="navigator.clipboard.writeText($refs.all.value); copied = true; setTimeout(() => copied = false, 2000)">
                    <span x-text="copied ? 'Copied!' : 'Copy all short links'"></span>
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:.875rem">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid rgba(128,128,128,.3)">
                        <th style="padding:8px">Referer URL</th>
                        <th style="padding:8px">Type</th>
                        <th style="padding:8px">Destination URL</th>
                        <th style="padding:8px">Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr style="border-bottom:1px solid rgba(128,128,128,.15);vertical-align:top">
                            <td style="padding:8px;font-weight:600;white-space:nowrap">{{ $r['short'] }}</td>
                            <td style="padding:8px;white-space:nowrap">{{ $r['type'] }}</td>
                            <td style="padding:8px;word-break:break-all">{{ $r['url'] }}</td>
                            <td style="padding:8px;color:#d97706">{{ $r['existing'] !== '' ? 'Already had: '.$r['existing'] : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    @if(count($invalid))
        <x-filament::section heading="Skipped lines (not web addresses)">
            <ul style="font-size:.875rem;color:#dc2626;word-break:break-all">
                @foreach($invalid as $line)<li>{{ $line }}</li>@endforeach
            </ul>
        </x-filament::section>
    @endif
</x-filament-panels::page>
