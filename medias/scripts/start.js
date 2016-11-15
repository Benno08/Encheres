$(document).ready(function() {
    $('.joueurselector').click(function() {
        $('.joueurselector').removeClass('selected');
        $(this).addClass('selected');

        $('input[name=joueurId]').val($(this).data('id'));
        $('.button').removeClass('disabled');
    });

    $('.button').click(function() {
        if(!$(this).hasClass('disabled'))
            $(this).parents('form').first().submit();
    });
});