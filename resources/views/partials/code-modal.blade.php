{{-- One shared pop-up for every "Show code" button (offer rows and coupon cards). --}}
<dialog class="codemodal" id="codemodal" aria-labelledby="cm-title">
    <form method="dialog"><button class="cm-x" aria-label="Close">&times;</button></form>
    <div class="cm-logo"></div>
    <p class="cm-store"></p>
    <h3 id="cm-title" class="cm-title"></h3>
    <div class="cm-codebox">
        <span class="cm-code"></span>
        <button type="button" class="btn cm-copy">Copy</button>
    </div>
    <p class="cm-hint">Copy the code, then paste it at checkout. The store has opened in a new tab.</p>
    <a class="btn alt cm-go" href="#" target="_blank" rel="sponsored nofollow noopener">Go to store &rarr;</a>
</dialog>
<script>
(function () {
    var modal = document.getElementById('codemodal');
    if (!modal || typeof modal.showModal !== 'function') modal = null;

    function copy(text, done) {
        function fallback() {
            var t = document.createElement('textarea');
            t.value = text; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
            document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); done(); } catch (_) {}
            t.remove();
        }
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done, fallback);
        else fallback();
    }

    function copied() {
        var b = modal && modal.querySelector('.cm-copy');
        if (!b) return;
        b.textContent = 'Copied!';
        b.classList.add('done');
        setTimeout(function () { b.textContent = 'Copy'; b.classList.remove('done'); }, 2200);
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-reveal]');
        if (btn) {
            // The link itself still opens the store in a new tab; here we show the code.
            var code = btn.getAttribute('data-code') || (btn.nextElementSibling ? btn.nextElementSibling.textContent.trim() : '');
            var inline = btn.nextElementSibling;
            if (inline && inline.classList.contains('code')) { inline.hidden = false; inline.style.display = ''; btn.hidden = true; }
            if (!modal) { copy(code, function () {}); return; }

            var logo = modal.querySelector('.cm-logo');
            logo.textContent = '';
            var store = btn.getAttribute('data-store') || '';
            if (btn.getAttribute('data-logo')) {
                var img = document.createElement('img');
                img.src = btn.getAttribute('data-logo'); img.alt = store + ' logo';
                logo.appendChild(img);
            } else {
                var av = document.createElement('span');
                av.className = 'logoavatar'; av.textContent = store.charAt(0).toUpperCase();
                logo.appendChild(av);
            }
            modal.querySelector('.cm-store').textContent = store;
            modal.querySelector('.cm-title').textContent = btn.getAttribute('data-title') || '';
            modal.querySelector('.cm-code').textContent = code;
            var go = modal.querySelector('.cm-go');
            go.href = btn.href;
            go.textContent = store ? 'Go to ' + store + ' →' : 'Go to store →';
            modal.showModal();
            copy(code, copied);
            return;
        }
        if (modal && e.target.closest('.cm-copy')) copy(modal.querySelector('.cm-code').textContent, copied);
        if (modal && e.target === modal) modal.close();
    });

    // Categories menu: close when clicking elsewhere or pressing Escape.
    document.addEventListener('click', function (e) {
        document.querySelectorAll('[data-navdrop][open]').forEach(function (d) { if (!d.contains(e.target)) d.open = false; });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') document.querySelectorAll('[data-navdrop][open]').forEach(function (d) { d.open = false; });
    });
})();
</script>
