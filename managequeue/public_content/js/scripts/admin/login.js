$(document).ready(function() {
    var resend_vc_attemtps = 0;

    $('#mobile_number').change(function () {
        $(this).removeClass('invalid-input');
        $(this).removeClass('is-invalid');
    });

    (function() {
        'use strict';
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var get_admin_login_forms = document.getElementsByClassName('admin_login_token');
        // Loop over them and prevent submission
        Array.prototype.filter.call(get_admin_login_forms, function(get_admin_login_form) {

            get_admin_login_form.addEventListener('submit', function(event) {

                if (get_admin_login_form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }else{

                    if($('#mobile_number').val().length !== 10)
                    {
                        $('#mobile_number').addClass('invalid-input');
                        $('#mobile_number').addClass('is-invalid');

                        event.preventDefault();
                        event.stopPropagation();
                    }else{
                        $('#mobile_number').removeClass('is-invalid');
                        $('#mobile_number').removeClass('invalid-input');

                        event.preventDefault();
                        $.ajax({
                            url: '/admin/login',
                            method: 'POST',
                            data: {
                                csrf_manage_queue : $('#admin_login_csrf_value').val(),
                                mobile_number: $('#mobile_number').val()
                            },
                            beforeSend: function(){
                                $("#mobile_number").prop('disabled', true);
                                $("#admin_send_verification_code").prop('disabled', true);
                            },
                            success: function (data, status, jqXHR) {
                                var json_parsed_data = JSON.parse(data);

                                var alert_class = json_parsed_data.status ? 'alert-success' : 'alert-danger';

                                $('#alert-box').html('<div class="alert '+alert_class+'" role="alert">'+json_parsed_data.message+'</div>');

                                if(!json_parsed_data.status)
                                {
                                    $("#mobile_number").prop('disabled', false);
                                    $("#admin_send_verification_code").prop('disabled', false);
                                    $('#admin_login_csrf_value').val(json_parsed_data.csrf_hash);
                                }
                                else
                                {
                                    $('#verify_number_csrf_value').val(json_parsed_data.csrf_hash);
                                    $('#rvc_csrf_value').val(json_parsed_data.csrf_hash);
                                    $('#officers_id').val(json_parsed_data.officers_id);
                                    $('#rvc_officers_id').val(json_parsed_data.officers_id);
                                    $('#admin_login_form').hide();
                                    $('#verify_admin_number_form').show();
                                    $('#resend_verification_code').show();
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                                $('#alert-box').html('<div class="alert alert-danger" role="alert">'+errorThrown+'</div>');
                            }
                        });
                    }
                }
                get_admin_login_form.classList.add('was-validated');
            }, false);

        });

        var verify_number_forms = document.getElementsByClassName('verify_number');

        Array.prototype.filter.call(verify_number_forms, function(verify_number_form) {
            verify_number_form.addEventListener('submit', function(event) {

                if (verify_number_form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }else{
                    event.preventDefault();

                    $.ajax({
                        url: '/admin/verify_mobile',
                        method: 'POST',
                        data: {
                            csrf_manage_queue: $('#verify_number_csrf_value').val(),
                            verification_code: $('#verification_code').val(),
                            officers_id: $('#officers_id').val()
                        },
                        beforeSend: function(){
                            $("#verification_code").prop('disabled', true);
                            $("#verify_mobile_no").prop('disabled', true);
                            $("#resend_verification_code").prop('disabled', true);
                        },
                        success: function (data, status, jqXHR) {
                            var json_parsed_data = JSON.parse(data);
                            var alert_class = json_parsed_data.status ? 'alert-success' : 'alert-danger';
                            $('#alert-box').html('<div class="alert '+alert_class+'" role="alert">'+json_parsed_data.message+'</div>');

                            if(!json_parsed_data.status)
                            {
                                $("#verify_number_csrf_value").val(json_parsed_data.csrf_hash);
                                $("#verification_code").prop('disabled', false);
                                $("#verify_mobile_no").prop('disabled', false);
                                $("#resend_verification_code").prop('disabled', false);
                            }else
                            {
                                window.location.replace('/admin/index');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown){
                            $('#alert-box').html('<div class="alert alert-danger" role="alert">'+errorThrown+'</div>');
                        }
                    });
                }
                verify_number_form.classList.add('was-validated');
            }, false);
        });

        var resend_verification_code_forms = document.getElementsByClassName('resend_verification_code');

        Array.prototype.filter.call(resend_verification_code_forms, function(resend_verification_code_form) {
            resend_verification_code_form.addEventListener('submit', function(event) {

                if (resend_verification_code_form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }else{
                    event.preventDefault();

                    if(resend_vc_attemtps > 1)
                    {
                        $('#alert-box').html('<div class="alert alert-warning" role="alert">Too many resend verification code requests. Please check the number</div>');
                        $('#verify_admin_number_form').hide();
                        $('#resend_verification_code').hide();
                        $('#admin_login_form').show();
                        resend_vc_attemtps = 0;
                        $("#admin_send_verification_code").prop('disabled', false);
                        $("#mobile_number").prop('disabled', false);
                        $("#get_token_csrf_value").val($("#verify_number_csrf_value").val());
                    }
                    else
                    {
                        resend_vc_attemtps++;
                        $.ajax({
                            url: '/admin/login',
                            method: 'POST',
                            data: {
                                csrf_manage_queue: $('#rvc_csrf_value').val(),
                                officers_id: $('#rvc_officers_id').val()
                            },
                            beforeSend: function(){
                                $("#resend_vc").prop('disabled', true);
                            },
                            success: function (data, status, jqXHR) {

                                var json_parsed_data = JSON.parse(data);
                                var alert_class = json_parsed_data.status ? 'alert-success' : 'alert-danger';
                                $('#alert-box').html('<div class="alert '+alert_class+'" role="alert">'+json_parsed_data.message+'</div>');

                                $("#verify_number_csrf_value").val(json_parsed_data.csrf_hash);
                                $("#rvc_csrf_value").val(json_parsed_data.csrf_hash);
                                $("#resend_vc").prop('disabled', false);
                            },
                            error: function(jqXHR, textStatus, errorThrown){
                                $('#alert-box').html('<div class="alert alert-danger" role="alert">'+errorThrown+'</div>');
                            }
                        });
                    }
                }

                resend_verification_code_form.classList.add('was-validated');
            }, false);
        });
    })();
});