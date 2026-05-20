<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.empty')]
#[Title('Login')]
class extends Component {

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public bool $remember = false;

    public string $errorMessage = '';

    public bool $showError = false;

    public function mount()
    {
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth('web')->attempt($credentials, $this->remember)) {
            request()->session()->regenerate();

            return redirect()->intended('/');
        }

        $this->errorMessage = 'Hatalı e-posta veya parola!';
        $this->showError = true;
    }
};
?>

<div class="flex w-full h-screen bg-gray-100">
    <!-- Left side with login form -->
    <div class="flex items-center justify-center w-full md:w-1/2 lg:w-2/5 bg-white">
        <div class="w-full max-w-md px-8 py-12">
            <div class="mb-8 text-center">
                <img src="{{ asset('/assets/images/logo.png') }}" alt="Logo" class="mx-auto mb-6" style="width: 180px;">
                <h1 class="text-3xl font-bold text-gray-800">Güvenli Giriş</h1>
            </div>

            <x-form wire:submit.prevent="login" class="space-y-6">
                <x-alert x-show="$wire.showError" class="alert-error mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r" :title="$errorMessage" icon="o-exclamation-triangle"/>

                <div>
                    <x-input placeholder="E-Posta" wire:model="email" icon="o-envelope" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <x-input placeholder="Parola" wire:model="password" type="password" icon="o-key" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="remember" class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-sm text-gray-600">Beni Hatırla</span>
                    </label>
{{--
                    <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">Parolanızı mı unuttunuz?</a>
--}}
                </div>

                <div>
                    <x-button label="Giriş Yap" type="submit" icon="o-paper-airplane" class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out" spinner="login"/>
                </div>
            </x-form>
        </div>
    </div>

    <!-- Right side with image -->
    <div class="hidden md:block md:w-1/2 lg:w-3/5">
        <img src="{{ asset('/assets/images/login-bg.jpg') }}" alt="Placeholder Image" class="object-cover h-full w-full">
    </div>
</div>
