<!DOCTYPE html>
<html lang="en">
<title> <?= $title ?> | <?= env('APP_NAME'); ?> </title>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
	<link rel="icon" type="image/png" sizes="20x20" href="public/images/favicon.png">
	<style>
		html,
		body {
			padding: 0;
			margin: 0;
			width: 100%;
			height: 100%;
		}

		* {
			box-sizing: border-box;
		}

		body {
			text-align: center;
			padding: 0;
			/* background: #d6433b; */
			background: #405189;
			color: #fff;
			font-family: Open Sans;
		}

		h1 {
			font-size: 50px;
			font-weight: 100;
			text-align: center;
		}

		body {
			font-family: Open Sans;
			font-weight: 100;
			font-size: 20px;
			color: #fff;
			text-align: center;
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-pack: center;
			-ms-flex-pack: center;
			justify-content: center;
			-webkit-box-align: center;
			-ms-flex-align: center;
			align-items: center;
		}

		article {
			display: block;
			width: 700px;
			padding: 50px;
			margin: 0 auto;
		}

		a {
			color: #fff;
			font-weight: bold;
		}

		a:hover {
			text-decoration: none;
		}

		svg {
			width: 75px;
			margin-top: 1em;
		}
	</style>
</head>

<body>
	<article>
		<img src="<?= $image ?>" width="80%">
		<h1>Ops, <?= $title ?>!</h1>
		<div>
			<?= $message ?>
		</div>
	</article>
</body>