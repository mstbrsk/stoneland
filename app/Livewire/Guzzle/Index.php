<?php

namespace App\Livewire\Guzzle;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Spatie\Browsershot\Browsershot;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
class Index extends Component
{

    public $responseData;

    public function fetchData()
    {
        try {
            // Guzzle ile bir GET isteği gönderiyoruz
            $response = Http::get('https://jsonplaceholder.typicode.com/posts/1');

            // API'den gelen veriyi alıyoruz
            $this->responseData = $response->json();
        } catch (\Exception $e) {
            // Hata durumunda burada işlemler yapabilirsiniz
            $this->responseData = ['error' => $e->getMessage()];
        }
    }



    public function generatePdf(): Response
    {
        $pdf = PDF::loadView('livewire.report-card-generator')->setPaper('A4');
        return $pdf->stream('report.pdf');
    }

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
        return view('livewire.guzzle.index');
    }
}
