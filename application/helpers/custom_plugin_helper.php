<?php

use eftec\bladeone\BladeOne; // reff : https://github.com/EFTEC/BladeOne
use voku\helper\AntiXSS; // reff : https://github.com/voku/anti-xss

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// SECURITY PLUGIN 

if (!function_exists('purify')) {
    function purify($post = NULL)
    {
        if (!empty($post)) {
            $antiXss = new AntiXSS();
            $antiXss->removeEvilAttributes(array('style')); // allow style-attributes
            return $antiXss->xss_clean($post);
        }

        return $post;
    }
}

if (!function_exists('antiXss')) {
    function antiXss($data) {
        $antiXss = new AntiXSS();
        $antiXss->removeEvilAttributes(['style']); // Allow style attributes

        if (is_array($data)) {
            // Optimize array iteration for efficiency
            foreach ($data as &$post) {
                $antiXss->xss_clean($post);
                if ($antiXss->isXssFound()) {
                    return true; // Immediately return if XSS is found
                }
            }
        } else {
            $antiXss->xss_clean($data);
            if ($antiXss->isXssFound()) {
                return true; // Immediately return if XSS is found
            }
        }

        return false; // No XSS found
    }
}

// BLADE PLUGIN

if (!function_exists('render')) {
	function render($fileName, $data = NULL)
	{
		$views = APPPATH . 'views';
		$cache = isMobileDevice() ? APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'blade_cache/mobile/' : APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'blade_cache/browser/';

		$fileName = $fileName . '.blade.php';

		if (file_exists($views . DIRECTORY_SEPARATOR . $fileName)) {

			if (!file_exists($cache)) {
				mkdir($cache, 0755, true);
			}

			loadBladeTemplate($views, $cache, $fileName, $data);
		} else {
			log_message('error', $fileName . 'not found');
			error('404');
		}
	}
}

if (!function_exists('loadBladeTemplate')) {
	function loadBladeTemplate($views, $cache = NULL, $fileName = NULL, $data = NULL)
	{
		# Please use this settings :
		# 0 - MODE_AUTO : BladeOne reads if the compiled file has changed. If has changed,then the file is replaced.
		# 1 - MODE_SLOW : Then compiled file is always replaced. It's slow and it's useful for development.
		# 2 - MODE_FAST : The compiled file is never replaced. It's fast and it's useful for production.
		# 5 - MODE_DEBUG :  DEBUG MODE, the file is always compiled and the filename is identifiable.
		try {
			$blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);
			// $blade->setAuth(currentUserID(), currentUserRoleID(), permission());
            $blade->pipeEnable=true; // pipes are disable by default so it must be enable.
			$blade->setBaseUrl(base_url() . 'public/'); // with or without trail slash
			echo $blade->run($fileName, $data);
		} catch (Exception $e) {
			log_message('error', 'Blade render : ' .$e->getMessage());
			echo "<b> ERROR FOUND : </b> <br><br>" . $e->getMessage() . "<br><br><br>" . $e->getTraceAsString();
		}
	}
}

// IMPORT EXCEL PLUGIN

if (!function_exists('readExcel')) {
    function readExcel($files, $filesPath = NULL, $maxAllowSize = 8388608)
    {
        $name = $files["name"];
        $tmp_name = $files["tmp_name"];
        $error = $files["error"];
        $size = $files["size"];
        $type = $files["type"];

        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv', // Add support for CSV files
            'application/csv', // Add support for CSV files
            'application/excel', // Add support for CSV files
            'application/vnd.ms-excel', // Add support for CSV files
            'application/vnd.msexcel', // Add support for CSV files
            // 'text/plain', // Add support for CSV files
        ];

        // 1st : check files type, only excel file are accepted
        if (in_array($type, $allowedFileType)) {

            // 2nd : check file size
            if ($size < $maxAllowSize) {
                if (file_exists($filesPath)) {

                    /**  Identify the type of $inputFileName  **/
                    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filesPath);
                    /**  Create a new Reader of the type that has been identified  **/
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                    /**  Load $inputFileName to a Spreadsheet Object  **/
                    $spreadsheet = $reader->load($filesPath);
                    /**  Convert Spreadsheet Object to an Array for ease of use  **/
                    $spreadSheetAry = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                    return ['code' => 200, 'data' => $spreadSheetAry, 'count' => count($spreadSheetAry)];
                } else {
                    return returnData(['code' => 422, 'message' => 'The files upload was not found.'], 422);
                }
            } else {
                return returnData(['code' => 422, 'message' => 'The size is not supported : ' . $size . ' bytes'], 422);
            }
        } else {
            return returnData(['code' => 422, 'message' => 'The file type is not supported : ' . $type], 422);
        }
    }
}

// EXPORT EXCEL PLUGIN

if (!function_exists('exportToExcel')) {
    function exportToExcel($data, $filename = "data.xlsx")
    {
        ini_set('display_errors', '1');
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 0);

        try {
            // reset previous buffer
            ob_end_clean();

            // start output buffering
            ob_start();

            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();

            // set properties
            $title = empty($option) ? "My Excel Data" : (isset($option['title']) ? $option['title'] : "My Excel Data");
            $spreadsheet->getProperties()
                ->setTitle($title)
                ->setKeywords('data,export,excel')
                ->setCreator(env('APP_NAME'))
                ->setLastModifiedBy(currentUserFullName())
                // ->setCompany(currentCompanyName())
                ->setCategory('Data Export')
                ->setCreated(timestamp());

            // Add data to the first sheet
            $sheet = $spreadsheet->getActiveSheet();

            // Set data in the worksheet
            $sheet->fromArray($data);

            // Set the headers to force a download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            // Create a new Xlsx writer and save the file
            $writer = new Xlsx($spreadsheet);

            // Check if the writer object is valid
            if ($writer === null) {
                return ['code' => 400, 'message' => 'Error creating Xlsx writer object'];
            }

            // end output buffering and flush the output
            ob_end_clean();

            $directory = 'public' . DIRECTORY_SEPARATOR . '_temp' . DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR;
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $tempFile = $directory . 'export_excel.xls';
            if (file_exists($directory)) {
                unlink($tempFile);
            }

            $result = $writer->save($tempFile);

            // Save to computer.
            // $result = $writer->save('php://output');

            // Check if the file was saved successfully
            // if ($result === null) {
            // 	return ['code' => 400, 'message' => 'Error saving Excel file'];
            // 	exit;
            // }

            // Return success message
            return ['code' => 200, 'message' => 'File exported', 'filename' => $filename, 'path' => url($tempFile)];
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            return ['code' => 400, 'message' => 'Error writing to file: ', $e->getMessage()];
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            return ['code' => 400, 'message' => 'Error: ', $e->getMessage()];
        } catch (Exception $e) {
            // Return error message
            return ['code' => 400, 'message' => 'Error exporting file: ' . $e->getMessage()];
        }
    }
}
