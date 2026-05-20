<?php

namespace App\Livewire;

use Livewire\Component;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class GeneratePdf extends Component
{
    public function createPDF()
    {
        $data = ['title' => 'Laravel Livewire PDF Tutorial'];
        $pdf = PDF::loadView('pdf_view', $data);

        $filePath = 'public/example.pdf';
        Storage::put($filePath, $pdf->output());

        session()->flash('message', 'PDF başarıyla oluşturuldu ve kaydedildi.');
    }



    public function render()
    {
        return view('livewire.generate-pdf');
    }
}
