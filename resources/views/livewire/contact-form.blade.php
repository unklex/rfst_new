@php
    /** @var \App\Settings\ContactSettings $c */
    $c = $this->settings;
    $turnstileKey = $this->turnstileKey;
@endphp
<div>
    @if ($submitted)
        <div class="form-success">
            <b>Заявка отправлена</b>
            Спасибо! Свяжемся в рабочее время, чтобы уточнить детали.
        </div>
    @else
        <form wire:submit.prevent="submit" autocomplete="on" novalidate>
            @if ($error)
                <div class="field-err" role="alert" style="margin-bottom:4px">{{ $error }}</div>
            @endif

            <div>
                <label for="cf-name">{{ $c->form_label_name }}</label>
                <input id="cf-name" type="text" wire:model.blur="name" autocomplete="name"
                       maxlength="120"
                       placeholder="{{ $c->form_placeholder_name }}"
                       @class(['is-invalid' => $errors->has('name')])>
                @error('name') <span class="field-err">{{ $message }}</span> @enderror
            </div>

            <div class="row">
                <div>
                    <label for="cf-phone">{{ $c->form_label_phone }}</label>
                    <input id="cf-phone" type="tel" wire:model.blur="phone" autocomplete="tel" inputmode="tel"
                           maxlength="32"
                           placeholder="{{ $c->form_placeholder_phone }}"
                           @class(['is-invalid' => $errors->has('phone')])>
                    @error('phone') <span class="field-err">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="cf-email">{{ $c->form_label_email }}</label>
                    <input id="cf-email" type="email" wire:model.blur="email" autocomplete="email" inputmode="email"
                           maxlength="120"
                           placeholder="{{ $c->form_placeholder_email }}"
                           @class(['is-invalid' => $errors->has('email')])>
                    @error('email') <span class="field-err">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="cf-message">{{ $c->form_label_message }}</label>
                <textarea id="cf-message" wire:model.blur="message" rows="4"
                          maxlength="2000"
                          placeholder="{{ $c->form_placeholder_message }}"
                          @class(['is-invalid' => $errors->has('message')])></textarea>
                @error('message') <span class="field-err">{{ $message }}</span> @enderror
            </div>

            {{-- Honeypot: bots fill it, humans never see it. --}}
            <div class="honeypot" aria-hidden="true">
                <label for="cf-website">Website</label>
                <input id="cf-website" type="text" name="website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- Cloudflare Turnstile: rendered only when a site key is configured in IntegrationSettings. --}}
            @if ($turnstileKey)
                <div wire:ignore>
                    <div class="cf-turnstile"
                         data-sitekey="{{ $turnstileKey }}"
                         data-callback="onTurnstileSuccess"
                         data-expired-callback="onTurnstileExpired"
                         data-error-callback="onTurnstileExpired"
                         data-refresh-expired="auto"
                         data-theme="light"></div>
                </div>
            @endif

            <div class="submit">
                <small>
                    <label style="display:flex;gap:8px;align-items:flex-start;cursor:pointer;text-transform:uppercase;letter-spacing:0.12em;font-size:10px;font-family:var(--mono);color:var(--ink-3);margin:0">
                        <input type="checkbox" wire:model="consent" style="margin-top:2px;flex:0 0 auto;accent-color:var(--signal)">
                        <span>{{ $c->form_consent_text }}</span>
                    </label>
                    @error('consent') <span class="field-err" style="margin-top:4px">{{ $message }}</span> @enderror
                </small>
                <button type="submit" class="btn btn-signal" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">{{ $c->form_submit_label }}</span>
                    <span wire:loading wire:target="submit">Отправка…</span>
                    <span class="arr">→</span>
                </button>
            </div>
        </form>
    @endif
</div>
