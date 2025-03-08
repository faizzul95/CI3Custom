<?php

// CRON JOB (SERVICES)
// Route::group('/cron', function () {
// 	Route::get('/backup/{upload?}', 'BackupController@BackupDrive');
// });

Route::group('/sys', function () {

    Route::group('admin', ['middleware' => ['Sanctum']], function () {
        // SYSTEM INFORMATION
        Route::get('/phpinfo', function () {
            phpinfo();
        });

        Route::get('/php-info', function () {
            include_once APPPATH . 'libraries/Debug.php';
            $debug = new Debug();
            $debug->phpInfo();
        });

        Route::get('/server-info', function () {
            include_once APPPATH . 'libraries/Debug.php';
            $debug = new Debug();
            $debug->serverInfo();
        });
    });

    // Only run this on development/staging environment
    if (env('ENVIRONMENT') != 'production') {
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
        Route::get('/migrate-drop/{filename}/{backup?}', function ($filename, $backup = false) {
            ci()->load->helper('custom_ci3_sysdb');
            jsonResponse(dropTable($filename, (bool) $backup));
        });

        // SYSTEM SEED DATA (Single)
        Route::get('/seed/{filename}', function ($filename) {
            ci()->load->helper('custom_ci3_sysdb');
            jsonResponse(seedTable($filename));
        });
    }
});

Route::group('superadmin', ['middleware' => ['Sanctum']], function () {
    Route::group('/sys-rbac', function () {
        // Load the page for settings
        Route::get('/', function () {
            render('system/_main_rbac', [
                'title' => 'System Settings',
                'currentSidebar' => 'Settings',
                'currentSubSidebar' => null,
                'permission' => permission(
                    [
                        'rbac-view'
                    ]
                )
            ]);
        });

        // Load the page for roles
        Route::get('/roles-list-pages', function () {
            render('system/list_roles', [
                'title' => 'System Settings',
                'currentSidebar' => 'Settings',
                'currentSubSidebar' => 'Roles',
                'permission' => permission(
                    [
                        'roles-view',
                        'roles-create',
                    ]
                )
            ]);
        });

        // Load the page for menu navigation
        Route::get('/menu-list-pages', function () {
            render('system/list_menu_navigation', [
                'title' => 'System Settings',
                'currentSidebar' => 'Settings',
                'currentSubSidebar' => 'Menu Navigation',
                'permission' => permission(
                    [
                        'menu-navigation-view',
                        'menu-navigation-create',
                    ]
                )
            ]);
        });

        // Load the page for email
        Route::get('/email-list-pages', function () {
            render('system/_email_section', [
                'title' => 'System Settings',
                'currentSidebar' => 'Settings',
                'currentSubSidebar' => 'Email',
                'permission' => permission(
                    [
                        'email-view',
                    ]
                )
            ]);
        });

        // Load the page for developer zone
        Route::get('/developer-list-pages', function () {
            render('system/developer_section', [
                'title' => 'System Settings',
                'currentSidebar' => 'Settings',
                'currentSubSidebar' => 'Developer zone',
                'permission' => null
            ]);
        });
    });
});
