"use strict";

jQuery(function() {
    jQuery('.js-post').on('submit', function(e) {
        $.ajax({
            url: './publisher.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json'
        }).done(
            (function(form, response) {
                if (response.status == 'error') {
                    alert(response.message);
                } else {
                    this.reset();
                }
            }).bind(this)
        ).fail(function() {
            alert('Technical error');
        });

        e.preventDefault();
    });

    var callConsumer = function() {
        $.ajax({
            url: './consumer.php',
            dataType: 'json'
        }).done(function(response) {
            if (response.status == 'success') {
                $('.js-chat').append(
                    $('<li />')
                        .text(response.message)
                );
            }

            callConsumer();
        });
    };

    callConsumer();
});
