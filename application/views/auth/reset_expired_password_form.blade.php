<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="base_url" content="<?php echo base_url() ?>" />
    <title> <?php echo $title; ?> </title>

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
            <h2 class="card-title text-center mb-4"> <?php echo $title; ?> </h2>
            <form id="formUpdatePassword" method="post">
                <div class="mb-3">
                    <label for="password" class="form-label"> New Password </label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="off" required>
                </div>
                <div class="mb-3">
                    <label for="retypenewpassword" class="form-label"> Re-Type New Password </label>
                    <input type="password" class="form-control" id="retypenewpassword" autocomplete="off" required>
                </div>
                <div class="d-grid gap-2">
                    <?= recaptchaInputDiv() ?>
                    <input type="hidden" class="form-control" id="id" name='id' autocomplete="off" value="<?php echo $user_id; ?>" required>
                    <button type="submit" id="updatePassBtn" class="btn btn-primary"> Update Password </button>
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

        $("#formUpdatePassword").submit(async function(event) {
            event.preventDefault();
            var id = $('#id').val();
            var password = $('#password').val();
            var retype = $('#retypenewpassword').val();

            if(retype != password){
                noti(400, 'Password not match');
                return false;
            }

            if (validateDataResetPass()) {
                const submitBtnText = $('#updatePassBtn').html();
                loadingBtn('updatePassBtn', true, submitBtnText);

                const res = await callApi('post', 'auth/password_reset_form', {
                    'id': trimData(id),
                    'password': trimData(password)
                });

                if (isSuccess(res)) {
                    noti(200, res.data.message);
                    $('#id').val(''); // reset field
                }

                loadingBtn('updatePassBtn', false, submitBtnText);
            } else {
                validationJsError('toastr', 'multi'); // single or multi
            }

            grecaptcha.reset();
			onloadCallback();
        });

        function validateDataResetPass() {
            const rules = {
                'id': 'required',
                'password': 'required|min:8|max:20',
                'retypenewpassword': 'required|min:8|max:20'
            };

            return validationJs(rules);
        }
    </script>

</body>

</html>