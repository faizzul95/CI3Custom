<?php

namespace App\services\google;

defined('BASEPATH') or exit('No direct script access allowed');

use Google\Service\Drive\DriveFile;

class GoogleDrive extends GoogleServices
{
	private $parent_id;

	public function __construct($folderType = NULL)
	{
		parent::__construct('drive');
		$folders = $this->CI->config->item('folder_id');
		$this->parent_id = array_key_exists($folderType, $folders) ? $folders[$folderType] : NULL;
	}

	public function uploadFile($backup_file)
	{
		if (file_exists($backup_file)) {

			// Check if backup folder exists, create if necessary
			date_default_timezone_set('Asia/Kuala_Lumpur');
			$backup_folder_name = date('Y-m-d');
			$folderID = $this->checkFolder($backup_folder_name);

			try {

				// Upload the backup file to Google Drive
				$file_metadata = new DriveFile(array(
					'name' => basename($backup_file),
					'parents' => [$folderID]
				));

				$file = $this->service->files->create($file_metadata, array(
					'data' => file_get_contents($backup_file),
					'mimeType' => 'application/zip',
					'uploadType' => 'multipart',
					'fields' => 'id, webViewLink'
				));

				// Delete the backup file from the server
				unlink($backup_file);

				$res = ['resCode' => 200, 'message' => NULL, 'data' => $file];
			} catch (\Exception $e) {
				$res = ['resCode' => 400, 'message' => $e->getMessage(), 'data' => NULL];
			}

			return $res;
		} else {
			return ['resCode' => 400, 'message' => 'File to upload is does not exist', 'data' => NULL];
		}
	}

	private function checkFolder($folder_name)
	{
		$folder_id = '';

		$results = $this->service->files->listFiles(array(
			'q' => "'$this->parent_id' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
			'fields' => 'nextPageToken, files(id, name)'
		));

		date_default_timezone_set('Asia/Kuala_Lumpur');
		foreach ($results->getFiles() as $file) {
			$folderDriveName = $file->getName();

			// format folder name
			$dateFolder = date('Y-m-d', strtotime($folderDriveName));

			// Check if the date is exactly one month ago
			if (date('Y-m-d', strtotime('-1 month')) == $dateFolder) {
				$this->service->files->delete($file->getId()); // Delete the folder
			}

			if ($folderDriveName == $folder_name) {
				$folder_id = $file->getId();
			}
		}

		if (empty($folder_id)) {
			$folder_id = $this->createDriveFolder($folder_name);
		}

		return $folder_id;
	}

	public function createDriveFolder($backup_folder_name)
	{
		$backup_folder_metadata = new DriveFile(array(
			'name' => $backup_folder_name,
			'parents' => [$this->parent_id],
			'mimeType' => 'application/vnd.google-apps.folder'
		));

		$backup_folder = $this->service->files->create($backup_folder_metadata, array(
			'fields' => 'id'
		));

		return $backup_folder->id;
	}
}
