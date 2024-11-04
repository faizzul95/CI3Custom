<?php

// CRON JOB (SERVICES)
// Route::group('/cron', function () {
// 	Route::get('/backup/{upload?}', 'BackupController@BackupDrive');
// });

Route::group('/sys', function () {
    // SYSTEM INFORMATION
    Route::get('/info', function () {
        phpinfo();
    });

    // SYSTEM LIST ALL DATABASE TABLE 
    Route::get('/db', function () {
        ci()->load->helper('custom_ci3_sysdb');
        render('system/sysdb', ['title' => 'Database', 'data' => listSysDB()]);
    });

    // SYSTEM LIST ALL MIGRATION FILES 
    Route::get('/migration', function () {
        ci()->load->helper('custom_ci3_sysdb');
        render('system/sysdb', ['title' => 'Migration', 'data' => listSysMigration()]);
    });

    // SYSTEM SHOW TABLE STRUCTURE
    Route::get('/table-structure/{table}', function ($table) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(getTableStructure($table));
    });

    // SYSTEM GENERATE MIGRATION FILES
    Route::get('/generate-migrate/{table}', function ($table) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(generateMigration($table));
    });

    // SYSTEM RE-GENERATE MIGRATION FILES
    Route::get('/regenerate-migrate/{table}/{filename}', function ($table, $filename) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(regenerateMigration($filename, $table));
    });

    // SYSTEM GENERATE MODEL  
    Route::get('/generate-model/{table}', function ($table) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(generateModel($table));
    });

    // SYSTEM RE-GENERATE MODEL  
    Route::get('/regenerate-model/{table}/{filename}', function ($table, $filename) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(regenerateModel($filename, $table));
    });

    // SYSTEM TRUNCATE TABLE 
    Route::get('/truncate-table/{table}', function ($table) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(truncateTable($table));
    });

    // SYSTEM BACKUP TABLE 
    Route::get('/backup-table/{table}', function ($table) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(backupTable($table));
    });

    // SYSTEM MIGRATE TABLE (All)
    Route::get('/migrate-all', function () {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(migrateAllTable());
    });

    // SYSTEM MIGRATE TABLE (All)
    Route::get('/migrate-drop-all', function () {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(dropAllTable());
    });

    // SYSTEM MIGRATE TABLE (Single)
    Route::get('/migrate/{filename}', function ($filename) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(migrateTable($filename));
    });

    // SYSTEM DROP TABLE (Single)
    Route::get('/migrate-drop/{filename}', function ($filename) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(dropTable($filename));
    });

    // SYSTEM SEED DATA (Single)
    Route::get('/seed/{filename}', function ($filename) {
        ci()->load->helper('custom_ci3_sysdb');
        jsonResponse(seedTable($filename));
    });
});
