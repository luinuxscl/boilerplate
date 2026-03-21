<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $currentLocale;

    /** @var array<string, string> */
    public array $locales;

    public function mount(): void
    {
        $this->currentLocale = App::getLocale();
        $this->locales = config('app.locale_names', ['en' => 'English']);
    }

    public function switchLocale(string $locale): void
    {
        $supported = config('app.supported_locales', ['en']);

        if (! in_array($locale, $supported)) {
            return;
        }

        session(['locale' => $locale]);

        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }

        $this->redirect(request()->header('Referer', '/'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.language-switcher');
    }
}
