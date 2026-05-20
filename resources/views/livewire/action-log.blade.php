<div>
    <x-drawer wire:model="showHistoryDrawer" title="İşlem Geçmişi" right separator with-close-button
              class="lg:w-1/3">
        <div class="grid gap-5">
            @php
                /** @var \App\Models\ActionLog[] $actionLogs */
            @endphp
            @foreach($actionLogs as $key => $actionLog)
                <x-timeline-item :title="$actionLog->notes"
                                 subtitle="{{ $actionLog->created_at->format('d.m.Y H:i') }}"
                                 description="{{ $actionLog->createdBy->name }}"
                                 :first="$key==0"/>
            @endforeach
        </div>

        <x-slot:actions>
            <x-button label="Kapat" icon="o-check" class="btn-primary" wire:click="$set('showHistoryDrawer',false)" />
        </x-slot:actions>
    </x-drawer>
</div>
