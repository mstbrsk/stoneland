<?php
/** @var \App\Models\Message[] $messages */

use Livewire\Volt\Component;

new class extends Component {
    public \Illuminate\Support\Collection $messages;

    public string $newMessage = '';

    public function mount()
    {
        $this->loadMessages();
    }

    public function loadMessages()
    {
        $this->messages = \App\Models\Message::latest()->take(50)->get()->reverse();
    }

    public function sendMessage()
    {
        $message = \App\Models\Message::create([
            'content' => $this->newMessage,
            'sender_id' => auth('web')->id(),
            'receiver_id' => auth('web')->id(),
        ]);

        $this->reset('newMessage');
        $this->loadMessages();

        \App\Events\MessageSent::dispatch($message);
    }

    #[\Livewire\Attributes\On('echo:chat,MessageSent')]
    public function handleNewMessage()
    {
        $this->loadMessages();
    }
}
?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-md rounded-lg max-w-lg w-full">
        <div class="p-4 border-b bg-gray-200">
            <h2 class="text-lg font-semibold">Chat Box</h2>
        </div>
        <div class="p-4 h-80 overflow-y-auto">
            @foreach($messages  as $message)
                <div class="mb-2">
                    <p class="text-sm text-gray-600">{{ $message->sender->name }}:</p>
                    <p class="text-sm">{{ $message->content }}</p>
                </div>
            @endforeach
        </div>
        <div class="p-4 border-t">
            <form wire:submit="sendMessage">
                <x-input
                    type="text"
                    wire:model="newMessage"
                    class="w-full px-3 py-2 border rounded-lg"
                    placeholder="Type your message..."
                />
                <button
                    type="submit"
                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg"
                >
                    Send
                </button>
            </form>
        </div>
    </div>
</div>
