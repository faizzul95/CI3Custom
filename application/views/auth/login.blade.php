<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title> Login | <?= env('APP_NAME') ?></title>
	<!--begin::Accessibility Meta Tags-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
	<meta name="color-scheme" content="light dark" />
	<meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
	<meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
	<meta name="base_url" content="<?php echo base_url() ?>" />

	<!--end::Accessibility Meta Tags-->
	<!--begin::Primary Meta Tags-->
	<meta name="title" content="Login | <?= env('APP_NAME') ?>" />
	<meta name="author" content="<?= env('COMPANY_NAME') ?>" />

	<!--end::Primary Meta Tags-->
	<!--begin::Accessibility Features-->
	<!-- Skip links will be dynamically added by accessibility.js -->
	<meta name="supported-color-schemes" content="light dark" />
	<link rel="preload" href="<?php echo base_url('public/adminlte4/css/adminlte.css') ?>" as="style" />
	<!--end::Accessibility Features-->
	<!--begin::Fonts-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print" onload="this.media='all'" />
	<!--end::Fonts-->
	<!--begin::Third Party Plugin(OverlayScrollbars)-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
	<!--end::Third Party Plugin(OverlayScrollbars)-->
	<!--begin::Third Party Plugin(Bootstrap Icons)-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
	<!--end::Third Party Plugin(Bootstrap Icons)-->
	<!--begin::Required Plugin(AdminLTE)-->
	<link rel="stylesheet" href="<?php echo base_url('public/adminlte4/css/adminlte.css') ?>" />
	<!--end::Required Plugin(AdminLTE)-->

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
<!--end::Head-->
<!--begin::Body-->

<body class="login-page bg-body-secondary">
	<div class="login-box">
		<div class="login-logo">
			<?= env('APP_NAME') ?>
		</div>
		<!-- /.login-logo -->
		<div class="card">
			<div class="card-body login-card-body">
				<p class="login-box-msg">Sign in to start your session</p>

				<form id="formAuthentication" method="post">
					<div class="input-group mb-3">
						<input id="username" name="username" type="text" autocomplete="off" class="form-control" placeholder="Username/Email" required />
						<div class="input-group-text"><span class="bi bi-envelope"></span></div>
					</div>
					<div class="input-group mb-3">
						<input id="password" name="password" type="password" autocomplete="off" class="form-control" placeholder="Password" required />
						<div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
					</div>
					<!--begin::Row-->
					<div class="row">
						<div class="col-8">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" value="" id="rememberme" name="rememberme" />
								<label class="form-check-label" for="rememberme"> Remember Me </label>
							</div>
						</div>
						<!-- /.col -->
						<div class="col-4">
							<div class="d-grid gap-2">
								<button type="submit" id="loginBtn" class="btn btn-primary">Sign In</button>
							</div>
						</div>
						<!-- /.col -->
					</div>
					<!--end::Row-->
					<?= recaptchaInputDiv() ?>
				</form>

				<div class="social-auth-links text-center mb-3 d-grid gap-2">
					<p>- OR -</p>
					<button type="button" class="btn btn-danger btn-icon waves-effect waves-light w-100 google-signin" onclick="googleLogin()" disabled>
						<i class="bi bi-google me-2"></i> &nbsp; Sign In with Google
					</button>
				</div>
				<!-- /.social-auth-links -->
				<p class="mb-1"><a href="<?= url('forgot-password') ?>">I forgot my password</a></p>
				<p class="mb-0">
					<a href="javascript:void(0);" class="text-center"> Register a new membership </a>
				</p>
			</div>
			<!-- /.login-card-body -->
		</div>
	</div>
	<!-- /.login-box -->
	<!--begin::Third Party Plugin(OverlayScrollbars)-->
	<script
		src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
		crossorigin="anonymous"></script>
	<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
	<script
		src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
		crossorigin="anonymous"></script>
	<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
		crossorigin="anonymous"></script>
	<!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
	<script src="<?php echo base_url('public/adminlte4/js/adminlte.js') ?>"></script>
	<!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
	<script>
		const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
		const Default = {
			scrollbarTheme: 'os-theme-light',
			scrollbarAutoHide: 'leave',
			scrollbarClickScroll: true,
		};
		document.addEventListener('DOMContentLoaded', function() {
			const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
			if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
				OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
					scrollbars: {
						theme: Default.scrollbarTheme,
						autoHide: Default.scrollbarAutoHide,
						clickScroll: Default.scrollbarClickScroll,
					},
				});
			}
		});
	</script>
	<!--end::OverlayScrollbars Configure-->
	<!--end::Script-->

	<script type="text/javascript">
		$(document).ready(function() {
			setTimeout(function() {
				googleLogin();
			}, 10);
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
							// noti(400, 'Google Auth cannot be initialize');
						}
					);
			});
		}

		function attachSignin(GoogleAuth, element) {
			GoogleAuth.attachClickHandler(element, {},
				async function(googleUser) {
						var profile = googleUser.getBasicProfile();
						var google_id_token = googleUser.getAuthResponse().id_token;

						const res = await callApi('post', 'auth/socialite', {
							'email': profile.getEmail()
						});

						if (isSuccess(res.status)) {
							if (res.data != null) {
								const resCode = parseInt(res.data.code);
								noti(resCode, res.data.message);

								if (isSuccess(resCode)) {
									setTimeout(function() {
										window.location.href = res.data.redirectUrl;
									}, 400);
								}
							} else {
								noti(400, 'Email not found or not registered!');
							}
						}
					},
					function(res) {
						if (res.error != 'popup_closed_by_user') {
							noti(400, "Login using google was unsuccessful");
						} else {
							console.log('error', res);
						}
					});
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
<!--end::Body-->

</html>