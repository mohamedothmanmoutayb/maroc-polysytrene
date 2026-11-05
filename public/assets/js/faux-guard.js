/**
 * Garde "faux" — avertit sans jamais bloquer.
 *
 * Sur les lignes d'articles des ventes, devis et factures, compare ce que
 * l'utilisateur saisit avec ce qui est en base :
 *   - quantité saisie  > stock disponible de l'article
 *   - prix saisi      <> prix enregistré de l'article
 *
 * La valeur de référence est lue au moment où l'article est choisi dans la
 * liste (prix rempli automatiquement + attribut data-stock de l'option).
 * Si la saisie s'écarte, le champ passe en orange, un message s'affiche sous
 * le champ et un petit toast prévient. L'enregistrement reste toujours possible.
 */
(function ($) {
    'use strict';
    if (!$) return;

    var DELAY = 500;
    var timers = new WeakMap();

    $('<style>')
        .text(
            '.faux-warn{border-color:#f59e0b!important;background-color:#fffbeb!important;box-shadow:0 0 0 .15rem rgba(245,158,11,.25)!important}' +
            '.faux-hint{display:block;margin-top:3px;font-size:11px;line-height:1.3;color:#b45309}' +
            '#faux-toast-container{position:fixed;bottom:16px;right:16px;z-index:11000;display:flex;flex-direction:column;gap:8px;max-width:340px}' +
            '.faux-toast{background:#f59e0b;color:#1f1300;border-radius:8px;padding:10px 14px;font-size:13px;box-shadow:0 4px 14px rgba(0,0,0,.2)}'
        )
        .appendTo('head');

    function fmt(n) {
        return (Math.round(n * 10000) / 10000).toLocaleString('fr-FR', { maximumFractionDigits: 4 });
    }

    function toast(message) {
        var box = $('#faux-toast-container');
        if (!box.length) box = $('<div id="faux-toast-container"></div>').appendTo('body');
        var el = $('<div class="faux-toast"></div>').text('⚠ ' + message).appendTo(box);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 4500);
    }

    // Mémorise prix + stock de référence quand un article est choisi.
    $(document).on('change', '.item-select', function () {
        var select = $(this);
        var row = select.closest('tr');
        if (!row.length) return;

        // Les pages remplissent le prix dans leur propre handler ; on lit après.
        setTimeout(function () {
            var opt = select.find('option:selected');
            var priceInput = row.find('.item-price');
            var price = parseFloat(priceInput.val());
            var stock = opt.length ? opt.attr('data-stock') : undefined;

            row.data('fauxRef', {
                price: isNaN(price) || price <= 0 ? null : price,
                stock: stock === undefined || stock === '' ? null : parseFloat(stock)
            });
            row.find('.item-quantity, .item-price').each(function () { evaluate($(this), false); });
        }, 0);
    });

    function setHint(input, message) {
        var hint = input.next('.faux-hint');
        if (!message) {
            input.removeClass('faux-warn');
            hint.remove();
            return;
        }
        input.addClass('faux-warn');
        if (!hint.length) hint = $('<small class="faux-hint"></small>').insertAfter(input);
        hint.text(message);
    }

    function evaluate(input, notify) {
        var row = input.closest('tr');
        var ref = row.data('fauxRef');
        var message = '';

        if (ref && input.val() !== '') {
            var value = parseFloat(input.val());
            if (!isNaN(value)) {
                if (input.hasClass('item-quantity') && ref.stock !== null && value > ref.stock) {
                    message = 'Quantité ' + fmt(value) + ' supérieure au stock disponible (' + fmt(ref.stock) + ')';
                } else if (input.hasClass('item-price') && ref.price !== null &&
                    Math.abs(value - ref.price) > 0.005) {
                    message = 'Prix ' + fmt(value) + ' DH ' + (value > ref.price ? 'supérieur' : 'inférieur') +
                        ' au prix enregistré (' + fmt(ref.price) + ' DH)';
                }
            }
        }

        var previous = input.data('fauxMsg') || '';
        input.data('fauxMsg', message);
        setHint(input, message);
        if (notify && message && message !== previous) toast(message);
    }

    $(document).on('input change', '.item-quantity, .item-price', function () {
        var input = $(this);
        var el = this;
        clearTimeout(timers.get(el));
        timers.set(el, setTimeout(function () { evaluate(input, true); }, DELAY));
    });

    // Une ligne supprimée ou un article remplacé ne garde pas d'avertissement.
    window.FauxGuard = { evaluate: evaluate };
})(window.jQuery);
