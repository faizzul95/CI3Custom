<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Mailgun\Mailgun;

function sentMail($recipientData = NULL, $subject = NULL, $dataBody = NULL, $attachment = NULL)
{
	if (in_array(env('MAIL_DRIVER'), ['mailer', 'mailgun'])) {
		if (env('MAIL_DRIVER') == 'mailer') {
			return sentUsingMailer($recipientData, $subject, $dataBody, $attachment);
		} else if (env('MAIL_DRIVER') == 'mailgun') {
			return sentUsingMailGun($recipientData, $subject, $dataBody, $attachment);
		}
	} else {
		log_message('debug', "Mailer Error: missing driver " . env('MAIL_DRIVER'));
		return ['success' => false, 'message' => "Mailer Error: Could not find any driver for " . env('MAIL_DRIVER')];
	}
}

// Sent Using MailGun Email
function sentUsingMailGun($recipientData = NULL, $subject = NULL, $dataBody = NULL, $attachment = NULL)
{
	header("Access-Control-Allow-Origin: *");

	$mgClient = Mailgun::create(env('MAIL_USERNAME'));

	try {
		$cc = NULL;
		$bcc = NULL;

		// Add a To recipient
		if (is_array($recipientData['recipient_email'])) {
			$to = $recipientData['recipient_email'];
		} else {
			$to = $recipientData['recipient_name'] . ' <' . $recipientData['recipient_email'] . '>';
		}

		// Add a CC recipient
		if (array_key_exists("recipient_cc", $recipientData) && hasData($recipientData['recipient_cc'])) {
			$cc = $recipientData['recipient_cc'];
		}

		// Add a BCC recipient
		if (array_key_exists("recipient_bcc", $recipientData) && hasData($recipientData['recipient_bcc'])) {
			$bcc = $recipientData['recipient_bcc'];
		}

		$dataSent = [
			'from'    => env('MAIL_FROM_NAME') . '<' . env('MAIL_FROM_ADDRESS') . '>',
			'to'      => $to,
			'subject' => $subject,
			'html'    => $dataBody,
			'cc'      => $cc,
			'bcc'     => $bcc,
			// 'attachment' => array(
			//     array(
			//         'filePath' => 'test.txt',
			//         'filename' => 'test_file.txt'
			//     )
			// )
		];

		$result = $mgClient->messages()->send(env('APP_DOMAIN'), $dataSent);

		if ($result) {
			return ['success' => true, 'message' => 'Email sent successfully'];
		} else {
			return ['success' => false, 'message' => 'Email unable to sent'];
		}

		$status = true;
		$status_sent = 1;
	} catch (Exception $e) {
		$status = false;
		$status_sent = 0;
	}
}

// Sent Using PHPMAILER / Default
function sentUsingMailer($recipientData = NULL, $subject = NULL, $dataBody = NULL, $attachment = NULL)
{
	// Create an instance; passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try {

		// Server settings
		if (filter_var(env('MAIL_DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
			$mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
		}

		if (filter_var(env('MAIL_IS_SMTP'), FILTER_VALIDATE_BOOLEAN)) {
			$mail->isSMTP();  									// Send using SMTP
			$mail->SMTPAuth   = true;                 			// Enable SMTP authentication
		}

		$mail->Host       = env('MAIL_HOST', 'smtp.gmail.com'); // Set the SMTP server to send through
		$mail->Username   = env('MAIL_USERNAME', '');       	// SMTP username
		$mail->Password   = env('MAIL_PASSWORD', '');      		// SMTP password
		$mail->SMTPSecure = env('MAIL_ENCRYPTION', 'TLS');  	// Enable implicit TLS encryption
		$mail->Port       = env('MAIL_PORT', 587);          	// TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

		// Recipients
		$mail->setFrom(env('MAIL_FROM_ADDRESS', 'do-no-reply@email.test'), env('MAIL_FROM_NAME'));
		$mail->addAddress($recipientData['recipient_email'], $recipientData['recipient_name']); // Add a recipient

		// Add a CC recipient
		if (array_key_exists("recipient_cc", $recipientData) && hasData($recipientData['recipient_cc'])) {
			$ccs = $recipientData['recipient_cc'];
			if (is_array($ccs)) {
				foreach ($ccs as $cc) {
					$mail->addCC($cc);
				}
			} else {
				$mail->addCC($ccs);
			}
		}

		// Add a BCC recipient
		if (array_key_exists("recipient_bcc", $recipientData) && hasData($recipientData['recipient_bcc'])) {
			$bccs = $recipientData['recipient_bcc'];
			if (is_array($bccs)) {
				foreach ($bccs as $bcc) {
					$mail->AddBCC($bcc);
				}
			} else {
				$mail->AddBCC($bccs);
			}
		}

		// Content
		$mail->isHTML(true); //Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $dataBody;

		if (!empty($attachment)) {
			if (is_array($attachment)) {
				foreach ($attachment as $files) {
					if (file_exists($files))
						$mail->addAttachment($files);
				}
			} else {
				if (file_exists($attachment))
					$mail->addAttachment($attachment);
			}
		}

		if ($mail->send()) {
			$response =  ['success' => true, 'message' => 'Email sent successfully'];
		} else {
			$response =  ['success' => false, 'message' => 'Email unable to sent'];
		}
	} catch (Exception $e) {
		log_message('debug', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
		$response = ['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
	}

	return $response;
}

function arrayDataReplace($data)
{
	$newKey = $newValue = $newData = [];
	foreach ($data as $key => $value) {
		array_push($newKey, '%' . $key . '%');
		array_push($newValue, $value);
	}

	foreach ($newKey as $key => $data) {
		$newData[$data] = $newValue[$key];
	}

	return $newData;
}
