// include CSS
import 'daterangepicker/daterangepicker.css'
import 'jquery-datetimepicker/build/jquery.datetimepicker.min.css'
import 'jquery-ui/themes/base/all.css'
import '@fancyapps/fancybox/dist/jquery.fancybox.css'
import '../sass/mealz.scss'

// include vendors
import 'jquery';
import 'jquery-migrate';
import 'jquery-datetimepicker/build/jquery.datetimepicker.full';
import '@fancyapps/fancybox';
import 'easy-autocomplete';
import 'daterangepicker';
import {Controller} from "./controller";
import {
    ParticipationCountUpdateHandler, ParticipationGuestCountUpdateHandler
} from "./modules/participation-count-update-handler";
import {ParticipationGuestToggleHandler, ParticipationToggleHandler} from "./modules/participation-toggle-handler";
import {ParticipationPreToggleHandler} from "./modules/participation-pre-toggle-handler";
import {UpdateOffersHandler} from "./modules/update-offers-handler";

if (process.env.MODE === 'production') {
    jQuery.migrateMute = true;
} else if (import.meta.webpackHot) {
    import.meta.webpackHot.accept();
}

function importAll(r) {
    r.keys().forEach(r);
}

window.Mealz = function () {
    this.prototypeFormId = undefined;
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.hiddenClass = 'hidden';
    this.weekCheckbox = $('.meal-form .week-disable input[type="checkbox"]')[0];
    this.$weekDayCheckboxes = $('.meal-form .week-day-action input[type="checkbox"]');
    this.participationToggleHandler = undefined;
    this.participationPreToggleHandler = undefined;
    this.updateOffersHandler = undefined;
    this.participationCountUpdateHandler = undefined;
    this.$participationCheckboxes = $('.meals-list input.checkbox, .meals-list input[type = "checkbox"]');
    this.$guestParticipationCheckboxes = $('.meal-guests input.checkbox, .meal-guests input[type = "checkbox"]');
    this.$iconCells = $('.icon-cell');
    this.selectWrapperClass = 'select-wrapper';
    this.mealRowsWrapperClassSelector = '.meal-rows-wrapper';
    this.$selects = $('select');
    this.$body = $('body');
    this.$editParticipationEventListener = undefined;
    this.$profileAdd = $('.profile-list a[class="button small"]');
};

importAll(require.context('./modules/', true, /\.js$/));

$(function () {
    const view = $('body').data('view');
    new Controller(view);

    var mealz = new Mealz();
    mealz.styleCheckboxes();
    mealz.styleSelects();
    mealz.initButtonHandling();
    mealz.copyToClipboard();

    mealz.confirmAction(
        '.hide-user-action',
        'data-hide-user-confirmation',
        '#hide-user-confirmation-continue'
    );
    mealz.initHiddenUsersToggler();

    /**
     * See: https://stackoverflow.com/questions/1537032/how-do-i-stop-jquery-appending-a-unique-id-to-scripts-called-via-ajax
     * http://api.jquery.com/jQuery.ajaxPrefilter/
     */
    $.ajaxPrefilter('script', function (options) {
        options.cache = true;
    });

    /**
     * Week creation, dish and variations selection
     */
    mealz.initDishSelection();

    /**
     * Mobile navigation button
     */
    $('.hamburger').on('click', function () {
        $(this).toggleClass('is-active');
        $('.header-content').toggleClass('is-open');
    });

    /**
     * Ajax form handling
     */
    mealz.initAjaxForms();

    /**
     * Enable table sorting
     */
    mealz.enableSortableTables();

    if (undefined === mealz.participationToggleHandler) {
        if (mealz.$participationCheckboxes.length > 0) {
            mealz.participationToggleHandler = new ParticipationToggleHandler(mealz.$participationCheckboxes);
            mealz.participationCountUpdateHandler = new ParticipationCountUpdateHandler(mealz.$participationCheckboxes);
            mealz.updateOffersHandler = new UpdateOffersHandler();
        } else if (mealz.$guestParticipationCheckboxes.length > 0) {
            mealz.participationToggleHandler = new ParticipationGuestToggleHandler(mealz.$guestParticipationCheckboxes);
            mealz.participationCountUpdateHandler = new ParticipationGuestCountUpdateHandler(mealz.$guestParticipationCheckboxes);
        }

        this.participationPreToggleHandler = new ParticipationPreToggleHandler(mealz.participationToggleHandler);
    }

    /**
     * Lightbox
     */
    mealz.enableLightbox();

    if ($('.edit-participation').length > 0) {
        /**
         * Profile Selection on Participants View
         */
        mealz.initAutocomplete();
        mealz.showProfiles();

        /**
         * init toggle participation
         */
        mealz.initToggleParticipation();
    }

    mealz.exportTransactions();

    /**
     * if meals is limited it should be displayed
     */
    $('.participation-limit').each(function () {
        if ($(this).val().length > 0 && $(this).val() > 0) {
            $(this).closest('.day').children('.limit-icon').addClass('modified');
        }
    });

    /**
     * datetimepicker
     */
    $('.calendar-icon').each(function (i) {
        var thisDay = $('#week_form_days_' + i + '_lockParticipationDateTime');
        $(this).datetimepicker({
            format: 'Y-m-d H:i:s',
            inline: false,
            defaultTime: new Date(thisDay.val()),
            defaultDate: new Date(thisDay.val()),
            onClose: function (dp, $input) {
                if ($input.val().length > 0) {
                    thisDay.val($input.val());
                }
            }
        });
    });
    if ($('.language-switch > span').text() == 'de') {
        $.datetimepicker.setLocale('de');
    }

    /*
     * MouseOver hack
     */
    (function ($) {
        $.mlp = {x: 0, y: 0}; // Mouse Last Position
        function documentHandler() {
            var $current = this === document ? $(this) : $(this).contents();
            $current.on('mousemove', function (e) {
                jQuery.mlp = {x: e.pageX, y: e.pageY};
            });
            $current.find('iframe').on('load', documentHandler);
        }

        $(documentHandler);
        $.fn.ismouseover = function (overThis) {
            var result = false;
            this.eq(0).each(function () {
                var $current = $(this).is('iframe') ? $(this).contents().find('body') : $(this);
                var offset = $current.offset();
                result = offset.left <= $.mlp.x && offset.left + $current.outerWidth() > $.mlp.x &&
                    offset.top <= $.mlp.y && offset.top + $current.outerHeight() > $.mlp.y;
            });
            return result;
        };
    })(jQuery);
});