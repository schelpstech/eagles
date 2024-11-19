
$(document).ready(function () {
    $('.registration-form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: './assets/php/formhandler.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    // Show success message
                    $('.form-messages').html('<span style="color: green;">' + response.message + '</span>');
                    $('.registration-form')[0].reset(); // Reset form fields
                } else {
                    // Show error message
                    $('.form-messages').html('<span style="color: red;">' + response.message + '</span>');
                }
            },
            error: function () {
                $('.form-messages').html('<span style="color: red;">An unexpected error occurred. Please try again later.</span>');
            }
        });
    });
});