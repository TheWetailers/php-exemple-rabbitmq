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
                console.log(response, form);
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

    var callConsumer1 = function() {
        $.ajax({
            url: './consumer.php',
            dataType: 'json'
        }).done(function(response) {
            if (response.status == 'success') {
                $('.js-chat-1').append(
                    $('<li />')
                        .text(response.message)
                );
            }

            callConsumer1();
        });
    };

    callConsumer1();

    var callConsumer2 = function() {
        $.ajax({
            url: './consumer.php',
            dataType: 'json'
        }).done(function(response) {
            if (response.status == 'success') {
                $('.js-chat-2').append(
                    $('<li />')
                        .text(response.message)
                );
            }

            callConsumer2();
        });
    };

    callConsumer2();
});
