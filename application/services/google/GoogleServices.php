<?php

namespace App\services\google;

defined('BASEPATH') or exit('No direct script access allowed');

use Google\Client;
use Google\Service\Drive;
use Google\Service\Analytics;
use Google\Exception;

class GoogleServices
{
	public $CI;
	public $client;
	public $service;

	public function __construct($scope = NULL)
	{
		$this->CI = &get_instance();

		try {
			$this->CI->load->config('customs/google');

			// Set up the Google API client
			$this->client = new Client();
			$this->client->setClientId($this->CI->config->item('client_id'));
			$this->client->setClientSecret($this->CI->config->item('client_secret'));
			$this->client->setRedirectUri($this->CI->config->item('redirect_uri'));
			$this->client->setAccessType('offline');
			$this->client->setApplicationName(env('APP_NAME'));
			// $this->client->setApprovalPrompt('force');
			$this->client->setIncludeGrantedScopes(true);

			if (!empty($scope)) {
				if ($scope == 'drive') {
					$this->client->addScope(Drive::DRIVE_FILE);
					$this->service = new Drive($this->client);
				} else if ($scope == 'analytic') {
					$this->client->addScope(Analytics::ANALYTICS_READONLY);
					$this->service = new Analytics($this->client);
				}
			}

			// Get authorization code from Google
			$code = $this->CI->input->get('code');
			if (!empty($code)) {
				$this->client->fetchAccessTokenWithAuthCode($code);
				$this->saveAccessToken($this->client->getAccessToken());
				header('Location: ' . filter_var($this->CI->config->item('redirect_uri'), FILTER_SANITIZE_URL), TRUE, 301);
				exit;
			}

			// Authenticate the user and get an access token
			$this->getAccessToken();

			// Check if token expired
			if ($this->client->isAccessTokenExpired()) {
				// save refresh token to some variable
				$refreshTokenSaved = $this->client->getRefreshToken();

				// update access token
				$this->client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

				// pass access token to some variable
				$accessTokenUpdated = $this->client->getAccessToken();

				// append refresh token
				$accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

				// Set the new acces token
				$accessToken = $refreshTokenSaved;
				$this->client->setAccessToken($accessToken);

				// save to file
				$this->saveAccessToken($accessTokenUpdated);

				// set new token
				$this->getAccessToken();
			}
		} catch (\Exception $e) {
			return ['code' => 400, 'message' => $e->getMessage(), 'data' => NULL];
		}
	}

	public function getAccessToken()
	{
		// Check if an access token already exists in the database or file
		// If yes, return the access token
		// If not, authenticate the user and get a new access token

		$accessToken = '';

		// Code to get the access token from the database or file goes here
		$credentials_file_path = $this->CI->config->item('credentials_file_path');

		if (file_exists($credentials_file_path)) {
			$accessToken = file_get_contents($credentials_file_path);
		} else {
			// Authenticate the user and get a new access token
			$authUrl = $this->client->createAuthUrl();
			header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
		}

		// Authenticate the user and get an access token
		$this->client->setAccessToken($accessToken);

		return $accessToken;
	}

	public function saveAccessToken($accessToken)
	{
		// Code to save the access token to the database or file goes here
		$credentials_file_path = $this->CI->config->item('credentials_file_path');

		if (file_exists($credentials_file_path))
			unlink($credentials_file_path);

		$json = json_encode($accessToken);
		file_put_contents($credentials_file_path, $json);
	}
}
