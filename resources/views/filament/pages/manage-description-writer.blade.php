<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        <div class="mt-6">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>

    <x-filament::section heading="Try it" description="Paste a title and a link to see what the writer produces. Nothing is saved.">
        <div style="display:grid;gap:12px">
            <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
                <label><span style="font-size:.875rem;font-weight:500">Type</span>
                    <x-filament::input.wrapper><x-filament::input.select wire:model="previewType"><option value="product">Product</option><option value="store">Store</option></x-filament::input.select></x-filament::input.wrapper>
                </label>
                <label><span style="font-size:.875rem;font-weight:500">Length</span>
                    <x-filament::input.wrapper><x-filament::input.select wire:model="previewLength">
                        @foreach(\App\Support\Describer\Describer::LENGTHS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </x-filament::input.select></x-filament::input.wrapper>
                </label>
            </div>
            <label><span style="font-size:.875rem;font-weight:500">Title</span>
                <x-filament::input.wrapper :valid="! $errors->has('previewTitle')"><x-filament::input type="text" wire:model="previewTitle" placeholder="SUNLU PLA Filament – Best for Beginners" /></x-filament::input.wrapper>
                @error('previewTitle')<span style="color:#dc2626;font-size:.8rem">{{ $message }}</span>@enderror
            </label>
            <label><span style="font-size:.875rem;font-weight:500">Link</span>
                <x-filament::input.wrapper :valid="! $errors->has('previewLink')"><x-filament::input type="text" wire:model="previewLink" placeholder="https://store.sunlu.com/" /></x-filament::input.wrapper>
                @error('previewLink')<span style="color:#dc2626;font-size:.8rem">{{ $message }}</span>@enderror
            </label>
            <div>
                <x-filament::button wire:click="preview" icon="heroicon-o-sparkles" wire:loading.attr="disabled" wire:target="preview">
                    <span wire:loading.remove wire:target="preview">Write a description</span>
                    <span wire:loading wire:target="preview">Reading the website…</span>
                </x-filament::button>
            </div>
            @if($previewResult)
                <style>.dw-preview p{margin:.5rem 0}.dw-preview ul{list-style:disc;padding-left:1.25rem}</style>
                <div class="dw-preview" style="border:1px solid rgba(128,128,128,.3);border-radius:12px;padding:12px 16px">
                    {!! str_contains($previewResult, '<p>') ? $previewResult : nl2br(e($previewResult)) !!}
                </div>
                @if($previewNote)<p style="color:#6b7280;font-size:.8rem">{{ $previewNote }} Click again for another version.</p>@endif
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
