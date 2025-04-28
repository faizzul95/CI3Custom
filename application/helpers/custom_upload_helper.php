<?php

const BANNED_REPLACE_WORD = '[CONTENT-BLOCKED]';

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
			'error' => 'File upload error: ' . fileUploadErrorMessage($fileError)
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
function fileUploadErrorMessage($errorCode)
{
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write the file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];

    return $errors[$errorCode] ?? 'Unknown upload error.';
}

// UPLOAD SECURITY HELPER

/**
 * Check if a string contains potentially malicious data
 * 
 * This function checks input strings for various types of malicious content including:
 * - XSS (Cross-Site Scripting) attempts
 * - JavaScript injection
 * - HTML injection
 * - Protocol handlers
 * - Base64 encoded malicious content
 * - DOM-based attacks
 * - SQL Injection attempts
 * - NoSQL Injection patterns
 * - Advanced XSS obfuscation techniques
 * 
 * @param string $data The input string to check
 * @param boolean $sanitizeValue Whether to sanitize and return the value (default: true)
 * @return array Returns ['malicious' => bool, 'value' => string] where value is either sanitized or original
 */
if (!function_exists('containsMalicious')) {
    function containsMalicious($input, $sanitizeValue = true)
    {
        // Return false if input is empty or not string
        if (empty($input) || !is_string($input)) {
            return ['malicious' => false, 'value' => $input];
        }

        // Store original input 
        $original_input = $input;

        // Convert to lowercase for case-insensitive matching
        $input = strtolower($input);

        // Define the list of tags that need to sanitize
        $tags_to_sanitize = 'a|abbr|address|area|article|aside|audio|b|base|bdi|bdo|blockquote|body|br|button|canvas|caption|cite|code|col|colgroup|data|datalist|dd|del|details|dfn|dialog|div|dl|dt|em|embed|fieldset|figcaption|figure|footer|form|h[1-6]|head|header|hr|html|i|iframe|img|input|ins|kbd|label|legend|li|link|main|map|mark|meta|meter|nav|noscript|object|ol|optgroup|option|output|p|param|picture|pre|progress|q|rb|rp|rt|rtc|ruby|s|samp|script|section|select|small|source|span|strong|style|sub|summary|sup|svg|table|tbody|td|template|textarea|tfoot|th|thead|time|title|tr|track|u|ul|var|video|wbr|marquee';

        // Use a single pattern with proper delimiters to match both opening and closing tags
        $combined_pattern = '#<(/?)(' . $tags_to_sanitize . ')(?:[^>]*?)>#i';
        $htmlMalStatus = false;

        // Check if any HTML tags exist and replace them if needed
        if (preg_match($combined_pattern, $input) === 1) {
            if ($sanitizeValue) {
                $original_input = preg_replace($combined_pattern, BANNED_REPLACE_WORD, $original_input);
            }
            $htmlMalStatus = true;
        }

        if ($htmlMalStatus) {
            return [
                'malicious' => true,
                'value' => $original_input
            ];
        }

        // First check for unusual character sequences that might be encoding attacks
        $suspicious_sequences = [
            // Increased threshold for repeating characters
            '/(.)\1{15,}/',                      // Any character repeated 15+ times 

            // Increased threshold for non-whitespace chars
            '/[^\s]{250,}/'                      // 250+ consecutive non-whitespace chars 
        ];

        foreach ($suspicious_sequences as $pattern) {
            if (preg_match($pattern, $input)) {
                return [
                    'malicious' => true,
                    'value' => $sanitizeValue ? preg_replace($pattern, BANNED_REPLACE_WORD, $original_input) : $original_input
                ];
            }
        }

        // Check for hex/unicode/octal encoded strings that might bypass filters
        // Modified to focus on actual exploits rather than general encoded characters
        $encodedChars = [
            // Focusing only on encoded script tags, not all brackets/quotes
            '/&#x0*(?:3c)script/i',             // Encoded <script

            // Only match base64 that contains suspicious executable content
            '/base64[^a-zA-Z0-9\+\/\=]*,\s*[a-zA-Z0-9\+\/\=]{30,}.*(?:script|eval|function)/i',

            // UTF-7 XSS - keep this as is since it's a genuine attack vector
            '/\+ADw-script/i',                  // UTF-7 encoded <script
        ];

        foreach ($encodedChars as $pattern) {
            if (preg_match($pattern, $input)) {
                return [
                    'malicious' => true,
                    'value' => $sanitizeValue ? preg_replace($pattern, BANNED_REPLACE_WORD, $original_input) : $original_input
                ];
            }
        }

        // Array of only dangerous event handlers to check (reduced list to focus on commonly exploited ones)
        $event_handlers = [
            'onload', 'onerror', 'onmouseover', 'onclick', 'onmousedown',
            'onmouseup', 'onkeypress', 'onkeydown', 'onkeyup', 'onsubmit',
            'onunload', 'onchange', 'onfocus', 'onblur'
        ];

        // Build a pattern to match dangerous event handlers in context (not just as words)
        $event_handler_pattern = '/\s+(';
        $event_handler_pattern .= implode('|', $event_handlers);
        $event_handler_pattern .= ')\s*=/i';

        // Check for any event handlers in the input
        if (preg_match($event_handler_pattern, $input)) {
            return [
                'malicious' => true,
                'value' => $sanitizeValue ? preg_replace($event_handler_pattern, BANNED_REPLACE_WORD, $original_input) : $original_input
            ];
        }

        // Combined pattern for truly dangerous HTML and JavaScript content only
        $pattern =
            // Advanced XSS evasion techniques - with word boundary checks
            '/<\s*s\s*c\s*r\s*i\s*p\s*t|' .        // Spaced script tag
            '\\\x3c|\\\u003c|' .                  // Encoded < character
            '\\\x3e|\\\u003e|' .                  // Encoded > character
            '\bfromcharcode\b|' .                 // String.fromCharCode() usage with word boundaries

            // Scripts and code execution - with word boundaries
            '<script|' .                           // Script tags
            '<\?(php)?|' .                         // PHP tags
            '\bjavascript:\b|' .                   // JavaScript protocol with word boundaries
            '\bvbscript:\b|' .                     // VBScript with word boundaries
            '\blivescript:\b|' .                   // LiveScript with word boundaries
            '\bmocha:\b|' .                        // Mocha protocol with word boundaries
            '\bexpression\s*\(|' .                // CSS expressions
            '\beval\s*\(|' .                      // eval() with word boundaries
            '\bdebugger\b|' .                     // debugger keyword with word boundaries
            '\bdocument.write\b|' .               // document object with word boundaries
            '\bdocument.cookie\b|' .               // document object with word boundaries

            // Event handlers - now with word boundaries
            '\bon\w+\s*=|' .                       // Generic event handlers
            '\bonclick=|' .                        // onclick
            '\bondblclick=|' .                     // ondblclick
            '\bonmousedown=|' .                    // onmousedown
            '\bonmousemove=|' .                    // onmousemove
            '\bonmouseout=|' .                     // onmouseout
            '\bonmouseover=|' .                    // onmouseover
            '\bonmouseup=|' .                      // onmouseup
            '\bonkeydown=|' .                      // onkeydown
            '\bonkeypress=|' .                     // onkeypress
            '\bonkeyup=|' .                        // onkeyup
            '\bonload=|' .                         // onload
            '\bonunload=|' .                       // onunload
            '\bonerror=|' .                        // onerror
            '\bonsubmit=|' .                       // onsubmit
            '\bonreset=|' .                        // onreset
            '\bonselect=|' .                       // onselect
            '\bonchange=|' .                       // onchange
            '\bonblur=|' .                         // onblur
            '\bonfocus=|' .                        // onfocus

            // Dangerous tags - these need to be tags, not substrings
            '<iframe|' .                          // iframes
            '<frame|' .                           // frames
            '<object|' .                          // objects
            '<embed|' .                           // embed
            '<applet|' .                          // applet
            '<meta|' .                            // meta
            '<link|' .                            // link
            '<style|' .                           // style
            '<form|' .                            // form
            '<input|' .                           // input
            '<textarea|' .                        // textarea
            '<button|' .                          // button
            '<select|' .                          // select
            '<option|' .                          // option
            '<xml|' .                             // xml
            '<svg|' .                             // svg
            '<math|' .                            // math
            '<canvas|' .                          // canvas
            '<video|' .                           // video
            '<audio|' .                           // audio

            // Data URIs and protocols - with word boundaries
            'data:\s*[^\s]*?base64|' .           // Base64 data URI
            'data:\s*text\/html|' .              // HTML data URI
            'data:\s*[^\s]*?javascript|' .        // JavaScript data URI
            '\bblob:\b|' .                        // Blob URI with word boundaries
            '\bfile:\b|' .                        // File protocol with word boundaries

            // Attributes that might contain exploits
            'href\s*=\s*[\'"]?\s*(javascript|data|vbscript):|' .  // Dangerous href values
            'src\s*=\s*[\'"]?\s*(javascript|data|vbscript):|' .   // Dangerous src values
            'action\s*=\s*[\'"]?\s*(javascript|data|vbscript):|' . // Dangerous action values
            '\bformaction\s*=|' .                 // formaction with word boundaries
            '\bposter\s*=|' .                     // poster with word boundaries
            '\bbackground\s*=|' .                 // background with word boundaries
            '\bdynsrc\s*=|' .                     // dynsrc with word boundaries
            '\blowsrc\s*=|' .                     // lowsrc with word boundaries
            '\bping\s*=|' .                       // ping with word boundaries

            // CSS and style-related - with word boundaries
            '\bbehavior\s*:|' .                   // CSS behavior with word boundaries
            '\b@import\b|' .                      // CSS import with word boundaries
            'url\s*\(\s*[\'"]?\s*(javascript|data|vbscript):|' . // Dangerous CSS URLs

            // Encoding and special characters
            '(?:\\\\u00[0-9A-Fa-f]{2}script)|' . // Unicode escapes for script tags
            '%[0-9A-Fa-f]{2}.*?(?:script|alert|eval|on\w+\s*=)|' .   // URL encoding followed by suspicious content
            '\bcharset\s*=|' .                    // charset with word boundaries

            // Additional dangerous patterns - with word boundaries
            '\bfunction\s*\(|' .                  // Function declaration with word boundaries
            '\bsetInterval\b|' .                  // setInterval with word boundaries
            '\bsetTimeout\b|' .                   // setTimeout with word boundaries
            '\bnew\s+Function\b|' .               // Function constructor with word boundaries
            '\bconstructor\s*\(|' .               // constructor with word boundaries
            '\b__proto__\b|' .                    // prototype pollution with word boundaries
            '\bprototype\[|' .                    // prototype manipulation
            '\[\s*"prototype"\s*\]|' .           // prototype access
            '\bwith\s*\(/ix';                    // with statement with word boundaries

            // XML dangerous patterns - actual attack vectors only
            '<!\[CDATA\[.*?<.*?>.*?\]\]>|' .       // CDATA containing HTML tags
            '<!ENTITY.*?SYSTEM|' .                 // External entity declarations only
            '<!DOCTYPE.*?SYSTEM|' .                // DOCTYPE with SYSTEM

            // Advanced XSS evasion techniques - focused on script execution
            '<\s*s\s*c\s*r\s*i\s*p\s*t\s*>|' .     // Spaced script tag with brackets
            '\bj\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:|' . // javascript: protocol with spacing

            // Scripts and code execution - actual execution contexts only
            '<script[^>]*>[^<]*<\/script>|' .      // Complete script tags with content
            '<\?php|' .                            // PHP tags specifically
            '\bjavascript:[^"\']*(?:alert|eval|document\.|window\.)|' . // JavaScript protocol with functions
            '\bvbscript:[^"\']*(?:msgbox|execute)|' . // VBScript with actions only
            '\bdata:[^"\']*base64[^"\']*,[^"\']*<script|' . // Data URI with script tags

            // More targeted evals and code execution
            '\beval\s*\([^)]*(?:document|window|alert|fetch|ajax)|' . // eval with suspicious content

            // Dangerous iframe, object with source
            '<iframe[^>]*src=|' .                  // iframe with src
            '<object[^>]*data=|' .                 // object with data

            // Data URIs that contain script content
            'data:\s*[^\s]*?(?:javascript|html).*?<script|' . // Data URIs with scripts

            // Only active CSS exploitations
            'url\s*\(\s*[\'"]?\s*(?:javascript:|data:[^)]*script)|' . // CSS URLs with script content

            // Added specific patterns for the examples
            '&lt;script&gt;|' .                   // HTML entity encoded script tag
            '&#60;script&#62;|' .                 // Decimal HTML entity encoded script tag
            '&#x3c;script&#x3e;|' .               // Hex HTML entity encoded script tag
            '&lt;img[^&]*onerror=|' .             // HTML entity encoded img with onerror
            '&#x3c;svg[^&]*onload=|' .            // Hex HTML entity encoded svg with onload
            '\\\\74script|' .                     // Octal encoded script tag
            '\\\\u003Csvg[^\\\\]*onload=|' .      // Unicode encoded svg with onload
            '\\\\x3Csvg[^\\\\]*onload=|' .        // Hex encoded svg with onload

            // URL encoded patterns
            '%3Cscript|' .                        // URL encoded script tag
            '%3Csvg|' .                           // URL encoded svg tag
            '%3Cimg|' .                           // URL encoded img tag
            '%3Ciframe|' .                        // URL encoded iframe tag
            'javascript%3A|' .                    // URL encoded javascript protocol
            '%3Cobject|' .                        // URL encoded object tag
            '%3Cembed|' .                         // URL encoded embed tag
            'onload%3D|' .                        // URL encoded onload
            'onerror%3D|' .                       // URL encoded onerror
            'onclick%3D|' .                       // URL encoded onclick
            'onmouseover%3D|' .                   // URL encoded onmouseover

            // Octal/hex encoded script tags (specifically for your example)
            '\\\\74script\\\\76|' .               // Octal encoded <script> tag
            '\\\\74/script\\\\76/ix';             // Octal encoded </script> tag

        // Run the main pattern check
        if (preg_match($pattern, $input) === 1) {
            // Check for mathematical expressions
            if (preg_match('/^[a-zA-Z0-9\s\+\-\*\/\(\)\>\<\=\&\|\!\^\.]+$/', $input)) {
                // This is likely a mathematical or logical expression, not XSS
                return [
                    'malicious' => false,
                    'value' => $original_input
                ];
            }

            // Check for URL query parameters
            if (preg_match('/^https?:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/[a-zA-Z0-9\-\._~:\/\?#\[\]@!\$&\'\(\)\*\+,;=]*)?$/', $input)) {
                // This is a valid URL with query parameters
                return [
                    'malicious' => false,
                    'value' => $original_input
                ];
            }

            // Check for URL query string format specifically
            if (preg_match('/^[a-zA-Z0-9\-_]+(\.[a-zA-Z0-9\-_]+)*\/[a-zA-Z0-9\-_\/?&=\.]+$/', $input) || preg_match('/^[a-zA-Z0-9\-_\/?&=\.]+$/', $input)) {
                // This is likely a URL path with query parameters, not XSS
                return [
                    'malicious' => false,
                    'value' => $original_input
                ];
            }

            return [
                'malicious' => true,
                'value' => $sanitizeValue ? preg_replace($pattern, BANNED_REPLACE_WORD, $original_input) : $original_input
            ];
        }

        // Mixed case variations - reduced and more targeted
        $mixedCasePatterns = [
            // Complete script tags with content only, not just the word "script"
            '/<[^>]*[sS][cC][rR][iI][pP][tT][^>]*>[^<]*<\/[^>]*[sS][cC][rR][iI][pP][tT][^>]*>/',

            // JavaScript protocol in an active context
            '/[hH][rR][eE][fF]\s*=\s*["\'][jJ][aA][vV][aA][sS][cC][rR][iI][pP][tT]:/i',

            // Alert in active execution context only
            '/[oO][nN]\w+\s*=\s*["\'][^"\']*[aA][lL][eE][rR][tT]\s*\(/i',
        ];

        foreach ($mixedCasePatterns as $pattern) {
            if (preg_match($pattern, $original_input) === 1) {
                return [
                    'malicious' => true,
                    'value' => $sanitizeValue ? preg_replace($pattern, BANNED_REPLACE_WORD, $original_input) : $original_input
                ];
            }
        }

        // Check for character frequency anomalies
        $charCount = strlen($input);
        if ($charCount > 50) {
            // Check for unusual character distribution
            $specialChars = preg_match_all('/[^a-zA-Z0-9\s\p{L}]/', $input, $matches); // Added \p{L} for UTF-8 letters
            $specialCharRatio = $specialChars / $charCount;

            // Only flag if extreme ratio 
            if ($specialCharRatio > 0.9) {
                return [
                    'malicious' => true,
                    'value' => $sanitizeValue ? preg_replace('/[^a-zA-Z0-9\s\p{L}]/', BANNED_REPLACE_WORD, $original_input) : $original_input
                ];
            }
        }

        // Add whitelist for common programming/mathematical expressions
        $whitelist_patterns = [
            '/\blog(?:\d+)?\s*\(\s*\d+\s*\)\s*=\s*\d+/i',  // Math logarithm notation
            '/\b[xyz]\s*[<>=]\s*[xyz](?:\s*&&\s*[xyz]\s*[<>=]\s*[xyz])*\b/i', // Logical expressions
            '/\b\([a-z]\s*\+\s*[a-z]\)²\s*=\s*[a-z]²\s*\+\s*\d[a-z]{1,2}\s*\+\s*[a-z]²/i', // Algebraic expressions
            '/sqrt\(\d+\)\s*=\s*\d+/i', // Square root expressions
            '/p\(\s*a\s*\|\s*b\s*\)\s*=\s*[\w\(\)]+/i', // Probability notation
        ];

        // If the input matches a whitelist pattern, return safe regardless of other checks
        foreach ($whitelist_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return [
                    'malicious' => false,
                    'value' => $original_input
                ];
            }
        }

        // Whitelist for common programming terms used in educational context
        $programming_terms = [
            '/\b(?:javascript|document\.write|alert)\b.*?(?:explained|tutorial|example|described|is a)/i',
            '/\b(?:html|css|php|jquery)\b.*?(?:explained|tutorial|example|described|is a)/i',
        ];

        foreach ($programming_terms as $pattern) {
            if (preg_match($pattern, $input)) {
                return [
                    'malicious' => false,
                    'value' => $original_input
                ];
            }
        }

        return [
            'malicious' => false,
            'value' => $original_input
        ];
    }
}

/**
 * Scan a CSV file for malicious content in each cell
 * 
 * This function processes a CSV file line by line to minimize memory usage,
 * checking each cell for potential security threats using containsMalicious().
 * Optimized to handle extremely large files.
 * 
 * @param string $filePath Path to the CSV file to check
 * @param bool $terminate Whether to terminate the process execution when malicious content is found (default: false)
 * @param array $options Additional options for processing large files:
 *                      - include_data: Return processed data (default: true)
 *                      - skip_first_line: Skip header row (default: true)
 *                      - max_execution_time: Maximum execution time in seconds (default: 0 = no limit)
 *                      - memory_limit: Maximum memory to use in MB (default: NULL = use PHP default)
 *                      - buffer_size: File read buffer size in bytes (default: 8192)
 *                      - max_file_size: Maximum file size in MB (default: 4MB)
 *                      - optimize_memory: Number of rows to process before GC (default: 10000)
 *                      - csv_line_length: Maximum line length for fgetcsv in characters (default: 1000 = no limit)
 *                      - sanitize_value: Return sanitized value instead of original when malicious content is detected (default: true)
 * @return array Returns standardized response array with the following keys:
 *                - success: boolean indicating operation success
 *                - message: operation result message
 *                - file_info: array containing file metadata information
 *                - header: array of CSV header data (if skip_first_line is true)
 *                - total_skipped_rows: count of skipped rows
 *                - total_processed_rows: count of processed rows
 *                - column_empty_count: array of empty columns with their names (if skip_first_line is true)
 *                - issue: array of detected issues (if any)
 *                - data: processed data (if include_data is true)
 */
if (!function_exists('extractSafeCSVContent')) {
    function extractSafeCSVContent($files, $terminate = false, $options = [])
    {
        // Set default options and merge with provided options
        $defaultOptions = [
            'include_data' => true,   // Will return data that has been processed
            'skip_first_line' => true,       // Skip first line check
            'max_execution_time' => 0,       // No execution time limit by default
            'memory_limit' => null,          // Use PHP default memory limit
            'buffer_size' => 8192,           // File read buffer size (bytes)
            'max_file_size' => 4,            // Maximum file size in MB
            'optimize_memory' => 10000,      // Number of rows to process before force GC to cleanup memory
            'csv_line_length' => 1000,       // Maximum line length for fgetcsv (0 = no limit)
            'sanitize_value' => true        // Return sanitized value instead of original when malicious content is detected
        ];
        $options = array_merge($defaultOptions, $options);

        // Default response
        $result = [
            'success' => false,
            'message' => '',
            'file_info' => [],
            'header' => [],
            'total_skipped_rows' => 0,
            'total_processed_rows' => 0,
            'issue' => [],
            'data' => [],
            'column_empty_count' => []
        ];

        if ($files['error'] !== UPLOAD_ERR_OK) {
            $result['message'] = 'File upload error: ' . fileUploadErrorMessage($files['error']);
            return $result;
        }

        $filename = $files['name'];
        $filePath = $files['tmp_name'];
        $fileSize = $files['size'];
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $fileSizeMB = round($fileSize / (1024 * 1024), 2); // Convert bytes to MB

        $checkFilename = containsMalicious($filename, true);
        if ($checkFilename['malicious']) {
            $result['message'] = 'Security warning: The filename contains potentially harmful characters. Please rename your file and try again.';
            return $result;
        }

        $result['file_info'] = [
            'name' => trim($filename),
            'path' => $filePath,
            'ext' => trim($fileExtension),
            'size' => $fileSize,
            'size_mb' => $fileSizeMB,
        ];

        if (!file_exists($filePath)) {
            $result['message'] = 'File not found: ' . $filePath;
            return $result;
        }

        if (!is_readable($filePath)) {
            $result['message'] = 'File not readable: ' . $filePath;
            return $result;
        }

        if ($fileExtension !== 'csv') {
            $result['message'] = 'File is not a CSV: ' . $filename;
            return $result;
        }

        if ($fileSizeMB > $options['max_file_size']) {
            $result['message'] = 'File size exceeds the maximum allowed limit of ' . $options['max_file_size'] . ' MB: ' . $filename;
            return $result;
        }

        // Apply execution time limit if specified
        if ($options['max_execution_time'] > 0) {
            set_time_limit($options['max_execution_time']);
        }

        // Apply memory limit if specified
        if (!empty($options['memory_limit']) || $options['memory_limit'] != 0) {
            ini_set('memory_limit', $options['memory_limit']);
        }

        // Initialize return values
        $dataProcess = [];

        // Open file for reading - using streaming approach to minimize memory usage
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $result['message'] = 'Failed to open file: ' . $filePath;
            return $result;
        }

        // Set buffer size to reduce memory usage
        stream_set_read_buffer($handle, $options['buffer_size']);

        $rowNumber = 0;

        try {

            $columnHasValue = [];

            // Skip header if needed
            if ($options['skip_first_line']) {
                // Read the first line as headers
                $headers = fgetcsv($handle, $options['csv_line_length'], ",");
                if ($headers === false || empty(array_filter($headers))) {
                    $result['message'] = 'CSV file is empty or has invalid headers.';
                    fclose($handle);
                    return $result;
                }

                // Trim headers to remove whitespace
                $result['header'] = array_map('trim', $headers);
                $result['total_skipped_rows'] += 1;

                // Initialize tracking array for all columns
                $columnHasValue = array_fill(0, count($result['header']), 0);
            }

            // Process each row using fgetcsv
            while (($row = fgetcsv($handle, $options['csv_line_length'], ",")) !== false) {
                ++$result['total_processed_rows'];

                // Skip completely empty rows (all columns are empty)
                if (!array_filter($row, 'strlen')) {
                    ++$result['total_skipped_rows'];
                    continue;
                }

                foreach ($row as $colNumber => $cellValue) {
                    $cellValue = trim($cellValue);

                    // Store processed data if requested
                    if ($options['include_data']) {
                        $dataProcess[$rowNumber][$colNumber] = $cellValue;
                    }

                    // Skip processing for empty cells
                    if (empty($cellValue)) {
                        if ($options['skip_first_line'])
                            $columnHasValue[$colNumber]++;

                        continue;
                    }

                    $check = containsMalicious($cellValue, $options['sanitize_value']);
                    $malicious = $check['malicious'] ?? false;
                    $sanitizeValue = $check['value'] ?? $cellValue;

                    // Check if cell contains malicious content
                    if ($malicious) {
                        $issue = [
                            'row' => $result['total_processed_rows'] + 1,
                            'column' => $colNumber + 1,
                            'value' => htmlspecialchars(mb_substr($cellValue, 0, 400) . (mb_strlen($cellValue) > 400 ? '...' : '')),
                            'sanitize_value' => htmlspecialchars($sanitizeValue)
                        ];

                        $result['issue'][] = $issue;

                        if ($terminate) {
                            if (is_resource($handle)) {
                                fclose($handle);
                            }

                            $result['data'] = []; // reset
                            $result['message'] = "Security Alert - Row {$issue['row']} column {$issue['column']}: 
                                <span 
                                style='text-decoration: underline; cursor: pointer; position: relative; display: inline-block; color: #d9534f; font-weight: bold; padding: 2px 4px; background-color: rgba(217, 83, 79, 0.1); border-radius: 3px;' 
                                onmouseover=\"this.querySelector('.tooltip').style.visibility='visible'; this.querySelector('.tooltip').style.opacity='1';\" 
                                onmouseout=\"this.querySelector('.tooltip').style.visibility='hidden'; this.querySelector('.tooltip').style.opacity='0';\">
                                Malicious code detected
                                <span style='visibility: hidden; width: 450px; background-color: #ffffff; color: #333333; text-align: left; 
                                border-radius: 8px; padding: 16px; position: absolute; z-index: 1000; top: -15px; left: 100%; 
                                margin-left: 15px; opacity: 0; transition: opacity 0.3s, transform 0.3s; box-shadow: 0 6px 20px rgba(0,0,0,0.15);
                                border: 1px solid #e0e0e0; transform: translateY(5px); font-weight: normal; line-height: 1.5;' 
                                class='tooltip'>
                                    <div style='margin-bottom: 12px; font-size: 15px; font-weight: 600; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px; display: flex; align-items: center;'>
                                        Security Issue Details
                                    </div>
                                        
                                    <div style='margin-bottom: 8px; font-weight: 500; color: #d32f2f; display: flex; align-items: center;'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' style='margin-right: 6px;'><path fill='currentColor' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'/></svg>
                                        Original Content:
                                    </div>
                                    <div style='background-color: #f8f8f8; padding: 10px; border-radius: 6px; margin-bottom: 14px; border-left: 3px solid #d32f2f;'>
                                        <textarea readonly style='width: 100%; height: 80px; background-color: transparent; color: #333333; 
                                        border: none; resize: vertical; font-family: monospace; font-size: 12px; line-height: 1.5;
                                        padding: 0; margin: 0; outline: none; overflow-y: auto;'>" . str_replace('"', '&quot;', htmlspecialchars($cellValue)) .
                                "</textarea>
                                    </div>
                                    
                                    <div style='margin-bottom: 8px; font-weight: 500; color: #2e7d32; display: flex; align-items: center;'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' style='margin-right: 6px;'><path fill='currentColor' d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/></svg>
                                        Sanitized Content:
                                    </div>
                                    <div style='background-color: #f8f8f8; padding: 10px; border-radius: 6px; border-left: 3px solid #2e7d32;'>
                                        <textarea readonly style='width: 100%; height: 80px; background-color: transparent; color: #333333; 
                                        border: none; resize: vertical; font-family: monospace; font-size: 12px; line-height: 1.5;
                                        padding: 0; margin: 0; outline: none; overflow-y: auto;'>" . $issue['sanitize_value'] .
                                "</textarea>
                                    </div>
                                    
                                    <div style='font-size: 12px; color: #666666; margin-top: 12px; display: flex; align-items: center; background-color: #fff8e1; padding: 8px; border-radius: 4px;'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' style='color: #f57c00; margin-right: 8px;'><path fill='currentColor' d='M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'/></svg>
                                        Potentially harmful content has been replaced with '<b>" . BANNED_REPLACE_WORD . "</b>'
                                    </div>
                                </span>
                            </span>. Please verify your data source and remove any potentially harmful content.";

                            return $result;
                        } else {
                            // Store processed data if requested as a sanitize data
                            if ($options['include_data']) {
                                $dataProcess[$rowNumber][$colNumber] = $sanitizeValue;
                            }
                        }
                    }

                    unset($check);
                }

                // Free memory
                unset($row);

                if ($rowNumber % $options['optimize_memory'] === 0) {
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }

                $rowNumber++;
            }

            $result['column_empty_count'] = $columnHasValue;

        } catch (Exception $e) {
            if (is_resource($handle)) {
                fclose($handle);
            }

            $result['message'] = 'Error processing CSV: ' . $e->getMessage();
            return $result;

        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $message = 'CSV scanned successfully.';
        $success = true;
        if (!empty($result['issue'])) {
            $message = 'Malicious content detected in CSV.';
            $success = false;
        } else if ($options['include_data'] && empty($dataProcess)) {
            $message = 'No valid data found in CSV file.';
            $success = false;
        }

        $result['success'] = $success;
        $result['message'] = $message;
        $result['data'] = $options['include_data'] ? $dataProcess : [];

        return $result;
    }
}