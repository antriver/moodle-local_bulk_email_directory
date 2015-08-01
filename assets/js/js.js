$(function(){

    $('#list-input').autocomplete({
        source: '/local/bulk_email_directory/ajax/autocomplete.php?type=list',
        minLength: 3,
        select: function(event, ui) {
            window.location = '?list=' + ui.item.value;
        }
    });

    $('#email-input').autocomplete({
        source: '/local/bulk_email_directory/ajax/autocomplete.php?type=email',
        minLength: 3,
        select: function(event, ui) {
            window.location = '?email=' + ui.item.value;
        }
    });

});
