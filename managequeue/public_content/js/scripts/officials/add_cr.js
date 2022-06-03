$(document).ready(function(){
    (function () {
       'use strict';
       var add_cr_forms = document.getElementsByClassName('officer_add_cr');

        Array.prototype.filter.call(add_cr_forms, function(add_cr_form) {
            add_cr_form.addEventListener('submit', function(event) {

                if (add_cr_form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }else{

                }

                add_cr_form.classList.add('was-validated');
            }, false);
        });

    })();
});