<?php

function folder($foldername = 'directory', $folderid = NULL, $type = 'image')
{
	// $foldername = replaceFolderName($foldername);
	$type = replaceFolderName($type);

	if (empty($folderid)) {
		$folder = 'public/upload/' . $foldername . '/' . $type;
	} else {
		$folderid = replaceFolderName($folderid);
		$folder = 'public/upload/' . $foldername . '/' . $folderid . '/' . $type;
	}

	// check if folder current not exist, 
	// create one with permission (server) to upload
	if (!is_dir($folder)) {

		$old = umask(0);
		mkdir($folder, 0755, true);
		umask($old);

		chmod($folder, 0755);
	}

	return $folder;
}

function replaceFolderName($folderName)
{
	return str_replace(array('\'', '/', '"', ',', ';', '<', '>', '@', '|'), '_', preg_replace('/\s+/', '_', $folderName));
}

function get_mime_type($filename)
{
	$idx = pathinfo($filename, PATHINFO_EXTENSION);

	$mimet = array(
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',

		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',

		// archives
		'zip' => 'application/zip',
		'rar' => 'application/x-rar-compressed',
		'exe' => 'application/x-msdownload',
		'msi' => 'application/x-msdownload',
		'cab' => 'application/vnd.ms-cab-compressed',

		// audio/video
		'mp3' => 'audio/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',

		// adobe
		'pdf' => 'application/pdf',
		'psd' => 'image/vnd.adobe.photoshop',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',

		// ms office
		'doc' => 'application/msword',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'docx' => 'application/msword',
		'xlsx' => 'application/vnd.ms-excel',
		'pptx' => 'application/vnd.ms-powerpoint',


		// open office
		'odt' => 'application/vnd.oasis.opendocument.text',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
	);

	if (isset($mimet[$idx])) {
		return $mimet[$idx];
	} else {
		return 'application/octet-stream';
	}
}

function upload($files, $folder, $data = NULL, $index = false, $compress = false, $file_compression = 1)
{
	// Check PHP.ini settings
	$maxFileSize = min(
		convertPHPSizeToBytes(ini_get('upload_max_filesize')),
		convertPHPSizeToBytes(ini_get('post_max_size'))
	);

	// Check if folder exist.
	if (!is_dir($folder)) {
		mkdir($folder, 0755, TRUE);
	}

	// Handle the file based on index
	$fileTmpPath = ($index === false) ? $files['tmp_name'] : $files['tmp_name'][$index];
	$fileName = ($index === false) ? $files['name'] : $files['name'][$index];
	$fileSize = ($index === false) ? $files['size'] : $files['size'][$index];
	$fileError = ($index === false) ? $files['error'] : $files['error'][$index];

	// Check for PHP file upload errors
	if ($fileError !== UPLOAD_ERR_OK) {
		return [
			'status' => 400,
			'error' => 'File upload error: ' . fileUploadError($fileError)
		];
	}

	// Check file size limit
	if ($fileSize > $maxFileSize) {
		return [
			'status' => 400,
			'error' => 'File exceeds the maximum allowed size'
		];
	}

	$ext = pathinfo($fileName, PATHINFO_EXTENSION);
	$newName = md5($fileName) . date('dmYhis');
	$saveName = $newName . '.' . $ext;
	$path = $folder . '/' . $saveName;

	if (move_uploaded_file($fileTmpPath, $path)) {

		$entity_type = $entity_file_type = $entity_id = $user_id = 0;

		// Handle compression if required
		if ($compress) {
			$canCompress = ['jpg', 'png', 'jpeg', 'gif'];
			if (in_array($ext, $canCompress)) {
				$compressfolder = $folder . '/' . $newName . "_compress." . $ext;
				$thumbnailfolder = $folder . '/' . $newName . "_thumbnail." . $ext;

				if ($file_compression === 2) {
					compress($path, $compressfolder, '60');
				} elseif ($file_compression === 3) {
					compress($path, $compressfolder, '60');
					compress($path, $thumbnailfolder, '15');
				}

				if (file_exists($compressfolder)) {
					$fileSize += filesize($compressfolder);
				}

				if (file_exists($thumbnailfolder)) {
					$fileSize += filesize($thumbnailfolder);
				}
			}
		}

		if (!empty($data)) {
			$user_id = $data['user_id'] ?? NULL;
			$entity_type = $data['type'] ?? NULL;
			$entity_file_type = $data['file_type'] ?? NULL;
			$entity_id = $data['entity_id'] ?? NULL;
		}

		$filesMime = get_mime_type($fileName);
		$fileType = explodeArr($filesMime, '/', 0)[0];

		return [
			'status' => 200,
			'data' => [
				'files_name' => $saveName,
				'files_original_name' => $fileName,
				'files_folder' => $folder,
				'files_type' => $fileType,
				'files_mime' => $filesMime,
				'files_extension' => $ext,
				'files_size' => $fileSize,
				'files_compression' => $file_compression,
				'files_path' => $path,
				'files_path_is_url' => 0,
				'entity_type' => $entity_type,
				'entity_file_type' => $entity_file_type,
				'entity_id' => $entity_id,
				'user_id' => $user_id,
			]
		];
	}

	return [
		'status' => 400,
		'error' => 'File could not be moved to destination directory'
	];
}

function moveFile($filesName, $currentPath, $folder, $data = NULL, $type = 'rename', $compress = false, $file_compression = 1)
{
	$ext = pathinfo($filesName, PATHINFO_EXTENSION);
	$newName = md5($filesName) . date('dmYhis');
	$saveName = $newName . '.' . $ext;
	$path = $folder . '/' . $saveName;
	$fileSize = filesize($currentPath);

	if ($type($currentPath, $path)) {

		$entity_type = $entity_file_type = $entity_id = $user_id = 0;

		// 1 = full size only, 2 = full size & compressed, 3 = full size, compressed & thumbnail	
		if ($compress) {
			$canCompress = ['jpg', 'png', 'jpeg', 'gif'];
			if (in_array(pathinfo($saveName, PATHINFO_EXTENSION), $canCompress)) {
				$compressfolder = $folder . '/' . $newName . "_compress." . $ext;
				$thumbnailfolder = $folder . '/' . $newName . "_thumbnail." . $ext;

				if ($file_compression === 2) {
					$file_compression = 2;
					$compressImage = compress($path, $compressfolder, '60');
				} elseif ($file_compression === 3) {
					$file_compression = 3;
					$compressImage = compress($path, $compressfolder, '60');
					$thumbnailImage = compress($path, $thumbnailfolder, '15');
				}

				// adjustment for _compress
				if (file_exists($compressfolder))
					$fileSize = $fileSize + filesize($compressfolder);

				// adjustment for _thumbnail
				if (file_exists($thumbnailfolder))
					$fileSize = $fileSize + filesize($thumbnailfolder);
			}
		}

		if (!empty($data)) {
			$user_id = (isset($data['user_id'])) ? $data['user_id'] : NULL;
			$entity_type = (isset($data['type'])) ? $data['type'] : NULL;
			$entity_file_type = (isset($data['file_type'])) ? $data['file_type'] : 'PROFILE_PHOTO';
			$entity_id = (isset($data['entity_id'])) ? $data['entity_id'] : NULL;
		}

		$filesMime = get_mime_type($filesName);
		$fileType = explodeArr($filesMime, '/',  0);
		$fileType = $fileType[0];

		//Clear cache and check filesize again
		clearstatcache();

		return [
			'files_name' => $saveName,
			'files_original_name' => $filesName,
			'files_folder' => $folder,
			'files_type' => $fileType,
			'files_mime' => $filesMime,
			'files_extension' => $ext,
			'files_size' => round($fileSize, 2),
			'files_compression' => $file_compression,
			'files_path' => $path,
			'files_path_is_url' => 0,
			'entity_type' => $entity_type,
			'entity_file_type' => $entity_file_type,
			'entity_id' => $entity_id,
			'user_id' => $user_id,
		];
	}

	return [];
}

// Quality: quality is optional, and ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file),
function compress($source, $destination, $quality = '100')
{
	$info = getimagesize($source);
	if ($info['mime'] == 'image/jpeg')
		$image = imagecreatefromjpeg($source);
	elseif ($info['mime'] == 'image/gif')
		$image = imagecreatefromgif($source);
	elseif ($info['mime'] == 'image/png')
		$image = imagecreatefrompng($source);

	imagejpeg($image, $destination, $quality);

	return $destination;
}

// Compress on the go
function compressImageonthego($source, $quality)
{
	$info = getimagesize($source);
	$extension = explode(".", $source);

	$newname = "temp" . rand(10, 100);

	if ($info['mime'] == 'image/jpeg')
		$image = imagecreatefromjpeg($source);

	elseif ($info['mime'] == 'image/gif')
		$image = imagecreatefromgif($source);

	elseif ($info['mime'] == 'image/png')
		$image = imagecreatefrompng($source);

	imagejpeg($image, "images/" . $newname . "." . $extension[1], $quality);
	echo "<b>" . $newname . "." . $extension[1] . "</b>";
}

// Convert base64 string
function convertBase64String($base64String)
{
	list($type, $base64String) = explode(';', $base64String);
	list(, $base64String) = explode(',', $base64String);

	// Decode the base64-encoded data
	$decoded_data = base64_decode($base64String);

	// Check if the decoding was successful
	if ($decoded_data !== false) {
		// Validation successful
		return $decoded_data;
	} else {
		// Validation failed
		return NULL;
	}
}

// convert from GB to Byte
function convertGBToByte($gbValue)
{
	return $gbValue * pow(1024, 3);
}

// Function to convert PHP size notation to bytes
function convertPHPSizeToBytes($size)
{
	$suffix = strtoupper(substr($size, -1));
	$value = (int)substr($size, 0, -1);
	switch ($suffix) {
		case 'P':
			$value *= 1024;
		case 'T':
			$value *= 1024;
		case 'G':
			$value *= 1024;
		case 'M':
			$value *= 1024;
		case 'K':
			$value *= 1024;
	}
	return $value;
}

// Function to handle file upload errors
function fileUploadError($errorCode)
{
	$errors = [
		UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
		UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
		UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
		UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
	];

	return $errors[$errorCode] ?? 'Unknown upload error.';
}
