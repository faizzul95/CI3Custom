<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="base_url" content="<?php echo base_url() ?>" />
	<title> System | <?= $title; ?> </title>
	<script src="<?php echo base_url('public/custom/js/jquery.min.js') ?>"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<!-- Custom JS (Fahmy Izwan) -->
	<script src="<?php echo base_url('public/custom/js/helper.js') ?>"></script>
</head>

<body style="background-color: #ececec;">

	<div class="container-fluid">
		<?php echo $data; ?>
		<br>
	</div>

</body>

</html>