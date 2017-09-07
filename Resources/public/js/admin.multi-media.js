jQuery(document).ready(function() {
    /**
     * Maj d'un champ de média multiple
     *
     * @param  {Object} select Selecteur jQuery vers le select contenant la valeur
     */
    var updateMediaFields = function(select) {
        var type = select.val();

        var container = select.closest('.sonata-ba-field').parent().closest('.sonata-ba-field');
        container.find('[data-type="'+ type +'"]').each(function() {
            $(this).closest('.form-group').show();
        });

        var otherType = type == 'image' ? 'youtube' : 'image';
        container.find('[data-type="'+ otherType +'"]').each(function() {
            $(this).closest('.form-group').hide();
        });
    };

    jQuery('[data-type="multi-media-selector"]').each(function() {
        updateMediaFields($(this));
    });

    $('body').on('change', '[data-type="multi-media-selector"]', function() {
        updateMediaFields($(this));
    });

    // En cas d'utilisation avec sonata collection
    jQuery('.sonata-collection-add').on('sonata-collection-item-added', function() {
        jQuery('[data-type="multi-media-selector"]').each(function() {
            updateMediaFields($(this));
        });
    });
});
