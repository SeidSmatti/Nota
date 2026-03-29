document.addEventListener('DOMContentLoaded', function() {

    // Mobile menu toggle
    var menuToggle = document.getElementById('menu-toggle');
    var siteNavigation = document.getElementById('site-navigation');

    if (menuToggle && siteNavigation) {
        menuToggle.addEventListener('click', function() {
            siteNavigation.classList.toggle('toggled');
            var expanded = siteNavigation.classList.contains('toggled');
            menuToggle.setAttribute('aria-expanded', expanded);
            var iconSpan = menuToggle.querySelector('.hamburger-icon');
            if (iconSpan) iconSpan.textContent = expanded ? '✕' : '☰';
        });
    }

    // Dark/light theme toggle
    var themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Sync with the state set by the inline anti-flash script in <head>
        themeToggle.textContent = document.documentElement.getAttribute('data-theme') === 'dark' ? '☼' : '☾';

        themeToggle.addEventListener('click', function() {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            themeToggle.textContent = next === 'dark' ? '☼' : '☾';
        });
    }

    // Focus (reading) mode
    var focusBtn = document.getElementById('btn-focus');
    if (focusBtn) {
        var focusLabel = focusBtn.querySelector('.btn-label');

        focusBtn.addEventListener('click', function() {
            var active = document.body.classList.toggle('focus-mode');
            if (active) {
                focusLabel.textContent = nota.i18n.focusExit;
                focusBtn.setAttribute('aria-label', nota.i18n.focusExit);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                focusLabel.textContent = nota.i18n.focusEnter;
                focusBtn.setAttribute('aria-label', nota.i18n.focusEnter);
            }
        });
    }

    // Citation modal — full article
    var btnCite = document.getElementById('btn-cite');
    var modal = document.getElementById('citation-modal');
    var closeSpan = document.querySelector('.close-modal');

    if (btnCite) {
        btnCite.onclick = function() {
            var title = document.querySelector('.entry-title') ? document.querySelector('.entry-title').innerText : document.title;

            var author = nota.i18n.unknownAuthor;
            var authorEl = document.querySelector('.author-name');
            if (authorEl) {
                author = authorEl.innerText.trim();
            } else {
                var byline = document.querySelector('.byline');
                if (byline) author = byline.innerText.trim();
            }

            var year = new Date().getFullYear();
            var dateEl = document.querySelector('.posted-on');
            if (dateEl) {
                var matches = dateEl.innerText.match(/\d{4}/);
                if (matches) year = matches[0];
            }

            var url      = window.location.href;
            var siteName = nota.siteName;
            var accessed = nota.i18n.accessed;
            var locale   = nota.lang === 'fr' ? 'fr-FR' : 'en-US';

            var now        = new Date();
            var accessDate = now.toLocaleDateString(locale, { day: 'numeric', month: 'long', year: 'numeric' });
            var urldate    = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            var nameParts  = author.trim().split(' ');
            var lastName   = nameParts[nameParts.length - 1];
            var firstName  = nameParts.slice(0, -1).join(' ');
            var firstInit  = firstName ? firstName.charAt(0).toUpperCase() + '.' : '';
            var apaAuthor  = firstName ? lastName + ', ' + firstInit : lastName;
            var longAuthor = firstName ? lastName + ', ' + firstName : lastName;
            var citeKey    = lastName.toLowerCase().replace(/[^a-z0-9]/g, '') + year;

            // APA 7th ed.
            var apaHTML = apaAuthor + ' (' + year + '). <em>' + title + '</em>. ' + siteName + '. ' + accessed + ' ' + accessDate + '. ' + url;
            // MLA 9th ed.
            var mlaHTML = longAuthor + '. "' + title + '." <em>' + siteName + '</em>, ' + year + ', ' + url + '. ' + accessed + ' ' + accessDate + '.';
            // Chicago 17th ed.
            var chicagoHTML = longAuthor + '. "' + title + '." <em>' + siteName + '</em>. ' + year + '. ' + accessed + ' ' + accessDate + '. ' + url + '.';
            // BibTeX — @online (BibLaTeX)
            var bibtexHTML = '@online{' + citeKey + ',\n  author       = {' + longAuthor + '},\n  title        = {' + title + '},\n  year         = {' + year + '},\n  url          = {' + url + '},\n  urldate      = {' + urldate + '},\n  organization = {' + siteName + '}\n}';

            document.getElementById('apa-text').innerHTML = apaHTML;
            document.getElementById('mla-text').innerHTML = mlaHTML;
            document.getElementById('chicago-text').innerHTML = chicagoHTML;
            document.getElementById('bibtex-text').innerText = bibtexHTML;

            modal.style.display = 'block';
        };
    }

    if (closeSpan) closeSpan.onclick = function() { modal.style.display = 'none'; };
    window.onclick = function(event) { if (event.target == modal) modal.style.display = 'none'; };

    // Inline selection citation
    var selCiteBtn = document.getElementById('selection-cite-btn');
    var selModal   = document.getElementById('selection-cite-modal');

    if (selCiteBtn && selModal) {
        var _selText    = '';
        var _selParaNum = 0;
        var textZone    = document.querySelector('.text-content');

        function updateSelBtn() {
            // Don't interfere while the modal is open
            if (selModal.style.display === 'block') return;

            var sel  = window.getSelection();
            var text = (sel && sel.rangeCount) ? sel.toString().trim() : '';

            if (text.length < 3 || !textZone) {
                selCiteBtn.classList.remove('visible');
                return;
            }

            var range = sel.getRangeAt(0);
            if (!textZone.contains(range.commonAncestorContainer)) {
                selCiteBtn.classList.remove('visible');
                return;
            }

            _selText = text;

            // Resolve the paragraph number containing the selection anchor
            var paras = textZone.querySelectorAll('p');
            _selParaNum = 0;
            for (var i = 0; i < paras.length; i++) {
                if (paras[i].contains(range.startContainer)) { _selParaNum = i + 1; break; }
            }

            // getBoundingClientRect() is viewport-relative — correct for position:fixed.
            // Do NOT add scrollY/scrollX here.
            var rect = range.getBoundingClientRect();
            var btnW = selCiteBtn.offsetWidth  || 80;
            var btnH = selCiteBtn.offsetHeight || 34;
            var midX = rect.left + rect.width / 2;

            // Prefer above the selection; fall back to below if too close to top
            var top  = rect.top - btnH - 10;
            if (top < 8) top = rect.bottom + 10;
            var left = Math.max(8, Math.min(window.innerWidth - btnW - 8, midX - btnW / 2));

            selCiteBtn.style.top  = top  + 'px';
            selCiteBtn.style.left = left + 'px';
            selCiteBtn.classList.add('visible');
        }

        // Short delay lets the browser finalize the selection range before reading it
        document.addEventListener('mouseup',  function() { setTimeout(updateSelBtn, 10); });
        document.addEventListener('touchend', function() { setTimeout(updateSelBtn, 100); });

        // Keep the selection alive when clicking the button
        selCiteBtn.addEventListener('mousedown', function(e) { e.preventDefault(); });

        selCiteBtn.addEventListener('click', function() {
            var title    = document.querySelector('.entry-title') ? document.querySelector('.entry-title').innerText : document.title;
            var author   = nota.i18n.unknownAuthor;
            var authorEl = document.querySelector('.author-name');
            if (authorEl) author = authorEl.innerText.trim();

            var year   = new Date().getFullYear();
            var dateEl = document.querySelector('.meta-value');
            if (dateEl) { var m = dateEl.innerText.match(/\d{4}/); if (m) year = m[0]; }

            var url      = window.location.href;
            var siteName = nota.siteName;
            var accessed = nota.i18n.accessed;
            var passage  = nota.i18n.passage;
            var locale   = nota.lang === 'fr' ? 'fr-FR' : 'en-US';

            var now        = new Date();
            var accessDate = now.toLocaleDateString(locale, { day: 'numeric', month: 'long', year: 'numeric' });
            var urldate    = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
            var nameParts  = author.trim().split(' ');
            var lastName   = nameParts[nameParts.length - 1];
            var firstName  = nameParts.slice(0, -1).join(' ');
            var firstInit  = firstName ? firstName.charAt(0).toUpperCase() + '.' : '';
            var apaAuthor  = firstName ? lastName + ', ' + firstInit : lastName;
            var longAuthor = firstName ? lastName + ', ' + firstName : lastName;
            var citeKey    = lastName.toLowerCase().replace(/[^a-z0-9]/g, '') + year;
            var locator    = _selParaNum > 0 ? ' (\u00b6\u202f' + _selParaNum + ')' : '';
            var excerpt    = _selText.length > 80 ? _selText.substring(0, 80) + '\u2026' : _selText;

            document.getElementById('sel-quote').textContent = '\u201c' + _selText + '\u201d';

            // APA 7th ed.
            document.getElementById('sel-apa-text').innerHTML =
                apaAuthor + ' (' + year + '). <em>' + title + '</em>' + locator + '. ' +
                siteName + '. ' + accessed + ' ' + accessDate + '. ' + url;
            // MLA 9th ed.
            document.getElementById('sel-mla-text').innerHTML =
                longAuthor + '. \u201c' + title + '.\u201d <em>' + siteName + '</em>, ' + year + locator +
                ', ' + url + '. ' + accessed + ' ' + accessDate + '.';
            // Chicago 17th ed.
            document.getElementById('sel-chicago-text').innerHTML =
                longAuthor + '. \u201c' + title + '.\u201d <em>' + siteName + '</em>. ' + year + locator +
                '. ' + accessed + ' ' + accessDate + '. ' + url + '.';
            // BibTeX — @online (BibLaTeX)
            document.getElementById('sel-bibtex-text').textContent =
                '@online{' + citeKey + ',\n  author       = {' + longAuthor + '},\n  title        = {' + title + '},\n' +
                '  year         = {' + year + '},\n  url          = {' + url + '},\n' +
                '  urldate      = {' + urldate + '},\n  organization = {' + siteName + '},\n' +
                '  note         = {' + passage + '\u202f: \u00ab\u202f' + excerpt + '\u202f\u00bb}\n}';

            // Reset tabs to APA before opening
            var tcs = selModal.getElementsByClassName('tab-content');
            for (var i = 0; i < tcs.length; i++) { tcs[i].style.display = 'none'; }
            var tbs = selModal.getElementsByClassName('tab-btn');
            for (var i = 0; i < tbs.length; i++) { tbs[i].className = tbs[i].className.replace(' active', ''); }
            document.getElementById('sel-APA').style.display = 'block';
            if (tbs[0]) tbs[0].className += ' active';

            selModal.style.display = 'block';
            selCiteBtn.classList.remove('visible');
        });

        document.getElementById('close-sel-modal').onclick = function() { selModal.style.display = 'none'; };
        window.addEventListener('click', function(e) { if (e.target === selModal) selModal.style.display = 'none'; });
    }
});

// Header search — toggle overlay, focus input, close on Escape or outside click
(function() {
    var header      = document.querySelector('.site-header');
    var toggle      = document.getElementById('search-toggle');
    var closeBtn    = document.getElementById('search-close');
    var wrap        = document.getElementById('header-search-wrap');
    var input       = document.getElementById('header-search-input');

    if (!header || !toggle || !closeBtn || !wrap || !input) return;

    function openSearch() {
        header.classList.add('search-open');
        wrap.removeAttribute('aria-hidden');
        toggle.setAttribute('aria-expanded', 'true');
        // Delay focus until after the fade-in starts so the transition is visible
        setTimeout(function() { input.focus(); }, 50);
    }

    function closeSearch() {
        header.classList.remove('search-open');
        wrap.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    }

    toggle.addEventListener('click', openSearch);
    closeBtn.addEventListener('click', closeSearch);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && header.classList.contains('search-open')) closeSearch();
    });

    // Close when clicking outside the overlay
    document.addEventListener('click', function(e) {
        if (header.classList.contains('search-open')
            && !wrap.contains(e.target)
            && e.target !== toggle
            && !toggle.contains(e.target)) {
            closeSearch();
        }
    });
})();

// Sidenotes — desktop alignment and mobile popover
(function() {

    // Position each sidenote vertically to align with its inline reference marker.
    // Dynamically checks whether the right gutter has enough room; falls back to
    // popover mode (JS class + CSS) when zoomed or on narrow viewports.
    // Runs after full page load so fonts and images don't shift measurements.
    function positionSidenotes() {
        var article = document.querySelector('.has-sidenotes');
        if (!article) return;

        var textContent = article.querySelector('.text-content');
        var container   = article.querySelector('.sidenotes-container');
        var notes       = article.querySelectorAll('.sidenote');
        if (!textContent || !notes.length) return;

        var tcRect  = textContent.getBoundingClientRect();
        var hasRoom = (window.innerWidth - tcRect.right) >= 260;

        if (!hasRoom) {
            article.classList.add('notes-popover-mode');
            article.classList.remove('notes-ready');
            return;
        }

        article.classList.remove('notes-popover-mode');

        var tcTop      = tcRect.top + window.scrollY;
        var prevBottom = 0;

        notes.forEach(function(note) {
            var n   = note.getAttribute('data-note');
            var ref = textContent.querySelector('.note-ref[data-note="' + n + '"]');
            if (!ref) return;

            var refTop = ref.getBoundingClientRect().top + window.scrollY - tcTop;
            var top    = Math.max(prevBottom + 10, refTop);
            note.style.top = top + 'px';
            prevBottom = top + note.offsetHeight;
        });

        // Fade in notes only after they are correctly positioned
        article.classList.add('notes-ready');
    }

    // Mobile popover — created once and reused for all notes
    document.addEventListener('DOMContentLoaded', function() {
        var popover = document.createElement('div');
        popover.className = 'sidenote-popover';
        document.body.appendChild(popover);

        document.addEventListener('click', function(e) {
            var ref = e.target.closest && e.target.closest('.note-ref');

            if (ref) {
                // On desktop (sidenote column visible) the note is already readable in the margin
                var article = document.querySelector('.has-sidenotes');
                if (article && !article.classList.contains('notes-popover-mode')) return;

                var n    = ref.getAttribute('data-note');
                var note = document.querySelector('.sidenote[data-note="' + n + '"]');
                if (!note) return;

                // Clone note body, strip the sidenote-number span
                var clone = note.cloneNode(true);
                var num   = clone.querySelector('.sidenote-number');
                if (num) num.remove();

                popover.innerHTML =
                    '<span class="sidenote-popover-close">&times;</span>' +
                    '<span class="sidenote-popover-number">' + n + '</span>' +
                    clone.innerHTML;

                popover.querySelector('.sidenote-popover-close').onclick = function() {
                    popover.classList.remove('visible');
                };

                // Position below the reference, keep within viewport
                var rect = ref.getBoundingClientRect();
                var left = Math.max(8, Math.min(rect.left, window.innerWidth - 296));
                var top  = rect.bottom + 8;
                if (top + 160 > window.innerHeight) top = rect.top - popover.offsetHeight - 8;
                popover.style.left = left + 'px';
                popover.style.top  = Math.max(8, top) + 'px';
                popover.classList.add('visible');

            } else if (!e.target.closest || !e.target.closest('.sidenote-popover')) {
                popover.classList.remove('visible');
            }
        });
    });

    window.addEventListener('load',   positionSidenotes);
    window.addEventListener('resize', positionSidenotes);

})();

// Print: relocate references after body text for academic ordering
(function() {
    var _biblio  = null;
    var _origParent = null;
    var _nextSib    = null;

    window.addEventListener('beforeprint', function() {
        _biblio = document.querySelector('.sidebar-biblio');
        var textContent = document.querySelector('.text-content');
        if (!_biblio || !textContent) return;
        _origParent = _biblio.parentNode;
        _nextSib    = _biblio.nextSibling;
        textContent.parentNode.insertBefore(_biblio, textContent.nextSibling);
    });

    window.addEventListener('afterprint', function() {
        if (!_biblio || !_origParent) return;
        _origParent.insertBefore(_biblio, _nextSib);
        _biblio = null;
    });
})();

// Exposed globally — called from inline event handlers in footer.php and single.php
function showTab(evt, tabName) {
    var tabcontent = document.getElementsByClassName('tab-content');
    for (var i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = 'none'; }
    var tablinks = document.getElementsByClassName('tab-btn');
    for (var i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(' active', ''); }
    document.getElementById(tabName).style.display = 'block';
    evt.currentTarget.className += ' active';
}

function showSelTab(evt, tabName) {
    var container = document.getElementById('selection-cite-modal');
    if (!container) return;
    var tabcontent = container.getElementsByClassName('tab-content');
    for (var i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = 'none'; }
    var tablinks = container.getElementsByClassName('tab-btn');
    for (var i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(' active', ''); }
    document.getElementById(tabName).style.display = 'block';
    evt.currentTarget.className += ' active';
}

function copyToClipboard(elementId) {
    var text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(function() {
        alert(nota.i18n.copied);
    });
}
