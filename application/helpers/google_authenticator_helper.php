<?php

use Vectorface\GoogleAuthenticator;

if (!function_exists('gaObj')) {
	function gaObj()
	{
		return new GoogleAuthenticator();
	}
}

if (!function_exists('generateSecretGA')) {
	function generateSecretGA()
	{
		return gaObj()->createSecret();
	}
}

if (!function_exists('generateImageGA')) {
	function generateImageGA($secret)
	{
		return gaObj()->getQRCodeUrl(env('APP_NAME'), $secret);
	}
}

if (!function_exists('verifyGA')) {
	function verifyGA($secret, $enterCode)
	{
		// 2 = 2*30sec clock tolerance
		$checkResult = gaObj()->verifyCode($secret, $enterCode, 2);
		if ($checkResult) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('testAuthenticator')) {
	function testAuthenticator()
	{
		$ga = gaObj();
		$secret = $ga->createSecret();
		echo "Secret is: {$secret} <br><br>";

		$qrCodeUrl = $ga->getQRCodeUrl('UiTM - ' . env('APP_NAME'), $secret);
		echo "PNG Data URI for the QR-Code: <img src={$qrCodeUrl} /> <br><br>";

		$oneCode = $ga->getCode($secret);
		echo "Checking Code '$oneCode' and Secret '$secret': ";

		// 2 = 2*30sec clock tolerance
		$checkResult = $ga->verifyCode($secret, $oneCode, 2);
		if ($checkResult) {
			echo 'OK';
		} else {
			echo 'FAILED';
		}
	}
}
