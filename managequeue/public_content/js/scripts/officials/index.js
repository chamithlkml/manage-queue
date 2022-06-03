$(document).ready(function() {

    (function() {

        $("#date_to_office").datepicker({
            format: 'yyyy/mm/dd',
            startDate: '0d',
            autoclose: true,
            daysOfWeekDisabled: '[0,6]'
        });

        'use strict';
        // window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var search_token_forms = document.getElementsByClassName('search_token_form');
            // Loop over them and prevent submission
            Array.prototype.filter.call(search_token_forms, function(search_token_form) {

                search_token_form.addEventListener('submit', function(event) {

                    if (search_token_form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    search_token_form.classList.add('was-validated');
                }, false);

            });
        // }, false);
    })();

});
