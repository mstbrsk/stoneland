<div>
    <x-modal :id="$id" :title="$title">
        <div>&nbsp;</div>

        <x-textarea label="Mesajınız" wire:model="message" required/>

        <br>

        <x-button label="Kaydet" class="btn-primary" type="submit" wire:click="save"/>
        <x-button label="Kapat" onclick="{{ $id }}.close()"/>

        <br><br><br>

        <x-card title="" subtitle="En son yazılan mesajlar" shadow separator>
            @foreach($messages as $key => $message)
                <x-timeline-item :title="$message->message"
                                 icon="o-paper-airplane"
                                 subtitle="{{ $message->created_at->format('d.m.Y H:i') }}"
                                 description="{{ $message->createdBy->name }}"
                                 :first="$key==0"/>
            @endforeach
        </x-card>
    </x-modal>
</div>
