<?php

if (isset($_POST['fileName'])) {
	$filename = $_POST['fileName'];
	$data = isset($_POST['dataArray']) ? $_POST['dataArray'] : [];
	$filePath = "../../../application/views/$filename";

	if (file_exists($filePath)) {
		$opts = array(
			'http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'content' => (!empty($data)) ? http_build_query($data) : NULL
			)
		);

		$context  = stream_context_create($opts);
		echo file_get_contents($filePath, false, $context);
	} else {
		// echo "File does not exist.";
		echo '<div class="alert alert-danger" role="alert">
                File <b><i>' . $filePath . '</i></b> does not exist.
               </div>';
	}
}

?>