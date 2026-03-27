<?php

namespace Rawand\FilamentReveal\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire component for password authentication using Filament Actions
 */
class AuthenticateReveal extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $columnId = '';
    public ?string $token = null;

    /**
     * Open authentication modal using Filament Action
     */
    #[On('openAuthModal')]
    public function openModal(string $columnId = '', ?string $token = null): void
    {
        $this->columnId = $columnId;
        $this->token = $token;

        // Trigger the Filament Action modal
        $this->mountAction('authenticate');
    }

    /**
     * Define the authentication action with modal
     */
    public function authenticateAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-reveal::reveal-column.authenticate_button'))
            ->modalHeading(__('filament-reveal::reveal-column.modal_title'))
            ->modalDescription(__('filament-reveal::reveal-column.modal_description'))
            ->modalSubmitActionLabel(__('filament-reveal::reveal-column.authenticate_button'))
            ->modalCancelActionLabel(__('filament-reveal::reveal-column.cancel_button'))
            ->form([
                TextInput::make('password')
                    ->label(__('filament-reveal::reveal-column.password_label'))
                    ->placeholder(__('filament-reveal::reveal-column.password_placeholder'))
                    ->password()
                    ->required()
                    ->autocomplete('current-password'),
            ])
            ->action(function (array $data, Action $action) {
                $user = $this->getAuthenticatedUser();

                if (!$user) {
                    Notification::make()
                        ->title(__('filament-reveal::reveal-column.authentication_failed'))
                        ->body(__('filament-reveal::reveal-column.unauthenticated'))
                        ->danger()
                        ->send();
                    $action->halt();
                    return;
                }

                if (!Hash::check($data['password'], $user->password)) {
                    Notification::make()
                        ->title(__('filament-reveal::reveal-column.authentication_failed'))
                        ->body(__('filament-reveal::reveal-column.authentication_failed'))
                        ->danger()
                        ->send();
                    $action->halt();
                    return;
                }

                // Success - trigger reveal
                $this->dispatchRevealEvent();

                Notification::make()
                    ->title(__('filament-reveal::reveal-column.authenticate_button'))
                    ->success()
                    ->send();
            });
    }

    /**
     * Get authenticated user from any guard
     */
    protected function getAuthenticatedUser(): ?Authenticatable
    {
        foreach (array_keys(config('auth.guards', [])) as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        return null;
    }

    /**
     * Dispatch reveal event to Alpine.js component
     */
    protected function dispatchRevealEvent(): void
    {
        $columnId = $this->columnId;

        $this->js(<<<JS
            setTimeout(() => {
                const elements = document.querySelectorAll('[x-data]');
                for (const el of elements) {
                    const xData = el.getAttribute('x-data');
                    if (xData?.includes('\$revealColumn') && xData?.includes('{$columnId}')) {
                        const alpine = Alpine.\$data(el);
                        if (alpine && typeof alpine.toggle === 'function') {
                            alpine.authenticated = true;
                            alpine.toggle();
                            break;
                        }
                    }
                }
            }, 150);
        JS);
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('filament-reveal::livewire.authenticate-reveal');
    }
}
