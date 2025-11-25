<!-- Alerta de actualización (desaparece por 5 días al cerrarla) -->
<div id="update-alert" class="max-w-7xl mx-auto px-4 py-3 mt-4 rounded-lg shadow-sm bg-yellow-50 border border-yellow-200 text-yellow-800 flex items-center justify-between gap-4" role="status" aria-live="polite">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
        <div class="text-sm">
            <strong class="font-medium">Actualización:</strong>
            Hemos actualizado la página. Revisa las novedades.
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button id="update-alert-close" type="button" class="text-sm px-3 py-1 rounded bg-yellow-100 hover:bg-yellow-200 border border-yellow-200">Cerrar</button>
    </div>
</div>

<script>
(function(){
    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/;SameSite=Lax";
    }
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    var cookieName = 'sgi_update_alert_closed';
    var alertEl = document.getElementById('update-alert');
    var closeBtn = document.getElementById('update-alert-close');

    if (!alertEl) return;

    // Si la cookie existe, ocultar inmediatamente
    if (getCookie(cookieName)) {
        alertEl.style.display = 'none';
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            setCookie(cookieName, '1', 5); // 5 días
            // ocultar con transición simple
            alertEl.style.transition = 'opacity 200ms ease, height 200ms ease, margin 200ms ease';
            alertEl.style.opacity = '0';
            alertEl.style.margin = '0';
            alertEl.style.height = '0';
            setTimeout(function(){ alertEl.style.display = 'none'; }, 250);
        });
    }
})();