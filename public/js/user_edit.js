$(document).ready(function(){

    $('#btn-password').on('click', function() {
        if ($('#container-password').hasClass('d-none')) {
            $('#container-password').removeClass('d-none');
        } else {
            $('#container-password').addClass('d-none');
        }
    })
});
