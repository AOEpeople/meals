var Mealz = function () {
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

$(document).ready(function () {
    var mealz = new Mealz();
    mealz.styleCheckboxes();
    mealz.styleSelects();
    mealz.initButtonHandling();
    mealz.copyToClipboard();

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
        var $participantsCount = $checkbox.closest('.meal-row').find('.participants-count');
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

    /**
     * datetimepicker
     */
    $('.calendar-icon').each(function(){
        $(this).datetimepicker({
            format:'d.m.Y H:i',
            inline:false,
            lang:'de',
            defaultTime:'16:00',
            onClose:function(dp,$input){
                console.log($input.val())
            }
        });
    });
});
