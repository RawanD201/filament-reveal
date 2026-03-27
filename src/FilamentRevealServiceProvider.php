<?php

namespace Rawand\FilamentReveal;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Rawand\FilamentReveal\Http\Controllers\RevealDataController;
use Rawand\FilamentReveal\Livewire\AuthenticateReveal;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRevealServiceProvider extends PackageServiceProvider
{
    public const VERSION = '1.0.0';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-reveal')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasAssets();
    }

    public function packageBooted(): void
    {
        $this->registerLivewireComponents();
        $this->registerRoutes();
        $this->registerFilamentAssets();
        $this->registerRenderHook();
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('filament-reveal-authenticate-reveal', AuthenticateReveal::class);
    }

    protected function registerRoutes(): void
    {
        $hash = hash_hmac('sha256', 'filament-reveal', config('app.key'));
        $identifier = substr($hash, 0, 16);

        Route::middleware(config('filament-reveal.middleware', ['web']))
            ->post("/x-fr-{$identifier}", [RevealDataController::class, 'fetch'])
            ->name('filament-reveal.fetch-data');
    }

    protected function registerFilamentAssets(): void
    {
        FilamentAsset::register([
            Css::make(
                'filament-reveal-styles',
                __DIR__ . '/../public/css/filament-reveal.css'
            ),
            Js::make(
                'filament-reveal-scripts',
                __DIR__ . '/../public/js/filament-reveal.js'
            ),
        ], package: 'rawand201/filament-reveal');
    }

    /**
     * Inject the auth modal Livewire component at the very end of the
     * Filament panel body — outside the table DOM — so modal clicks
     * cannot bubble up into table row navigation handlers.
     */
    protected function registerRenderHook(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn () => Blade::render("@livewire('filament-reveal-authenticate-reveal')"),
        );
    }
}
