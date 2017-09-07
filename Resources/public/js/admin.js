// Ordonne les éléments
var reorderElements = function() {
    var elements = new Array();
    jQuery('.umanit-sortable-element').each(function(index) {
        var parent = jQuery(this).closest('.umanit-sortable-collection>:first-child');
        if (elements[parent.attr('id')] == undefined) {
            elements[parent.attr('id')] = new Array();
        }

        elements[parent.attr('id')].push(jQuery(this));
    });

    for (key in elements) {
        var total = elements[key].length;

        for (index=0; index<total; index++) {
            var e = elements[key][index];

            e.find('.umanit-sortable-order').first().attr('value', index+1);

            if (index == 0) {
                e.closest('.umanit-sortable-element').children('.umanit-sort-up').hide();
            } else {
                e.closest('.umanit-sortable-element').children('.umanit-sort-up').show();
            }

            if (index+1 == total) {
                e.closest('.umanit-sortable-element').children('.umanit-sort-down').hide();
            } else {
                e.closest('.umanit-sortable-element').children('.umanit-sort-down').show();
            }
        }
    }
};

// Enregistre les événements sur le bouton monter/descendre
var registerEvents = function() {
    // Monter / Descendre une image (ou autre) dans le BO
    jQuery(".umanit-sort-up").on('click', function() {
        var parent = jQuery(this).parent('.umanit-sortable-element');
        var previous = parent.prev('.umanit-sortable-element');

        if (previous.length > 0) {
            parent.after(previous);
            reorderElements();
        }
    });

    jQuery(".umanit-sort-down").on('click', function() {
        var parent = jQuery(this).parent('.umanit-sortable-element');
        var next = parent.next('.umanit-sortable-element');

        if (next.length > 0) {
            parent.before(next);
            reorderElements();
        }
    });

    // Réordonne les éléments lors d'un ajout
    jQuery('.sonata-collection-add').on('sonata-collection-item-added', function() {
        registerEvents();
        reorderElements();
        hideButtons();
    });

    // Réordonne les éléments lors d'une suppression
    jQuery('.sonata-collection-delete').on('sonata-collection-item-deleted', function() {
        jQuery(this).parent().find('.umanit-sortable-order').first().removeClass('umanit-sortable-order');
        jQuery(this).parent().removeClass('umanit-sortable-element');
        registerEvents();
        reorderElements();
        hideButtons();
    });
};

var hideButtons = function() {
    jQuery('.umanit-sortable-collection').each(function() {
        jQuery(this).find('.sonata-collection-add').first().show();
        jQuery(this).find('.sonata-collection-delete').first().show();

        var max = jQuery(this).find('.umanit-collection-collection_max_items').first().data('value');
        var min = jQuery(this).find('.umanit-collection-collection_min_items').first().data('value');
        var orderable = jQuery(this).find('.umanit-collection-collection_orderable').first().data('value');

        var elements = jQuery(this).find('.umanit-sortable-element').length;

        if (max && elements >= max) {
            jQuery(this).find('.sonata-collection-add').first().hide();
        }

        if (min && elements <= min) {
            jQuery(this).find('.sonata-collection-delete').first().hide();
        }

        if (orderable == "0") {
            jQuery(this).find('.umanit-sort-up').hide();
            jQuery(this).find('.umanit-sort-down').hide();
        }
    });
};

jQuery(document).ready(function() {
    registerEvents();
    reorderElements();
    hideButtons();

    // Résoud le bug de a2lix translation, les tabs ne se mettent pas à jour
    jQuery(document).on('click', 'a[data-target^=".a2lix_translationsFields-"]', function() {
        jQuery('a[data-target^=".a2lix_translationsFields-"]').parent('li').removeClass('active');
        jQuery('a[data-target="' + jQuery(this).data('target') + '"]').parent('li').addClass('active');
    });
});

Admin.setup_select2 = function(subject) {
    if (window.SONATA_CONFIG && window.SONATA_CONFIG.USE_SELECT2 && window.Select2) {
        jQuery('select:not([data-sonata-select2="false"])', subject).each(function() {

            var select = jQuery(this);
            var allowClearEnabled = false;

            if (select.find('option[value=""]').length) {
                allowClearEnabled = true;
            }

            if (select.attr('data-sonata-select2-allow-clear')==='true') {
                allowClearEnabled = true;
            } else if (select.attr('data-sonata-select2-allow-clear')==='false') {
                allowClearEnabled = false;
            }

            ereg = /width:(auto|(([-+]?([0-9]*\.)?[0-9]+)(px|em|ex|%|in|cm|mm|pt|pc)))/i;
            select.select2({
                width: function() {

                // this code is an adaptation of select2 code (initContainerWidth function)
                style = this.element.attr('style');
                //console.log("main style", style);
                if (style !== undefined) {
                    attrs = style.split(';');
                    for (i = 0, l = attrs.length; i < l; i = i + 1) {

                        matches = attrs[i].replace(/\s/g, '').match(ereg);

                        if (matches !== null && matches.length >= 1)
                            return matches[1];
                        }
                    }

                    style = this.element.css('width');
                    if (style.indexOf("%") > 0) {
                        return style;
                    }

                    return '100%';
                },
                dropdownAutoWidth: true,
                allowClear: allowClearEnabled
            });

            var popover = select.data('popover');

            if (undefined !== popover) {
                select
                    .select2('container')
                    .popover(popover.options)
                ;
            }
        });
    }
};
