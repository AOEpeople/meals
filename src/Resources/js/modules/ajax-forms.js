Mealz.prototype.initAjaxForms = function () {
    var that = this;
    $('.load-ajax-form').on('click', function (e) {
        e.preventDefault();
        that.loadAjaxForm($(this));
    });

    $('.print-participations .meal-participation a').on('click', function (e) {
        e.preventDefault();
        that.toggleParticipationAdmin($(this));
    });

    $('.load-payment-form').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        that.loadAjaxFormPayment($(this));

        var thisParent = $(this).parent();
        var formsRendered = thisParent.children('form');

        // remove other Payment Forms opened
        if(formsRendered.length > 0) {
            $(document).mouseup(function(e){
                // if the target of the click isn't the container
                // nor a descendant of the container
                if (formsRendered.is(e.target) === false &&
                    formsRendered.has(e.target).length === 0) {
                        formsRendered.remove();
                }
            });
        }

        if ($('form[name="settleform"]').length >= 1) {
            Mealz.prototype.confirmAction(
                'a#settle-account',
                'data-account-settlement-confirmation',
                '#account-settlement-confirmation-continue'
            );
        }

        if ($('.load-payment-form').is("#ecash") === true && $('.paypal-button-container').length >= 1) {
            that.enablePaypal();
        }

    });
};

Mealz.prototype.loadAjaxForm = function ($element) {
    var that = this;

    var url = $element.attr('href');
    var animationDuration = 150;

    var $createForm = $('.create-form');
    var $editFormWrapper = $('.edit-form:visible');
    var $elementParentRow = $element.closest('.table-row');
    var $ajaxRow;

    if ($createForm.is(':visible')) {
        $createForm.slideUp(animationDuration);
        if ($element.hasClass('load-create-form')) {
            return;
        }
    } else if ($element.hasClass('load-create-form') && $element.hasClass('loaded')) {
        $createForm.slideDown(animationDuration);
        $editFormWrapper.find('form').slideUp(animationDuration, function () {
            $editFormWrapper.hide();
        });
        return;
    }

    if ($editFormWrapper.length > 0) {
        $ajaxRow = $elementParentRow.next('.table-row-form');

        var ajaxRowVisible = $ajaxRow.length > 0 && $ajaxRow.is(':visible');

        $editFormWrapper.find('form').slideUp(animationDuration, function () {
            $editFormWrapper.hide();
        });

        if (!ajaxRowVisible && $element.hasClass('load-edit-form') && $element.hasClass('loaded')) {
            $ajaxRow.show();
            $ajaxRow.find('form').slideDown(animationDuration);
            return;
        } else if (ajaxRowVisible) {
            return;
        }
    } else if ($element.hasClass('load-edit-form') && $element.hasClass('loaded')) {
        $ajaxRow = $elementParentRow.next('.table-row-form');
        $ajaxRow.show();
        $ajaxRow.find('form').slideDown(animationDuration);
        return;
    }

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            var $wrapperForm;

            if ($element.hasClass('load-create-form')) {
                $createForm.html(data);
                $createForm.slideDown(animationDuration);
                $wrapperForm = $createForm;
            } else {
                $wrapperForm = $(data).insertAfter($elementParentRow);
                $wrapperForm.find('form').slideDown(animationDuration);
            }

            // Style selects
            $wrapperForm.find('select')
                .wrap('<div class="' + that.selectWrapperClass + '"></div>')
                .parent().append('<span class="loader"></span>');

            $element.addClass('loaded');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

Mealz.prototype.loadAjaxFormPayment = function ($element) {
    var that = this;
    var url = $element.attr('href');
    var $elementParent = $element.parent();
    var $form = $elementParent.find('form');

    if ($form.length !== 0) {
        that.$iconCells.find('form').addClass(that.hiddenClass);
        $form.toggleClass(this.hiddenClass);
        if (!$form.hasClass(this.hiddenClass)) {
            $form.find('input[type=text]').focus();
        }
        return;
    }

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        async: false,
        success: function (data) {
            that.$iconCells.find('form').addClass(that.hiddenClass);
            $element.after(data);
            $form.find('input[type=text]').focus();
            $elementParent.children('form').on('click', function (e) {
                e.stopPropagation();
            });
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};
