{{--
PWA Install Banner Component
Included at the bottom of all master layouts.
Visibility is controlled by pwa.js at runtime.
--}}

{{-- ── Android / Chrome / Edge install banner ─────────────────────────── --}}
<div id="pwa-install-banner" class="d-none position-fixed bottom-0 mb-4 px-3"
    style="z-index: 10000; pointer-events: auto; width: 100%; max-width: 480px; transition: all 0.3s ease;">
    <div class="d-flex align-items-center justify-content-between p-3 shadow-lg rounded-4 border border-white border-opacity-10"
        style="background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); color: #fff;">

        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/icons/icon-72x72.png') }}" alt="FOMS BMS" width="46" height="46"
                class="rounded-3 shadow p-0.5 bg-white bg-opacity-10" onerror="this.style.display='none'">
            <div>
                <div class="fw-bold text-white mb-0.5"
                    style="font-size: 0.95rem; font-family: 'Inter', system-ui, sans-serif;">Install FOMS BMS</div>
                <div class="text-white text-opacity-70" style="font-size: 0.78rem;">
                    Add to your home screen for quick access
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button id="pwa-install-btn" type="button" class="btn btn-sm fw-bold rounded-pill px-3.5 py-1.5 text-white"
                style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border: none; font-size: 0.8rem; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3); transition: transform 0.2s;">
                Install
            </button>
            <button id="pwa-install-dismiss" type="button"
                class="btn btn-sm text-white text-opacity-60 d-flex align-items-center justify-content-center p-0 rounded-circle"
                aria-label="Dismiss"
                style="width: 26px; height: 26px; font-size: 1.25rem; line-height: 1; transition: all 0.2s;">
                &times;
            </button>
        </div>

     </div>
</div>

{{-- ── iOS "Add to Home Screen" instructions banner ────────────────────── --}}
<div id="pwa-ios-banner" class="d-none position-fixed bottom-0 mb-4 px-3"
    style="z-index: 10000; pointer-events: auto; width: 100%; max-width: 480px; transition: all 0.3s ease;">
    <div class="position-relative p-3 shadow-lg rounded-4 border border-white border-opacity-10"
        style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); color: #fff;">

        <button id="pwa-ios-dismiss" type="button"
            class="position-absolute top-0 end-0 mt-3 me-3 btn-close btn-close-white" aria-label="Dismiss"
            style="filter: brightness(1.2); scale: 0.8;"></button>

        <div class="d-flex align-items-center gap-3 mb-2.5">
            <img src="{{ asset('images/icons/icon-72x72.png') }}" alt="FOMS BMS" width="46" height="46"
                class="rounded-3 shadow p-0.5 bg-white bg-opacity-10" onerror="this.style.display='none'">
            <div>
                <div class="fw-bold text-white mb-0.5"
                    style="font-size: 0.95rem; font-family: 'Inter', system-ui, sans-serif;">Install FOMS BMS</div>
                <div class="text-white text-opacity-70" style="font-size: 0.78rem;">on your iPhone / iPad</div>
            </div>
        </div>

        <p class="mb-0 text-white text-opacity-80 ps-1"
            style="font-size: 0.82rem; font-family: 'Inter', system-ui, sans-serif; line-height: 1.5;">
            Tap the Safari share icon
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin: 0 2px;">
                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                <polyline points="16 6 12 2 8 6" />
                <line x1="12" y1="2" x2="12" y2="15" />
            </svg>
            below, then tap <strong class="text-info">"Add to Home Screen"</strong>.
        </p>

    </div>
</div>

<style>
    @media (min-width: 576px) {

        #pwa-install-banner,
        #pwa-ios-banner {
            right: 24px !important;
            left: auto !important;
            transform: none !important;
        }

        #pwa-install-banner:hover,
        #pwa-ios-banner:hover {
            transform: translateY(-2px) !important;
        }
    }

    @media (max-width: 575.98px) {

        #pwa-install-banner,
        #pwa-ios-banner {
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
            width: calc(100% - 32px) !important;
        }

        #pwa-install-banner:hover,
        #pwa-ios-banner:hover {
            transform: translate(-50%, -2px) !important;
        }
    }

    #pwa-install-dismiss:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9) !important;
    }

    #pwa-install-btn:hover {
        transform: scale(1.04);
        filter: brightness(1.1);
    }
</style>