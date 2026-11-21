{{-- Layar pembuka berlogo. Hanya muncul saat situs pertama dibuka di tab ini atau saat dimuat ulang, bukan di tiap perpindahan halaman. --}}
<div id="splash" class="splash" role="status" aria-label="Memuat alloBos" hidden>
    <div class="splash-mark">
        <span class="splash-ring" aria-hidden="true"></span>
        <img src="{{ asset('image/ikon-allobos.webp') }}" alt="" width="160" height="160" class="splash-icon">
    </div>
    <img src="{{ asset('image/logo-allobos.webp') }}" alt="alloBos" width="480" height="175" class="splash-word dark:hidden">
    <img src="{{ asset('image/logo-allobos-terang.webp') }}" alt="alloBos" width="480" height="175" class="splash-word hidden dark:block">
    <span class="splash-bar" aria-hidden="true"><i></i></span>
</div>
<script>
    (function () {
        var el = document.getElementById('splash'), root = document.documentElement, nav = null, first = true;
        try { nav = performance.getEntriesByType('navigation')[0]; } catch (e) {}
        try { first = !sessionStorage.getItem('splash'); sessionStorage.setItem('splash', '1'); } catch (e) {}
        if (!first && !(nav && nav.type === 'reload')) { el.remove(); return; }

        el.hidden = false;
        root.classList.add('splash-on');
        var start = Date.now(), done = false;
        function hide() {
            if (done) return;
            done = true;
            // Tampil minimal 1,2 detik supaya animasi logo sempat terlihat utuh.
            setTimeout(function () {
                el.classList.add('splash-out');
                root.classList.remove('splash-on');
                setTimeout(function () { el.remove(); }, 450);
            }, Math.max(0, 1200 - (Date.now() - start)));
        }
        window.addEventListener('load', hide);
        setTimeout(hide, 3500);
    })();
</script>
