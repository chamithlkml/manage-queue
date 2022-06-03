$(document).ready(function() {

    (function() {

        var add_purpose_forms = document.getElementsByClassName('add_purpose_form');

        Array.prototype.filter.call(add_purpose_forms, function (add_purpose_form) {

            add_purpose_form.addEventListener('submit', function(event){

                if(add_purpose_form.checkValidity() === false)
                {
                    event.preventDefault();
                    event.stopPropagation();
                }
                add_purpose_form.classList.add('was-validated');
            });
        })

    })();


});
