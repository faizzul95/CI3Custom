<?php

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

if (!function_exists('generateQR')) {
	function generateQR($dataQR = NULL, $folder = NULL, $logo = NULL, $fileName = "qr.png", $labelQR = NULL)
	{
		$writer = new PngWriter();

		// Create QR code
		$qrCode = QrCode::create($dataQR)
			->setEncoding(new Encoding('UTF-8'))
			->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
			->setSize(300)
			->setMargin(10)
			->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
			->setForegroundColor(new Color(0, 0, 0))
			->setBackgroundColor(new Color(255, 255, 255));

		if (!empty($logo)) {
			// Create generic logo
			$fileLogo = $logo['image'];
			$sizeLogo = $logo['size'];

			$logo = Logo::create($fileLogo)->setResizeToWidth($sizeLogo);
		}

		// Create generic label
		if (!empty($labelQR)) {
			$label = Label::create($labelQR)
				->setTextColor(new Color(255, 0, 0));

			$result = $writer->write($qrCode, $logo, $label);
		} else {
			$result = $writer->write($qrCode, $logo);
		}

		header('Content-Type: ' . $result->getMimeType());

		// Save it to a file
		$result->saveToFile($folder . '/' . $fileName);

		return [
			'qrFolder' => $folder,
			'qrFilename' => $fileName,
			'qrPath' => $folder . '/' . $fileName,
		];
	}
}
