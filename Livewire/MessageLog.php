<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Exceptions\ToastException;
use Mary\Traits\Toast;

class MessageLog extends Component
{
    use Toast;

    public string $id = '';
    public string $title = 'Mesaj Geçmişi';
    public string $relationId = '';
    public string $relationType = '';
    public string $redirectTo = '';

    public Collection $messages;

    public string $message = '';

    public function mount()
    {
        $this->messages = \App\Models\MessageLog::latest()
            ->where('relation_id', $this->relationId)
            ->where('relation_type', $this->relationType)
            ->get();
    }

    /**
     * @throws ToastException
     */
    public function save()
    {
        if (empty($this->message)) {
            throw ToastException::error('Hata', 'Lütfen mesajınızı yazınız!');
        }

        \App\Models\MessageLog::create([
            'message' => $this->message,
            'relation_type' => $this->relationType,
            'relation_id' => $this->relationId,
            'created_by' => auth('web')->id(),
        ]);

        log_action(message: 'Mesaj gönderildi', relationType: $this->relationType, relationId: $this->relationId);

        $text = 'Mesajınız gönderildi';

        if (!empty($this->redirectTo)) {
            $this->success($text, redirectTo: $this->redirectTo);
        } else {
            $this->success($text);
        }
    }

    public function render()
    {
        return view('livewire.message-log');
    }
}
