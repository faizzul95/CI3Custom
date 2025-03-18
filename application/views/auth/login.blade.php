<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="base_url" content="<?php echo base_url() ?>" />
	<title> Login Page </title>

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
	<script src="https://apis.google.com/js/platform.js" async defer></script>
	<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>

</head>

<body>

	<div class="container d-flex justify-content-center align-items-center vh-100">
		<div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
			<h2 class="card-title text-center mb-2"> Basic Login </h2>
			<em class="mb-1 text-center">Welcome to {{ env('APP_NAME') }}! 👋</em>
			<form id="formAuthentication" method="post">
				<div class="mb-3">
					<label for="username" class="form-label">Username</label>
					<input type="text" class="form-control" id="username" name="username" autocomplete="off" required>
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input type="password" class="form-control" id="password" name="password" autocomplete="off" required>
					<div class="text-end mt-2">
						<a href="<?= url('forgot-password') ?>" class="text-decoration-none">Forgot Password?</a>
					</div>
				</div>
				<div class="d-grid gap-2">
					<?= recaptchaInputDiv() ?>
					<button type="submit" id="loginBtn" class="btn btn-primary">Login</button>
					<button type="button" class="btn btn-danger btn-icon waves-effect waves-light w-100 google-signin" onclick="googleLogin()" disabled>
						<i class="ri-google-fill fs-16"></i> &nbsp; Sign In with Google
					</button>
				</div>
			</form>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout(function() {
				googleLogin();
			}, 15);
		});

		var onloadCallback = function() {
			grecaptcha.execute();
		};

		function setResponse(response) {
			document.getElementById('captcha-response').value = response;
		}

		function googleLogin() {

			var auth2;

			gapi.load('auth2', function() {

				var gapiConfig = JSON.parse('<?= gapiConfig() ?>');

				// Retrieve the singleton for the GoogleAuth library and set up the client.
				auth2 = gapi.auth2.init(gapiConfig)
					.then(
						//oninit
						function(GoogleAuth) {
							attachSignin(GoogleAuth, document.getElementsByClassName('google-signin')[0]);
							$('.google-signin').attr('disabled', false);
						},
						//onerror
						function(error) {
							console.log('error initialize', error);
							noti(500, 'Google Auth cannot be initialize');
						}
					);
			});
		}

		function attachSignin(GoogleAuth, element) {
			GoogleAuth.attachClickHandler(element, {},
				function(googleUser) {
					var profile = googleUser.getBasicProfile();
					var google_id_token = googleUser.getAuthResponse().id_token;
					loginGoogle(profile.getEmail());
				},
				function(res) {
					if (res.error != 'popup_closed_by_user') {
						noti(500, "Login using google was unsuccessful");
					} else {
						console.log('error', res);
					}
				});
		}

		async function loginGoogle(googleEmail) {

			const res = await callApi('post', 'auth/socialite', {
				'email': googleEmail
			});

			if (isSuccess(res.status)) {
				if (res.data != null) {
					const resCode = parseInt(res.data.code);
					noti(resCode, res.data.message);

					if (isSuccess(resCode)) {
						setTimeout(function() {
							window.location.href = res.data.redirectUrl;
						}, 500);
					}
				} else {
					noti(500, 'Email not found or not registered!');
				}
			}
		}

		$("#formAuthentication").submit(async function(event) {
			event.preventDefault();
			var username = $('#username').val();
			var password = $('#password').val();

			if (validateDataSignIn()) {
				var form = $(this);
				const res = await loginApi("auth/sign-in", form.serializeArray(), 'formAuthentication');
				if (isSuccess(res)) {
					const data = res.data;
					const resCode = parseInt(data.code);

					noti(resCode, data.message);

					if (isSuccess(resCode)) {
						setTimeout(function() {
							window.location.href = data.redirectUrl;
						}, 500);
					} else {
						$("#loginBtn").html('Login');
						$("#loginBtn").attr('disabled', false);
					}
				} else {
					$("#loginBtn").html('Login');
					$("#loginBtn").attr('disabled', false);
				}

			} else {
				validationJsError('toastr', 'multi'); // single or multi
			}

			grecaptcha.reset();
			onloadCallback();

		});

		function validateDataSignIn() {

			const rules = {
				'password': 'required|min:8|max:255',
				'username': 'required|min:2|max:255'
			};

			return validationJs(rules);
		}
	</script>

</body>

</html>