{{--
    PWA Install Banner Component
    Included at the bottom of all master layouts.
    Visibility is controlled by pwa.js at runtime.
--}}

{{-- ── Android / Chrome / Edge install banner ─────────────────────────── --}}
<div id="pwa-install-banner"
     class="d-none position-fixed bottom-0 start-0 end-0"
     style="z-index: 10000; pointer-events: auto;">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 shadow-lg"
         style="background: linear-gradient(135deg, #ec1d23 0%, #b81118 100%); color: #fff;">

        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/icons/icon-72x72.png') }}"
                 alt="FOMS BMS"
                 width="44"
                 height="44"
                 class="rounded-2 bg-white p-1"
                 onerror="this.style.display='none'">
            <div>
                <div class="fw-semibold" style="font-size: 0.95rem;">Install FOMS BMS</div>
                <div class="opacity-75" style="font-size: 0.78rem;">
                    Add to your home screen for quick access
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button id="pwa-install-btn"
                    type="button"
                    class="btn btn-light btn-sm fw-semibold"
                    style="min-width: 72px;">
                Install
            </button>
            <button id="pwa-install-dismiss"
                    type="button"
                    class="btn btn-sm"
                    aria-label="Dismiss"
                    style="color: rgba(255,255,255,.7); font-size: 1.25rem; padding: 0 6px; line-height: 1;">
                &times;
            </button>
        </div>

    </div>
</div>

{{-- ── iOS "Add to Home Screen" instructions banner ────────────────────── --}}
<div id="pwa-ios-banner"
     class="d-none position-fixed bottom-0 start-0 end-0"
     style="z-index: 10000; pointer-events: auto;">
    <div class="position-relative text-center px-4 py-3 shadow-lg"
         style="background: #1f2937; color: #fff; border-radius: 12px 12px 0 0;">

        <button id="pwa-ios-dismiss"
                type="button"
                class="position-absolute top-0 end-0 mt-2 me-3 btn-close btn-close-white"
                aria-label="Dismiss"
                style="filter: brightness(1.5);"></button>

        <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
            <img src="{{ asset('images/icons/icon-72x72.png') }}"
                 alt="FOMS BMS"
                 width="44"
                 height="44"
                 class="rounded-2"
                 onerror="this.style.display='none'">
            <div class="text-start">
                <div class="fw-semibold" style="font-size: 0.95rem;">Install FOMS BMS</div>
                <div class="opacity-75" style="font-size: 0.78rem;">on your iPhone / iPad</div>
            </div>
        </div>

        <p class="mb-0 opacity-75" style="font-size: 0.82rem;">
            Tap
            {{-- Safari share icon --}}
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" style="vertical-align: middle;">
                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                <polyline points="16 6 12 2 8 6"/>
                <line x1="12" y1="2" x2="12" y2="15"/>
            </svg>
            then tap
            <strong style="color: #60a5fa;">"Add to Home Screen"</strong>
        </p>

    </div>
</div>
