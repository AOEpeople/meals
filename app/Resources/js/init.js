$(document).ready(function() {
    $('#participant_participant').chosen();

    $('.btn-participation').click(function(e){
        e.preventDefault();
        var btnParticipation = $(this);
        $.get(btnParticipation.attr('href'), null, function(data){
            btnParticipation.blur();
            btnParticipation.addClass(data.btnAddClass);
            btnParticipation.removeClass(data.btnRemoveClass);
            btnParticipation.attr('href', data.btnUrl);
            btnParticipation.text(data.btnText);
        });
    });
});
