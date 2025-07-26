<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title> Forgot Password | <?= env('APP_NAME') ?></title>
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
				<p class="login-box-msg">Reset Email</p>

				<form id="formSentResetPassword" method="post">
					<div class="input-group mb-3">
						<input id="email" name="email" type="email" autocomplete="off" class="form-control" placeholder="Email" required />
						<div class="input-group-text"><span class="bi bi-envelope"></span></div>
					</div>
					<!--begin::Row-->
					<div class="row">
						<div class="col-8">

						</div>
						<!-- /.col -->
						<div class="col-4">
							<div class="d-grid gap-2">
								<button type="submit" id="resetBtn" class="btn btn-primary"> Sent </button>
							</div>
						</div>
						<!-- /.col -->
					</div>
					<!--end::Row-->
					<?= recaptchaInputDiv() ?>
				</form>

				<!-- /.social-auth-links -->
				<p class="mb-1"><a href="<?= url('') ?>">Back to login</a></p>
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
<!--end::Body-->

</html>