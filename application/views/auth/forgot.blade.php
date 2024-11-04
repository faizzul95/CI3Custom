<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="base_url" content="<?php echo base_url() ?>" />
	<title> Forgot Password Page </title>

	<!-- Bootstrap -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

	<!-- Custom JS (Fahmy Izwan) -->
	<script src="<?php echo base_url('public/custom/js/jquery.min.js') ?>"></script>
	<script src="<?php echo base_url('public/custom/js/axios.min.js') ?>"></script>
	<script src="<?php echo base_url('public/custom/js/js-cookie.js') ?>"></script>
	<script src="<?php echo base_url('public/custom/js/helper.js') ?>"></script>
	<script src="<?php echo base_url('public/custom/js/validationJS.js') ?>"></script>

	<script src="<?php echo base_url('public/custom/js/toastr.min.js') ?>"></script>
	<link href="<?php echo base_url('public/custom/css/toastr.min.css', null, false) ?>" rel="stylesheet" type="text/css" />

	<!-- google -->
	<!-- <script src="https://apis.google.com/js/platform.js" async defer></script> -->
	<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>
</head>

<body>

	<div class="container d-flex justify-content-center align-items-center vh-100">
		<div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
			<h2 class="card-title text-center mb-4"> Basic Reset Password</h2>
			<form id="formSentResetPassword" method="post">
				<div class="mb-3">
					<label for="email" class="form-label"> Email </label>
					<input type="email" class="form-control" id="email" name="email" autocomplete="off" required>
					<div class="text-end mt-2">
						<a href="<?= url('') ?>" class="text-decoration-none">Remember Password?</a>
					</div>
				</div>
				<div class="d-grid gap-2">
					<?= recaptchaInputDiv() ?>
					<button type="submit" id="resetBtn" class="btn btn-primary"> Sent Reset Link </button>
				</div>
			</form>
		</div>
	</div>

	<script type="text/javascript">

		var onloadCallback = function() {
			grecaptcha.execute();
		};

		function setResponse(response) {
			document.getElementById('captcha-response').value = response;
		}

		$("#formSentResetPassword").submit(async function(event) {
			event.preventDefault();
			var email = $('#email').val();

			if (validateDataReset()) {
				const submitBtnText = $('#resetBtn').html();
				loadingBtn('resetBtn', true, submitBtnText);

				const res = await callApi('post', 'auth/sent-mail-forgot', {
					'email': trimData(email)
				});

				if (isSuccess(res)) {
					noti(200, res.data.message);
					$('#email').val(''); // reset field
				}

				loadingBtn('resetBtn', false, submitBtnText);
			} else {
				validationJsError('toastr', 'multi'); // single or multi
			}

			grecaptcha.reset();
			onloadCallback();
		});

		function validateDataReset() {
			const rules = {
				'email': 'required|email|min:5|max:255'
			};

			return validationJs(rules);
		}
	</script>

</body>

</html>