<?php

namespace App\middleware\core\traits;

trait SecurityHeadersTrait
{
	public function set_security_headers()
	{
		// Add security headers
		$this->load->helper('security');

		// This will set the Strict-Transport-Security header to ensure that all communication with your server is done over HTTPS for the next year (31536000 seconds) and includes subdomains as well.
		$this->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

		// This will set the Content-Security-Policy header to allow loading resources only from the same origin and allow inline scripts and styles.
		// Example : default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';
		$this->output->set_header("Content-Security-Policy: img-src 'self' data:;");

		// This will set the X-Frame-Options header to allow embedding the content only in the same origin.
		// Example : DENY or SAMEORIGIN
		$this->output->set_header('X-Frame-Options: SAMEORIGIN');

		// This will set the X-Content-Type-Options header to prevent the browser from interpreting files as a different MIME type.
		$this->output->set_header('X-Content-Type-Options: nosniff');

		// This will set the Referrer-Policy header to only send the referrer information to the same origin or same site.
		// Example : no-referrer-when-downgrade or strict-origin-when-cross-origin
		$this->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');

		// This will set the Permissions-Policy header to only allow access to geolocation, microphone, and camera if explicitly granted by the user.
		
		// Note :
		// a) The self keyword ensures that the permission is only granted if the request is from the same origin as the page. 
		// b) The https://* value allows access only when the page is served over HTTPS.

		// Example : 
		// 1) accelerometer
		// 2) ambient-light-sensor
		// 3) autoplay
		// 4) camera
		// 5) document-domain
		// 6) encrypted-media
		// 7) execution-while-not-rendered
		// 8) execution-while-out-of-viewport
		// 9) fullscreen
		// 10) geolocation
		// 11) gyroscope
		// 12) layout-animations
		// 13) legacy-image-formats
		// 14) loading-frame-default-eager
		// 15) magnetometer
		// 16) microphone
		// 17) midi
		// 18) oversized-images
		// 19) payment
		// 20) picture-in-picture
		// 21) publickey-credentials-get
		// 22) screen-wake-lock
		// 23) sync-xhr
		// 24) usb
		// 25) vertical-scroll
		// 26) xr-spatial-tracking
		
		$this->output->set_header("Permissions-Policy: geolocation=(self;https://*), microphone=(self;https://*), camera=(self;https://*), fullscreen=(self;), sync-xhr=(self;), usb=(self;)");
	}
}
