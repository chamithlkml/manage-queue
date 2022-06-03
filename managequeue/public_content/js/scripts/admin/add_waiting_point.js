$(document).ready(function() {

    $("#date_of_waiting_point").datepicker({
        format: 'yyyy/mm/dd',
        startDate: '0d',
        autoclose: true,
        // daysOfWeekDisabled: '[0,6]'
    });

    //add_waiting_point_form
    (function() {
        'use strict';
        var add_waiting_point_forms = document.getElementsByClassName('add_waiting_point_form');

        Array.prototype.filter.call(add_waiting_point_forms, function(add_waiting_point_form){
            add_waiting_point_form.addEventListener('submit', function (event) {

                if(add_waiting_point_form.checkValidity() === false)
                {
                    event.preventDefault();
                    event.stopPropagation();
                }else{

                }

                add_waiting_point_form.classList.add('was-validated');
            })
        });

    })();
});