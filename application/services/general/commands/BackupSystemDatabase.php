<?php

namespace App\services\general\commands;

class BackupSystemDatabase
{
	/**
	 * The console command task name.
	 *
	 * @var string
	 */
	protected $taskName = 'Backup System & Database';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set scheduled to backup database & System';
	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle($scheduler): void
	{
		$scheduler->call(function () {
			print "[" . timestamp('d/m/Y h:i A') . "]: {$this->taskName} currently is running\n";
			app('App\services\BackupSystem')->backup_database();
			app('App\services\BackupSystem')->backup_folder();
		})
			->onlyOne()
			->before(function () {
				print "[" . timestamp('d/m/Y h:i A') . "]: Job {$this->taskName} Started\n";
				echo shell_exec('php struck maintenance on'); // put system under maintenance before backup
			})
			->then(function () {
				print "[" . timestamp('d/m/Y h:i A') . "]: Job {$this->taskName} Finished\n";
				echo shell_exec('php struck maintenance off'); // put system online after backup complete
			})->daily('00:01');
	}
}
