/**
 * Contains check login in settings page and ajax for forms pages.
 */
(function ($) {
    'use strict';

    window.eMailChef = {
        checkLogin: function (email, password, callback) {
            var data = {
                'action': 'emailchef_check_login',
                'email': email,
                'password': password
            };

            jQuery.post(ajaxurl, data, function (response) {
                callback(response.result);
            });
        }
    };

    $(document).ready(function () {
        var reloadActivatedWarning = function(that){
            // Disable warning not connected or enable if necessary
            if (!$('.list-id', that).val() || !$('.content-map select option[value="email"]:selected', that).length) {
                $('.accordion-section-content .content', that).parent().parent().removeClass('active');
            } else {
                $('.accordion-section-content .content', that).parent().parent().addClass('active');
            }
            reloadActivatedWarningChanging();
        };
        var reloadActivatedWarningChanging = function(that){
            if (!$('.list-id', that).val()) {
                $('.accordion-section-content .content', that).parent().parent().addClass('warning');
            } else {
                $('.accordion-section-content .content', that).parent().parent().removeClass('warning');
            }
            if(!$('.content-map select option[value="email"]:selected', that).length) {
                $('.accordion-section-content .content', that).parent().parent().addClass('warning-map');
            } else {
                $('.accordion-section-content .content', that).parent().parent().removeClass('warning-map');
            }
        };
        var reloadAccordionActivated = function (that, data, response) {
            $('.accordion-section-content .loading', that).hide();
            $('.accordion-section-content .content', that).show();
            $('.accordion-section-content .content', that).html(response);

            reloadActivatedWarning(that);

            if (!$('.list-id', that).val()) {
                $('.map-reload', that).show();
                $('.content-map', that).hide();
            }
            $('.list-id', that).change(function () {
                $('.map-reload', that).show();
                $('.content-map', that).hide();
            });

            $('.list-id,.content-map select', that).change(function () {
                reloadActivatedWarningChanging(that);
            });

            $('.reset', that).click(function (e) {
                e.preventDefault();
                reloadAccordion(that);
            });
            $('.create', that).click(function (e) {
                e.preventDefault();
                $('.accordion-section-content .loading', that).show();
                $('.accordion-section-content .content', that).hide();
                $.post(ajaxurl, $.extend(data, {
                    'create': 1,
                    'data': $('form', that).serialize()
                }), function (response) {
                    reloadAccordionActivated(that, data, response);
                });
            });
            $('form', that).submit(function (e) {
                e.preventDefault();
                $('.accordion-section-content .loading', that).show();
                $('.accordion-section-content .content', that).hide();
                $.post(ajaxurl, $.extend(data, {'data': $('form', that).serialize()}), function (response) {
                    reloadAccordionActivated(that, data, response);
                });
            });
        };
        var reloadAccordion = function (that) {
            $('.accordion-section-content .loading', that).show();
            $('.accordion-section-content .content', that).hide();

            var data = {
                'action': 'emailchef_forms_form',
                'id': $(that).data('id'),
                'driver': $(that).data('driver')
            };

            jQuery.post(ajaxurl, data, function (response) {
                reloadAccordionActivated(that, data, response);
            }).fail(function(){
				window.location.href = urlToSettingsPage;
			});
        };
        $('.emailchef-form .accordion-section').each(function () {
            var that = $(this);
            $('.accordion-section-title', that).click(function (e) {
                reloadAccordion(that);
            });
        });


    });


})(jQuery);


/**
 * Accordion-folding functionality.
 *
 * Markup with the appropriate classes will be automatically hidden,
 * with one section opening at a time when its title is clicked.
 * Use the following markup structure for accordion behavior:
 *
 * <div class="accordion-container">
 *    <div class="accordion-section open">
 *        <h3 class="accordion-section-title"></h3>
 *        <div class="accordion-section-content">
 *        </div>
 *    </div>
 *    <div class="accordion-section">
 *        <h3 class="accordion-section-title"></h3>
 *        <div class="accordion-section-content">
 *        </div>
 *    </div>
 *    <div class="accordion-section">
 *        <h3 class="accordion-section-title"></h3>
 *        <div class="accordion-section-content">
 *        </div>
 *    </div>
 * </div>
 *
 * Note that any appropriate tags may be used, as long as the above classes are present.
 *
 * @since 3.6.0.
 */

(function ($) {

    $(document).ready(function () {

        // Expand/Collapse accordion sections on click.
        $('.accordion-container').on('click keydown', '.accordion-section-title', function (e) {
            if (e.type === 'keydown' && 13 !== e.which) { // "return" key
                return;
            }

            e.preventDefault(); // Keep this AFTER the key filter above

            accordionSwitch($(this));
        });

    });

    /**
     * Close the current accordion section and open a new one.
     *
     * @param {Object} el Title element of the accordion section to toggle.
     * @since 3.6.0
     */
    function accordionSwitch(el) {
        var section = el.closest('.accordion-section'),
            sectionToggleControl = section.find('[aria-expanded]').first(),
            container = section.closest('.accordion-container'),
            siblings = container.find('.open'),
            siblingsToggleControl = siblings.find('[aria-expanded]').first(),
            content = section.find('.accordion-section-content');

        // This section has no content and cannot be expanded.
        if (section.hasClass('cannot-expand')) {
            return;
        }

        // Add a class to the container to let us know something is happening inside.
        // This helps in cases such as hiding a scrollbar while animations are executing.
        container.addClass('opening');

        if (section.hasClass('open')) {
            section.toggleClass('open');
            content.toggle(true).slideToggle(150);
        } else {
            siblingsToggleControl.attr('aria-expanded', 'false');
            siblings.removeClass('open');
            siblings.find('.accordion-section-content').show().slideUp(150);
            content.toggle(false).slideToggle(150);
            section.toggleClass('open');
        }

        // We have to wait for the animations to finish
        setTimeout(function () {
            container.removeClass('opening');
        }, 150);

        // If there's an element with an aria-expanded attribute, assume it's a toggle control and toggle the aria-expanded value.
        if (sectionToggleControl) {
            sectionToggleControl.attr('aria-expanded', String(sectionToggleControl.attr('aria-expanded') === 'false'));
        }
    }

})(jQuery);
