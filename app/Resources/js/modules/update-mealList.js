Mealz.prototype.initUpdateSwappableMeals = function () {
    window.setInterval(updateSwappableMeals, 500);
};


function updateSwappableMeals() {
    $.getJSON('/menu/meal/getSwappableMeals', function(data){
        $.each(data, function (mealId, value) {
            var mealWrapper = $("[data-id=" + mealId + "]");
            userCheckbox = mealWrapper.find('.checkbox-wrapper');

            //TODO for Raza: add fade animation to counter and case for pending participation (offer taken)

            //if there is an available offer and the user is not already participating
            if (value[0] === true && userCheckbox.attr('class') !== 'checkbox-wrapper checked') {
                mealWrapper.find("#participants-count").attr('class', 'participants-count offer-available'); //make participants counter green
                mealWrapper.find('.tooltiptext-availableMeal').attr('class', 'tooltiptext-availableMeal active'); //activate tooltip
                mealWrapper.find('.checkbox-wrapper.disabled').attr('class', 'checkbox-wrapper'); //enable checkbox wrapper
                mealWrapper.find('.participation-checkbox').removeAttr('disabled'); //enable checkbox
                mealWrapper.find('.participation-checkbox').attr('class', 'participation-checkbox acceptOffer-action'); //adapt checkbox class
                mealWrapper.find('.participation-checkbox').attr('value', '/menu/' + value[1] + '/' + value[2] + '/acceptOffer'); //change checkbox value
            //if there is no available offer and the user is not already participating
            } else if (value[0] === false && userCheckbox.attr('class') !== 'checkbox-wrapper checked') {
                //don't change meals that are disabled and checked (i.e. yesterday's meals)
                if (userCheckbox.attr('class') !== 'checkbox-wrapper checked disabled') {
                    mealWrapper.find("#participants-count").attr('class', 'participants-count'); //make participants counter grey
                    mealWrapper.find('.tooltiptext-availableMeal active').attr('class', 'tooltiptext-availableMeal'); //deactivate tooltip
                    mealWrapper.find('.checkbox-wrapper').attr('class', 'checkbox-wrapper disabled'); //disable checkbox wrapper
                    mealWrapper.find('.participation-checkbox').attr('disabled', 'disabled'); //disable checkbox
                }
            }
        });
    });
}