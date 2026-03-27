@php
    use Illuminate\Support\Js;
    use Rawand\FilamentReveal\Support\RevealTokenGenerator;

    $mask = $getMask();
    $revealedColor = $getRevealedColor();
    $requiresAuth = $getRequiresAuthentication();
    $iconSize = config('filament-reveal.icon_size', 'md');

    $nonce = bin2hex(random_bytes(8));
    $columnId = $nonce;
    $recordId = $getRecord()?->getKey();

    $revealToken = $recordId ? RevealTokenGenerator::generate(
        (string) $recordId,
        $getName(),
        $getRecord()::class
    ) : null;

    $endpoint = RevealTokenGenerator::generateEndpoint();

    $iconSizeClass = match ($iconSize) {
        'sm' => 'frc-icon-sm',
        'lg' => 'frc-icon-lg',
        default => 'frc-icon-md',
    };

    $colorClass = 'frc-color-' . $revealedColor;
@endphp

<div x-data="$revealColumn(
        {{ Js::from($columnId) }},
        {{ Js::from($requiresAuth) }},
        {{ Js::from($endpoint) }},
        {{ Js::from($revealToken) }},
        {{ Js::from([
            'authenticate_to_reveal' => __('filament-reveal::reveal-column.authenticate_to_reveal'),
            'loading'                => __('filament-reveal::reveal-column.loading'),
            'toggle_visibility'      => __('filament-reveal::reveal-column.toggle_visibility'),
        ]) }}
    )"
    class="fi-size-sm fi-ta-text-item fi-ta-text">

    <div class="frc-cell">
    @if ($recordId)
        <button
            type="button"
            @click.stop.prevent="toggle()"
            class="frc-btn"
            :class="{ 'frc-loading': loading }"
            :disabled="loading"
            :title="getTitle()">

            <span class="frc-btn-surface {{ $iconSizeClass }}">
                <x-filament::icon
                    icon="heroicon-o-eye"
                    x-show="!revealed && !loading"
                    class="{{ $iconSizeClass }} frc-icon-muted" />

                <svg
                    x-show="loading"
                    x-cloak
                    class="{{ $iconSizeClass }} frc-spinner frc-icon-muted"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24">
                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <x-filament::icon
                    icon="heroicon-o-eye-slash"
                    x-show="revealed && !loading"
                    x-cloak
                    class="{{ $iconSizeClass }} {{ $colorClass }}" />
            </span>
        </button>

        <div class="frc-value">
            <span
                x-show="!revealed"
                x-cloak
                class="frc-text frc-icon-muted">
                {{ $mask }}
            </span>

            <span
                x-show="revealed"
                x-cloak
                @click.stop.prevent="copy()"
                x-tooltip.raw="{{ __('filament-reveal::reveal-column.click_to_copy') }}"
                class="frc-text frc-text-revealed {{ $colorClass }}"
                x-text="data || '{{ __('filament-reveal::reveal-column.loading') }}'">
            </span>
        </div>
    @else
        <span class="frc-text frc-icon-muted" style="grid-column: 1 / -1; font-style: italic;">
            {{ __('filament-reveal::reveal-column.no_data') }}
        </span>
    @endif
    </div>
</div>

