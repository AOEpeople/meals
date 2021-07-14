// include CSS
import 'jquery-datetimepicker/build/jquery.datetimepicker.min.css'
import '@fancyapps/fancybox/dist/jquery.fancybox.css'
import '../sass/mealz.scss'

// include vendors
import 'jquery';
import 'jquery-datetimepicker/build/jquery.datetimepicker.full';
import '@fancyapps/fancybox';
import 'easy-autocomplete';
import 'daterangepicker';

function importAll(r) {
    r.keys().forEach(r);
}

window.Mealz = function () {
    this.prototypeFormId = undefined;
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.hiddenClass = 'hidden';
    this.weekCheckbox = $('.meal-form .week-disable input[type="checkbox"]')[0];
    this.$weekDayCheckboxes = $('.meal-form .week-day-action input[type="checkbox"]');
    this.$participationCheckboxes = $('.meals-list input.checkbox, .meals-list input[type = "checkbox"]');
    this.$guestParticipationCheckboxes = $('.meal-guests input.checkbox, .meal-guests input[type = "checkbox"]');
    this.$iconCells = $('.icon-cell');
    this.selectWrapperClass = 'select-wrapper';
    this.mealRowsWrapperClassSelector = '.meal-rows-wrapper';
    this.$selects = $("select");
    this.$body = $('body');
    this.$editParticipationEventListener = undefined;
    this.$profileAdd = $('.profile-list a[class="button small"]');
};

importAll(require.context('./modules/', true, /\.js$/));

$(document).ready(function () {
    var mealz = new Mealz();
    mealz.styleCheckboxes();
    mealz.styleSelects();
    mealz.initButtonHandling();
    mealz.copyToClipboard();

    /**
     * See: https://stackoverflow.com/questions/1537032/how-do-i-stop-jquery-appending-a-unique-id-to-scripts-called-via-ajax
     * http://api.jquery.com/jQuery.ajaxPrefilter/
     */
    $.ajaxPrefilter('script', function(options) {
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

    // prepare checkboxes on guest invitation form
    mealz.$guestParticipationCheckboxes.each(function (idx, checkbox) {
        var $checkbox = $(checkbox);
        var $participantsCount = $checkbox.parents('.action').parent().find('.participants-count');
        var actualCount = parseInt($participantsCount.find('span').html());
        mealz.applyCheckboxClasses($checkbox);
        $participantsCount.find('span').text($checkbox.is(':checked') ? actualCount + 1 : actualCount);
    });

    /**
     * Lightbox
     */
    mealz.enableLightbox();

    if($('.edit-participation').length > 0) {
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
     * If there are any meals in the list, run updateOffers to check for available offers.
     */
    if($('.meals-list').length > 0) {
        mealz.updateOffers();
    }

    /**
     * if meals is limited it should be displayed
     */
    $('.participation-limit').each(function(){
        if($(this).val().length > 0 && $(this).val() > 0){
            $(this).closest('.day').children('.limit-icon').addClass('modified');
        }
    });

    /**
     * datetimepicker
     */
    $('.calendar-icon').each(function(i){
        var thisDay = $('#week_form_days_'+i+'_lockParticipationDateTime');
        $(this).datetimepicker({
            format:'Y-m-d H:i:s',
            inline:false,
            defaultTime:new Date(thisDay.val()),
            defaultDate:new Date(thisDay.val()),
            onClose:function(dp,$input){
                if($input.val().length > 0){
                    thisDay.val($input.val());
                }
            }
        });
    });
    if($('.language-switch > span').text() == 'de'){
        $.datetimepicker.setLocale('de');
    }

    /*
     * MouseOver hack
     */
    (function($){
        $.mlp = {x:0,y:0}; // Mouse Last Position
        function documentHandler(){
            var $current = this === document ? $(this) : $(this).contents();
            $current.mousemove(function(e){jQuery.mlp = {x:e.pageX,y:e.pageY};});
            $current.find("iframe").load(documentHandler);
        }
        $(documentHandler);
        $.fn.ismouseover = function(overThis) {
            var result = false;
            this.eq(0).each(function() {
                var $current = $(this).is("iframe") ? $(this).contents().find("body") : $(this);
                var offset = $current.offset();
                result =    offset.left<=$.mlp.x && offset.left + $current.outerWidth() > $.mlp.x &&
                    offset.top<=$.mlp.y && offset.top + $current.outerHeight() > $.mlp.y;
            });
            return result;
        };
    })(jQuery);
});
