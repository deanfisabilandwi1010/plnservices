{{-- Board member profile, filled and animated by site/person-modal.js. One per page. --}}
<dialog class="pm" aria-labelledby="pm-name" data-pm data-lenis-prevent>
    <div class="pm-scrim" data-pm-scrim></div>

    <div class="pm-panel" data-pm-panel>
        <div class="pm-sheet" data-pm-sheet></div>

        <div class="pm-frame" data-pm-frame>
            {{-- The whole photo is shown; the frame around it takes the photo's backdrop colour --}}
            <img class="pm-photo" alt="" data-pm-photo>
            <div class="pm-photo-empty" data-pm-empty hidden><x-glyph name="user-round" class="h-[70%] w-[70%]" stroke-width="1" /></div>
        </div>

        <div class="pm-body">
            <div class="pm-meta" data-pm-in>
                <span data-pm-group></span>
                <span class="tabular-nums" data-pm-count></span>
            </div>
            <h2 id="pm-name" class="pm-name" data-pm-in data-pm-name></h2>
            <p class="pm-position" data-pm-in data-pm-position></p>
            <span class="pm-bar" data-pm-in></span>
            <div class="pm-bio" data-pm-in data-pm-bio></div>

            <nav class="pm-nav" aria-label="Profil lainnya" data-pm-in data-pm-nav>
                <button type="button" class="pm-step" data-pm-prev>
                    <x-glyph name="arrow-left" class="h-4 w-4 shrink-0" />
                    <span><small>Sebelumnya</small><b data-pm-prev-name></b></span>
                </button>
                <div class="pm-dots" data-pm-dots></div>
                <button type="button" class="pm-step pm-step-next" data-pm-next>
                    <span><small>Berikutnya</small><b data-pm-next-name></b></span>
                    <x-glyph name="arrow-right" class="h-4 w-4 shrink-0" />
                </button>
            </nav>
        </div>

        <button type="button" class="pm-close" aria-label="Tutup profil" autofocus data-pm-close><x-glyph name="x" class="h-5 w-5" /></button>
    </div>
</dialog>
