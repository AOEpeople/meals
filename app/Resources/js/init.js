Array.prototype.remove = function () {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = $.inArray(what, this)) != -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

$(document).ready(function() {

    var participations = [];

    $('.participation-checkbox').click(function(e){
        var link = $(this).attr('value');
        if ($.inArray(link, participations) != -1) {
            participations.remove(link);
        } else {
            participations.push(link);
        }
    });

    $('.participation-submit').click(function(e){
        var i = 0;
        for (var arrayLength = participations.length; i < arrayLength; i++) {
            $.get(participations[i], null, function(){
                location.reload();
            });
        }
    });
});
