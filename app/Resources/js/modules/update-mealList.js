Mealz.prototype.initUpdateSwappableMeals = function () {
    window.setInterval(updateSwappableMeals, 5000);
};


function updateSwappableMeals() {
    $.getJSON('/menu/meal/getSwappableMeals', function (data) {
        $.each(data, function (mealId, value) {
            var mealWrapper = $("[data-id=" + mealId + "]");
            userCheckbox = mealWrapper.find('.checkbox-wrapper');
            participationCheckbox = mealWrapper.find('.participation-checkbox');

            //new offer available and not checkbox not checked yet
            if (value[0] === true && userCheckbox.attr('class') !== 'checkbox-wrapper checked') {
                //activate tooltip
                mealWrapper.find('.tooltiptext').attr('class', 'tooltiptext active');
                //enable checkbox wrapper
                mealWrapper.find('.checkbox-wrapper.disabled').attr('class', 'checkbox-wrapper');
                //enable checkbox
                participationCheckbox.removeAttr('disabled');
                //adapt checkbox class
                participationCheckbox.attr('class', 'participation-checkbox acceptOffer-action');
                //change checkbox value
                participationCheckbox.attr('value', '/menu/' + value[1] + '/' + value[2] + '/acceptOffer');
                //make participants counter green
                mealWrapper.find("#participants-count").attr('class', 'participants-count offer-available');
            }

            //no offer available (anymore)
            if (value[0] === false) {
                //make participants counter grey
                mealWrapper.find("#participants-count").attr('class', 'participants-count');
                //deactivate tooltip
                mealWrapper.find('.tooltiptext active').attr('class', 'tooltiptext');
                //disable the checkbox if it's a unswap-checkbox (this means this user was swapping and the offer was taken)
                mealWrapper.find('.participation-checkbox.unswap-action').parent().attr('class', 'checkbox-wrapper disabled');

                //disable checkboxes that are not checked
                if (userCheckbox.attr('class') !== 'checkbox-wrapper checked') {
                    //disable checkbox wrapper
                    mealWrapper.find('.checkbox-wrapper').attr('class', 'checkbox-wrapper disabled');
                    //disable checkbox
                    participationCheckbox.attr('disabled', 'disabled');
                }
            }
        });
    });
}