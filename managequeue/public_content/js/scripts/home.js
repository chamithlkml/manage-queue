/**
 * Created by chamith on 8/22/18.
 */
$(document).ready(function() {

    var resend_vc_attemtps = 0;

	$("#date_to_office").datepicker({
		format: 'yyyy/mm/dd',
		startDate: '0d',
        autoclose: true,
        // daysOfWeekDisabled: '[0,6]'
	});

	$('#mobile_number').change(function () {
        $(this).removeClass('invalid-input');
        $(this).removeClass('is-invalid');
    });

    (function() {
        'use strict';
		var get_token_forms = document.getElementsByClassName('get_token');
		// Loop over them and prevent submission
		Array.prototype.filter.call(get_token_forms, function(get_token_form) {

			get_token_form.addEventListener('submit', function(event) {

				if (get_token_form.checkValidity() === false) {
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
							url: '/token',
							method: 'POST',
							data: {
								csrf_manage_queue : $('#get_token_csrf_value').val(),
								name: $('#name').val(),
								mobile_number: $('#mobile_number').val(),
								date_to_office: $('#date_to_office').val(),
								purpose_id: $('#purpose_id').val()
							},
							beforeSend: function(){
								$("#name").prop('disabled', true);
								$("#mobile_number").prop('disabled', true);
								$("#date_to_office").prop('disabled', true);
								$("#send_verification_code").prop('disabled', true);
							},
							success: function (data, status, jqXHR) {
								var json_parsed_data = JSON.parse(data);

								var alert_class = json_parsed_data.status ? 'alert-success' : 'alert-danger';

								$('#alert-box').html('<div class="alert '+alert_class+'" role="alert">'+json_parsed_data.message+'</div>');

								if(!json_parsed_data.status)
								{
									$("#name").prop('disabled', false);
									$("#mobile_number").prop('disabled', false);
									$("#date_to_office").prop('disabled', false);
									$("#send_verification_code").prop('disabled', false);
									$('#get_token_csrf_value').val(json_parsed_data.csrf_hash);
								}
								else
								{
									if(json_parsed_data.queue_no_issued)
									{
										$('#get_token_form').hide();
										$('#verify_number_form').hide();
										$('#resend_verification_code').hide();
										$('#apply_token_btn').show();
                                        $('#reach_office_csrf_value').val(json_parsed_data.csrf_hash);
										$('#reach_office_tokens_id').val(json_parsed_data.tokens_id);
                                        $('#reach_office_form').show();
									}
									else
									{
										$('#verify_number_csrf_value').val(json_parsed_data.csrf_hash);
										$('#rvc_csrf_value').val(json_parsed_data.csrf_hash);
										$('#tokens_id').val(json_parsed_data.tokens_id);
										$('#rvc_tokens_id').val(json_parsed_data.tokens_id);
										$('#get_token_form').hide();
										$('#verify_number_form').show();
										$('#resend_verification_code').show();
									}
								}
							},
							error: function(jqXHR, textStatus, errorThrown){
								$('#alert-box').html('<div class="alert alert-danger" role="alert">'+errorThrown+'</div>');
							}
						});
					}
				}
				get_token_form.classList.add('was-validated');
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
						url: '/token/verify_mobile',
						method: 'POST',
						data: {
							csrf_manage_queue: $('#verify_number_csrf_value').val(),
							verification_code: $('#verification_code').val(),
							tokens_id: $('#tokens_id').val()
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
								$('#get_token_form').hide();
								$('#verify_number_form').hide();
								$('#resend_verification_code').hide();
								$('#apply_token_btn').show();
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
						$('#verify_number_form').hide();
						$('#resend_verification_code').hide();
						$('#get_token_form').show();
						resend_vc_attemtps = 0;
						$("#send_verification_code").prop('disabled', false);
						$("#mobile_number").prop('disabled', false);
						$("#get_token_csrf_value").val($("#verify_number_csrf_value").val());
					}
					else
					{
						resend_vc_attemtps++;
						$.ajax({
							url: '/token/resend_verification_code',
							method: 'POST',
							data: {
								csrf_manage_queue: $('#rvc_csrf_value').val(),
								tokens_id: $('#rvc_tokens_id').val()
							},
							beforeSend: function(){
								$("#resend_verification_code").prop('disabled', true);
							},
							success: function (data, status, jqXHR) {

								var json_parsed_data = JSON.parse(data);
								var alert_class = json_parsed_data.status ? 'alert-success' : 'alert-danger';
								$('#alert-box').html('<div class="alert '+alert_class+'" role="alert">'+json_parsed_data.message+'</div>');

								$("#verify_number_csrf_value").val(json_parsed_data.csrf_hash);
								$("#rvc_csrf_value").val(json_parsed_data.csrf_hash);
								$("#resend_verification_code").prop('disabled', false);
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
