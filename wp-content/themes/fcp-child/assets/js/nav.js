/**
 * Navigation mobile : bascule accessible du menu.
 * Présentation uniquement — aucune logique métier.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.fcp-nav__toggle');
        var list = document.getElementById('fcp-nav-list');
        if (!toggle || !list) {
            return;
        }

        function setOpen(open) {
            toggle.setAttribute('aria-expanded', String(open));
            if (open) {
                list.removeAttribute('hidden');
            } else {
                list.setAttribute('hidden', '');
            }
        }

        setOpen(false);

        toggle.addEventListener('click', function () {
            var open = toggle.getAttribute('aria-expanded') === 'true';
            setOpen(!open);
        });

        // Referme au clavier (Échap).
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                setOpen(false);
            }
        });
    });
})();
