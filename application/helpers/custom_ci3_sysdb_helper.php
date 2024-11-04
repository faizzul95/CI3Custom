<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Lists database system information and all tables with their migration and model files status
 * @return string HTML with database info and table listings
 */
if (!function_exists('listSysDB')) {
    function listSysDB()
    {
        $CI = &get_instance();

        // Add CDN links for required CSS and JS
        $html = '
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <!-- SweetAlert2 -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        <!-- DataTables CSS -->
        <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Bootstrap 5 JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- DataTables -->
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

        <!-- Custom CSS -->
        <style>
            .card { margin-bottom: 1.5rem; }
            .card-header { background-color: #f8f9fa; }
            .table td, .table th { vertical-align: middle; }
            .btn-group .btn { margin-right: 0.25rem; }
            .info-card { transition: all 0.3s ease; }
            .info-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
            .system-info-value { font-weight: bold; color: #0d6efd; }
            .offcanvas { width: 1200px !important; }
            .table-structure-loader {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 200px;
            }
            .structure-title {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 1rem;
                padding-bottom: 0.5rem;
                border-bottom: 2px solid #dee2e6;
            }
            .dataTables_filter {
                margin-bottom: 1rem;
            }
        </style>';

        // Add Offcanvas for Table Structure
        $html .= '
         <div class="offcanvas offcanvas-end custom-offcanvas-width" tabindex="-1" id="tableStructureOffcanvas" aria-labelledby="tableStructureOffcanvasLabel">
             <div class="offcanvas-header">
                 <h5 class="offcanvas-title" id="tableStructureOffcanvasLabel">Table Structure</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
             </div>
             <div class="offcanvas-body">
                 <div class="table-structure-loader">
                     <div class="spinner-border text-primary" role="status">
                         <span class="visually-hidden">Loading...</span>
                     </div>
                 </div>
                 <div id="tableStructureContent"></div>
             </div>
         </div>';

        // Get database configuration
        $db_config = $CI->db->conn_id;
        $db_info = [
            'Database Driver' => $CI->db->dbdriver,
            'Database Name' => $CI->db->database,
            'Database Version' => $CI->db->dbdriver == 'mysqli' ? mysqli_get_server_info($db_config) : 'N/A',
            'Database Host' => $CI->db->hostname,
            'Database Port' => $CI->db->port ?? '3306',
            'Database User' => $CI->db->username,
            'Database Charset' => $CI->db->char_set,
            'Database Collation' => $CI->db->dbcollat
        ];

        // Get all tables from database
        $tables = $CI->db->list_tables();

        // Get all migration files
        $migration_path = APPPATH . 'migrations/';
        $migration_files = glob($migration_path . '*.php');

        // Get all model files
        $model_path = APPPATH . 'models/';
        $model_files = glob($model_path . '*.php');

        // Database System Information Card
        $html .= '<div class="container-fluid py-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-server me-2"></i>Database System Information
                            </h5>
                            <span class="badge bg-primary">' . $CI->db->database . '</span>
                        </div>
                        <div class="card-body">
                            <div class="row">';

        foreach ($db_info as $key => $value) {
            $html .= '<div class="col-md-3 mb-3">
                        <div class="card info-card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>' . $key . '
                                </h6>
                                <p class="card-text system-info-value">' . htmlspecialchars($value) . '</p>
                            </div>
                        </div>
                    </div>';
        }

        $html .= '</div></div></div>';

        // Tables Summary Card
        $html .= '<div class="card shadow-sm mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Database Tables
            </h5>
            <div>
                <span class="badge bg-info me-2">' . count($tables) . ' Tables</span>
                <span class="badge bg-success me-2">' . count($migration_files) . ' Migrations</span>
                <span class="badge bg-warning">' . count($model_files) . ' Models</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="databaseTables" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Table Name</th>
                            <th>Columns</th>
                            <th>Rows</th>
                            <th>Storage (MB)</th>
                            <th>Migration File</th>
                            <th>Model File</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($tables as $table) {

            if (in_array($table, ['system_migrations', 'migrations'])) {
                continue; // skip for the backup table
            }

            $canGenerateMigration = $canGenerateMModel = $canBackupTable = true;

            // Check if the table name is actually is backup, then removed the migration and model creation
            if (strpos($table, 'backup') !== false || strpos($table, 'bck') !== false || strpos($table, 'back') !== false || strpos($table, 'bak') !== false) {
                continue; // skip for the backup table
                $canGenerateMigration = $canGenerateMModel = $canBackupTable = false;
            }

            $migration_file = findMigrationFile($migration_files, $table);
            $model_file = findModelFile($model_files, $table);

            // Get total rows
            $total_rows = $CI->db->count_all($table);

            // Get total columns
            $fields = $CI->db->field_data($table);
            $total_columns = count($fields);

            $size = $CI->db->query("SELECT 
                    round(((data_length + index_length) / 1024 / 1024), 2) AS size_mb 
                    FROM information_schema.TABLES 
                    WHERE table_schema = '{$CI->db->database}' 
                    AND table_name = '{$table}'")->row()->size_mb;

            // Check if the table name is actually is backup, then removed the migration and model creation
            if (strpos($table, 'backup') !== false || strpos($table, 'bck') !== false || strpos($table, 'back') !== false || strpos($table, 'bak') !== false) {
                $canGenerateMigration = $canGenerateMModel = $canBackupTable = false;
            }

            $html .= '<tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-database me-2 text-primary"></i>
                                <span>' . $table . '</span>
                            </div>
                        </td>
                        <td>' . $total_columns . '</td>
                        <td>' . number_format($total_rows) . '</td>
                        <td>' . number_format($size) . '</td>
                        <td>' . ($migration_file ?
                '<div class="d-flex align-items-center justify-content-between">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>' . basename($migration_file) . '</span>
                                <button class="btn btn-sm btn-warning ms-2 regenerate-migration" 
                                        data-table="' . $table . '" 
                                        data-file="' . basename($migration_file) . '" 
                                        title="Regenerate Migration">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>' :
                '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Not created</span>') .
                '</td>
                        <td>' . ($model_file ?
                    '<div class="d-flex align-items-center justify-content-between">
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i>' . basename($model_file) . '</span>
                                    <button class="btn btn-sm btn-warning ms-2 regenerate-model" 
                                            data-table="' . $table . '" 
                                            data-file="' . basename($model_file) . '" 
                                            title="Regenerate Model">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>' :
                    '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Not created</span>') .
                '</td>
                        <td>
                            <div class="btn-group" role="group">
                                ' . (!$migration_file && $canGenerateMigration ?
                    '<button class="btn btn-sm btn-primary create-migration" data-table="' . $table . '" title="Create Migration">
                                        <i class="fas fa-file-code"></i>
                                    </button>' : '') . '
                                ' . (!$model_file && $canGenerateMModel ?
                    '<button class="btn btn-sm btn-success create-model" data-table="' . $table . '" title="Create Model">
                                        <i class="fas fa-code"></i>
                                    </button>' : '') . '
                                <button class="btn btn-sm btn-info text-white view-structure" data-table="' . $table . '" title="View Structure">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ' . ($total_rows > 0 && $canBackupTable ?
                    '<button class="btn btn-sm btn-danger truncate-table" data-table="' . $table . '" title="Truncate Table">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>' : '') . '
                                     ' . ($total_rows > 0 && $canBackupTable ?
                    '<button class="btn btn-sm btn-secondary backup-table" data-table="' . $table . '" title="Backup Table">
                                    <i class="fas fa-download"></i>
                                </button>' : '') . '
                            </div>
                        </td>
                    </tr>';
        }

        $html .= '</tbody></table>
                </div>
            </div>
        </div>';

        // Migration Information Card
        $html .= '<div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code-branch me-2"></i>Migration Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card info-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="fas fa-file-code me-2"></i>Total Migration Files
                                        </h6>
                                        <p class="card-text system-info-value">' . count($migration_files) . '</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card info-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="fas fa-cog me-2"></i>Migration Type
                                        </h6>
                                        <p class="card-text system-info-value">' . ucfirst($CI->config->item('migration_type')) . '</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card info-card h-100">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="fas fa-folder me-2"></i>Migration Path
                                        </h6>
                                        <p class="card-text system-info-value text-truncate" title="' . $migration_path . '">' . $migration_path . '</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';

        // Add JavaScript for handling actions
        $html .= '
        <script>
            $(document).ready(function() {
                const baseUrl = "' . base_url() . '";

                // Initialize DataTable
                $("#databaseTables").DataTable({
                    pageLength: 25,
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    order: [[0, "asc"]],
                    columnDefs: [
                        {
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }
                    ]
                });

                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[title]\'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Handle migration creation
                $(".create-migration").click(function() {
                    var table = $(this).data("table");
                    Swal.fire({
                        title: "Create Migration",
                        text: `Do you want to create a migration file for table "${table}"?`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, create it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get(baseUrl + `sys/generate-migrate/${table}`, function(response) {
                                if (response.code === 200) {
                                    Swal.fire(
                                        "Created!",
                                        response.message || "Migration file has been created.",
                                        "success"
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        "Error!",
                                        response.message || "Failed to create migration file.",
                                        "error"
                                    );
                                }
                            }).fail(function(error) {
                                Swal.fire(
                                    "Error!",
                                    "Failed to create migration file.",
                                    "error"
                                );
                            });
                        }
                    });
                });

                // Handle model creation
                $(".create-model").click(function() {
                    var table = $(this).data("table");
                    Swal.fire({
                        title: "Create Model",
                        text: `Do you want to create a model file for table "${table}"?`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, create it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get(baseUrl + `sys/generate-model/${table}`, function(response) {
                                if (response.code === 200) {
                                    Swal.fire(
                                        "Created!",
                                        response.message || "Model file has been created.",
                                        "success"
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        "Error!",
                                        response.message || "Failed to create Model file.",
                                        "error"
                                    );
                                }
                            }).fail(function(error) {
                                Swal.fire(
                                    "Error!",
                                    "Failed to create Model file.",
                                    "error"
                                );
                            });
                        }
                    });
                });

                // Handle view structure with Offcanvas
                $(".view-structure").click(function() {
                    var table = $(this).data("table");
                    var offcanvas = new bootstrap.Offcanvas(document.getElementById("tableStructureOffcanvas"));

                    // Show offcanvas and loader
                    offcanvas.show();
                    $(".table-structure-loader").show();
                    $("#tableStructureContent").hide();

                    // Update offcanvas title
                    $("#tableStructureOffcanvasLabel").text(`Structure of "${table}"`);

                    // Fetch table structure
                    $.get(baseUrl + `sys/table-structure/${table}`, function(response) {
                        if (response.code === 200) {
                            $(".table-structure-loader").hide();
                            $("#tableStructureContent")
                                .html(response.data)
                                .show();
                        } else {
                            $(".table-structure-loader").hide();
                            $("#tableStructureContent")
                                .html(`<div class="alert alert-danger">Failed to load table structure</div>`)
                                .show();
                        }
                    }).fail(function() {
                        $(".table-structure-loader").hide();
                        $("#tableStructureContent")
                            .html(`<div class="alert alert-danger">Failed to load table structure</div>`)
                            .show();
                    });
                });

                // Handle regenerate migration
                $(".regenerate-migration").click(function() {
                    var table = $(this).data("table");
                    var filename = $(this).data("file");
                    Swal.fire({
                        title: "Regenerate Migration",
                        text: `Do you want to regenerate the migration file for table "${table}"?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#ffc107",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, regenerate it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get(baseUrl + `sys/regenerate-migrate/${table}/${filename}`, function(response) {
                                if (response.code === 200) {
                                    Swal.fire(
                                        "Regenerated!",
                                        response.message || "Migration file has been regenerated.",
                                        "success"
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        "Error!",
                                        response.message || "Failed to regenerate migration file.",
                                        "error"
                                    );
                                }
                            }).fail(function(error) {
                                Swal.fire(
                                    "Error!",
                                    "Failed to regenerate migration file.",
                                    "error"
                                );
                            });
                        }
                    });
                });

                // Handle regenerate model
                $(".regenerate-model").click(function() {
                    var table = $(this).data("table");
                    var filename = $(this).data("file");
                    Swal.fire({
                        title: "Regenerate Model",
                        text: `Do you want to regenerate the model file for table "${table}"?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#ffc107",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, regenerate it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get(baseUrl + `sys/regenerate-model/${table}/${filename}`, function(response) {
                                if (response.code === 200) {
                                    Swal.fire(
                                        "Regenerated!",
                                        response.message || "Model file has been regenerated.",
                                        "success"
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        "Error!",
                                        response.message || "Failed to regenerate model file.",
                                        "error"
                                    );
                                }
                            }).fail(function(error) {
                                Swal.fire(
                                    "Error!",
                                    "Failed to regenerate model file.",
                                    "error"
                                );
                            });
                        }
                    });
                });

                // Handle truncate table
                $(".truncate-table").click(function() {
                    var table = $(this).data("table");
                    Swal.fire({
                        title: "Truncate Table",
                        text: `Are you sure you want to truncate table "${table}"? This will remove all data!`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, truncate it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get(baseUrl + `sys/truncate-table/${table}`, function(response) {
                                if (response.code === 200) {
                                    Swal.fire(
                                        "Truncated!",
                                        response.message || "Table has been truncated.",
                                        "success"
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        "Error!",
                                        response.message || "Failed to truncate table.",
                                        "error"
                                    );
                                }
                            }).fail(function(error) {
                                Swal.fire(
                                    "Error!",
                                    "Failed to truncate table.",
                                    "error"
                                );
                            });
                        }
                    });
                });

                // Handle backup table
                $(".backup-table").click(function() {
                    var table = $(this).data("table");
                    Swal.fire({
                        title: "Backup Table",
                        text: `Do you want to backup table "${table}"?`,
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, backup it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get(baseUrl + `sys/backup-table/${table}`, function(response) {
                                if (response.code === 200) {
                                    Swal.fire(
                                        "Backup!",
                                        response.message || "Table has been Backup",
                                        "success"
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        "Error!",
                                        response.message || "Failed to backup table.",
                                        "error"
                                    );
                                }
                            }).fail(function(error) {
                            console.log(error.responseJSON.message);
                                Swal.fire(
                                    "Error!",
                                    error.responseJSON.message || "Failed to backup table.",
                                    "error"
                                );
                            });
                        }
                    });
                });

            });
        </script>';

        return $html;
    }
}

if (!function_exists('listSysMigration')) {
    function listSysMigration()
    {
        $CI = &get_instance();

        // Keep existing CDN links and CSS
        $html = '
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <!-- SweetAlert2 -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        <!-- DataTables CSS -->
        <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Bootstrap 5 JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- DataTables -->
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

        <style>
            .card { margin-bottom: 1.5rem; }
            .card-header { background-color: #f8f9fa; }
            .table td, .table th { vertical-align: middle; }
            .btn-group .btn { margin-right: 0.25rem; }
            .info-card { transition: all 0.3s ease; }
            .info-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
            .system-info-value { font-weight: bold; color: #0d6efd; }
            .migration-actions .btn { margin: 0 0.25rem; }
            .status-badge { font-size: 0.875rem; }
            .status-exists { background-color: #198754; }
            .status-not-exists { background-color: #dc3545; }
        </style>';

        // Get database configuration
        $db_config = $CI->db->conn_id;
        $db_info = [
            'Database Driver' => $CI->db->dbdriver,
            'Database Name' => $CI->db->database,
            'Database Version' => $CI->db->dbdriver == 'mysqli' ? mysqli_get_server_info($db_config) : 'N/A',
            'Database Host' => $CI->db->hostname,
            'Database Port' => $CI->db->port ?? '3306',
            'Database User' => $CI->db->username,
            'Database Charset' => $CI->db->char_set,
            'Database Collation' => $CI->db->dbcollat
        ];

        // Database System Information Card
        $html .= '<div class="container-fluid py-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-server me-2"></i>Database System Information
                            </h5>
                            <span class="badge bg-primary">' . $CI->db->database . '</span>
                        </div>
                        <div class="card-body">
                            <div class="row">';

        foreach ($db_info as $key => $value) {
            $html .= '<div class="col-md-3 mb-3">
                        <div class="card info-card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>' . $key . '
                                </h6>
                                <p class="card-text system-info-value">' . htmlspecialchars($value) . '</p>
                            </div>
                        </div>
                    </div>';
        }

        $html .= '</div></div></div>';

        // Migrations Card
        $html .= '<div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-database me-2"></i>Database Migrations
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-success me-2" onclick="migrateAll()">
                                <i class="fas fa-play me-2"></i>Migrate All
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="dropAll()">
                                <i class="fas fa-trash me-2"></i>Drop All Tables
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="migrationsTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                            <thead class="table-dark"">
                                <tr>
                                    <th>File Name</th>
                                    <th>Table</th>
                                    <th width="8%">Status</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>';

        // Get all tables from database
        $existing_tables = $CI->db->list_tables();

        // Get all migration files
        $migration_path = APPPATH . 'migrations/';
        $migration_files = glob($migration_path . '*.php');

        $CI->load->library('migration');

        // Process migration files to extract table information
        $migrations = [];
        $statusBadge = [
            0 => '<i class="fas fa-times-circle" style="color: red;"></i>',
            1 => '<i class="fas fa-check-circle" style="color: green;"></i>',
        ];

        foreach ($migration_files as $file) {
            $filename = basename($file);

            // Load the migration file to get the table name
            require_once $file;

            $class_name = 'Migration_' . substr($filename, 4, -4);
            $migration = new $class_name();
            $table_name = $migration->table_name;

            $seeder = method_exists($migration, 'seeder') ? true : false;
            if ($seeder && in_array($table_name, $existing_tables)) {
                if ($CI->db->count_all_results($table_name) > 0) {
                    $seeder = false;
                }
            }

            $migrations[] = [
                'file' => $filename,
                'table' => $table_name,
                'seeder' => $seeder,
                'exists' => in_array($table_name, $existing_tables),
                'badge' => !in_array($table_name, $existing_tables) ? $statusBadge[0] : $statusBadge[1]
            ];
        }

        foreach ($migrations as $migrate) {
            $html .= '<tr>
                        <td>' . $migrate['file'] . '</td>
                        <td>' . $migrate['table'] . '</td>
                        <td class="text-center">
                            ' . $migrate['badge'] . '
                        </td>
                        <td class="text-center">';

            // Show different buttons based on table existence
            if (!$migrate['exists']) {
                $html .= '<button class="btn btn-sm btn-primary me-1" onclick="migrateSingle(\'' . $migrate['file'] . '\')">
                            <i class="fas fa-play"></i> Migrate
                        </button>';
            } else {

                if ($migrate['seeder']) {
                    $html .= '<button class="btn btn-sm btn-info me-2" onclick="seederSingle(\'' . $migrate['file'] . '\')">
                                    <i class="fas fa-plus"></i> Seeder
                                </button>';
                }

                $html .= '<button class="btn btn-sm btn-warning me-1" onclick="remigrate(\'' . $migrate['file'] . '\')">
                            <i class="fas fa-redo"></i> Re-migrate
                        </button>
                        <button class="btn btn-sm btn-danger me-1" onclick="dropTable(\'' . $migrate['file'] . '\')">
                            <i class="fas fa-trash"></i>
                        </button>';
            }

            $html .= '</td></tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        // Add JavaScript for handling migration actions
        $html .= "
        <script>
            $(document).ready(function() {
                $('#migrationsTable').DataTable({
                    responsive: true,
                    order: [[0, 'asc']]
                });
            });

            const baseUrl = '" . base_url() . "';

            function migrateSingle(filename) {
                Swal.fire({
                    title: 'Confirm Migration',
                    text: 'Are you sure you want to migrate ' + filename + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, migrate it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get(baseUrl + 'sys/migrate/' + filename)
                            .done(function(response) {
                                Swal.fire('Success!', 'Migration completed successfully.', 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(error) {
                                Swal.fire('Error!', 'Migration failed: ' + error.responseText, 'error');
                            });
                    }
                });
            }

            function seederSingle(filename) {
                Swal.fire({
                    title: 'Confirm Seeder',
                    text: 'Are you sure you want to seed ' + filename + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, seed it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get(baseUrl + 'sys/seed/' + filename)
                            .done(function(response) {
                                Swal.fire('Success!', 'Seeder completed successfully.', 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(error) {
                                Swal.fire('Error!', 'Seeder failed: ' + error.responseText, 'error');
                            });
                    }
                });
            }

            function remigrate(filename) {
                Swal.fire({
                    title: 'Confirm Re-migration',
                    text: 'This will drop and re-create the table. Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, re-migrate!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // First drop then migrate
                        $.get(baseUrl + 'sys/migrate-drop/' + filename)
                            .done(function() {
                                return $.get(baseUrl + 'sys/migrate/' + filename);
                            })
                            .done(function(response) {
                                Swal.fire('Success!', 'Re-migration completed successfully.', 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(error) {
                                Swal.fire('Error!', 'Re-migration failed: ' + error.responseText, 'error');
                            });
                    }
                });
            }

            function dropTable(filename) {
                Swal.fire({
                    title: 'Confirm Table Drop',
                    text: 'Are you sure you want to drop this table?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, drop it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get(baseUrl + 'sys/migrate-drop/' + filename)
                            .done(function(response) {
                                Swal.fire('Success!', 'Table dropped successfully.', 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(error) {
                                Swal.fire('Error!', 'Failed to drop table: ' + error.responseText, 'error');
                            });
                    }
                });
            }

            function migrateAll() {
                Swal.fire({
                    title: 'Confirm All Migrations',
                    text: 'Are you sure you want to run all pending migrations?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, migrate all!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get(baseUrl + 'sys/migrate-all')
                            .done(function(response) {
                                Swal.fire('Success!', 'All migrations completed successfully.', 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(error) {
                                Swal.fire('Error!', 'Migration failed: ' + error.responseText, 'error');
                            });
                    }
                });
            }

            function dropAll() {
                Swal.fire({
                    title: 'Confirm Drop All Tables',
                    text: 'This will drop all tables in the database. Are you sure?',
                    icon: 'warning',
                    input: 'text',
                    inputPlaceholder: 'Type CONFIRM to proceed',
                    inputValidator: (value) => {
                        if (value !== 'CONFIRM') {
                            return 'You need to type CONFIRM to proceed'
                        }
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Yes, drop all!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get(baseUrl + 'sys/migrate-drop-all')
                            .done(function(response) {
                                Swal.fire('Success!', 'All tables dropped successfully.', 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(error) {
                                Swal.fire('Error!', 'Failed to drop tables: ' + error.responseText, 'error');
                            });
                    }
                });
            }
        </script>";

        return $html;
    }
}

/**
 * Finds migration file for a specific table
 * @param array $files Array of migration files
 * @param string $table Table name
 * @return string|null Path to migration file if found, null otherwise
 */
if (!function_exists('findMigrationFile')) {
    function findMigrationFile($files, $table)
    {
        $CI = &get_instance();

        // Load migration configuration
        $CI->load->config('migration');
        $type = $CI->config->item('migration_type');

        foreach ($files as $file) {
            $filename = basename($file);

            if ($type === 'sequential') {
                // For sequential migrations, format is: XXX_create_table_name.php
                if (preg_match('/^\d{3}_create_' . preg_quote($table) . '\.php$/', $filename)) {
                    return $file;
                }
            } else {
                // For timestamp migrations, format is: YYYYMMDDHHMMSS_create_table_name.php
                if (preg_match('/^\d{14}_create_' . preg_quote($table) . '\.php$/', $filename)) {
                    return $file;
                }
            }
        }

        return null;
    }
}

/**
 * Finds model file for a specific table
 * @param array $files Array of model files
 * @param string $table Table name
 * @return string|null Path to model file if found, null otherwise
 */
if (!function_exists('findModelFile')) {
    function findModelFile($files, $table)
    {
        // Convert table name to singular and capitalize first letter
        $model_name = str_replace(' ', '', ucwords(str_replace('_', ' ', rtrim($table, 's'))));

        foreach ($files as $file) {
            $filename = basename($file);

            // Model filename should match: TableName_model.php
            if (strtolower($filename) === strtolower($model_name . '_model.php')) {
                return $file;
            }
        }

        return null;
    }
}

/**
 * Generates a CodeIgniter 3 model file based on table structure
 * 
 * @param string $table Database table name
 * @return bool|string Returns generated code string or false on failure
 */
if (!function_exists('generateModel')) {
    function generateModel($table)
    {
        $CI = &get_instance();

        try {
            // Get table fields
            $fields = $CI->db->field_data($table);
            if (empty($fields)) {
                return false;
            }

            // Detect primary key
            $pk = 'id';
            foreach ($fields as $field) {
                if ($field->primary_key) {
                    $pk = $field->name;
                    break;
                }
            }

            // Set model name based on table name. Convert table name to singular and capitalize first letter
            // $modelName = ucfirst(rtrim($table, 's'));
            $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', rtrim($table, 's'))));
            $className = "{$modelName}_model";
            $classExtend = 'MY';

            // Exclude these fields from fillable and validation
            $excludeFields = ['id', 'created_at', 'updated_at', 'deleted_at'];

            // Prepare fillable fields
            $fillable = [];
            foreach ($fields as $field) {
                if (!in_array($field->name, $excludeFields)) {
                    $fillable[] = $field->name;
                }
            }

            // Prepare validation rules
            $validation = [];
            foreach ($fields as $field) {
                if ($field->name === $pk || in_array($field->name, ['created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }

                $rules = ['required', 'trim'];
                $label = ucwords(str_replace('_', ' ', $field->name));

                // Add type-specific rules
                switch ($field->type) {
                    case 'int':
                    case 'bigint':
                        $rules[] = 'integer';
                        break;
                    case 'decimal':
                    case 'float':
                    case 'double':
                        $rules[] = 'numeric';
                        break;
                    case 'varchar':
                    case 'text':
                        if ($field->max_length) {
                            $rules[] = "max_length[{$field->max_length}]";
                        } else {
                            $rules[] = "max_length[255]";
                        }
                        break;
                    case 'date':
                        $rules[] = 'regex_match[/^\d{4}-\d{2}-\d{2}$/]';  // YYYY-MM-DD
                        break;
                    case 'datetime':
                    case 'timestamp':
                        $rules[] = 'regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]';  // YYYY-MM-DD HH:MM:SS
                        break;
                    case 'email':
                        $rules[] = 'valid_email';
                        break;
                    case 'url':
                    case 'uri':
                        $rules[] = 'valid_url';
                        break;
                    case 'boolean':
                        $rules[] = 'in_list[0,1]';
                        break;
                }

                $validation[$field->name] = [
                    'field' => $field->name,
                    'label' => $label,
                    'rules' => implode('|', $rules)
                ];
            }

            // Generate model content
            $modelContent = "<?php\n\n";
            $modelContent .= "defined('BASEPATH') or exit('No direct script access allowed');\n\n";
            $modelContent .= "class {$className} extends {$classExtend}_Model\n{\n";
            $modelContent .= "    public \$table = '{$table}';\n";
            $modelContent .= "    public \$primaryKey = '{$pk}';\n\n";

            // Add fillable fields
            $modelContent .= "    public \$fillable = [\n";
            $fillableCount = count($fillable);
            for ($i = 0; $i < $fillableCount; $i++) {
                $line = "        '{$fillable[$i]}'";
                $modelContent .= $i < $fillableCount - 1 ? $line . ",\n" : $line . "\n";
            }
            $modelContent .= "    ];\n\n";

            // Add validation rules with inline formatting
            $modelContent .= "    public \$_validationRules = [\n";
            $validationCount = count($validation);
            $i = 0;
            foreach ($validation as $field => $rules) {
                $i++;
                $line = "        '{$field}' => ['field' => '{$rules['field']}', 'label' => '{$rules['label']}', 'rules' => '{$rules['rules']}']";
                $modelContent .= $i < $validationCount ? $line . ",\n" : $line . "\n";
            }
            $modelContent .= "    ];\n\n";

            // Add constructor
            $modelContent .= "    function __construct()\n";
            $modelContent .= "    {\n";
            $modelContent .= "        parent::__construct();\n";
            $modelContent .= "    }\n";
            $modelContent .= "}\n";

            // return $modelContent;
            // Write file
            if (file_put_contents(APPPATH . "models/{$className}.php", $modelContent)) {
                return ['code' => 200, 'message' => 'Model file created successfully: ' . $className];
            } else {
                return ['code' => 500, 'message' => 'Failed to write migration file'];
            }
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error generating model: ' . $e->getMessage()];
        }
    }
}

/**
 * Regenerate a model file by backing up the existing one and creating a new version
 * 
 * @param string $fileName The name of the model file to regenerate
 * @param string $table The database table name
 * @return array Status array with code and message
 */
if (!function_exists('regenerateModel')) {
    function regenerateModel($fileName, $table)
    {
        try {
            // Check if file exists
            $modelPath = APPPATH . "models/{$fileName}";
            if (!file_exists($modelPath)) {
                return ['code' => 404, 'message' => "Model file not found: {$fileName}"];
            }

            // Create backup
            $backupPath = APPPATH . "models/backups";
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0664, true);
            }

            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $backupPath . '/' . pathinfo($fileName, PATHINFO_FILENAME) . "_{$timestamp}.php";

            if (!copy($modelPath, $backupFile)) {
                return ['code' => 500, 'message' => 'Failed to create backup of model file'];
            }

            // Delete existing model file
            unlink($modelPath);

            // Generate new model
            $result = generateModel($table);
            if ($result['code'] !== 200) {
                // Restore from backup if generation fails
                copy($backupFile, $modelPath);
                return ['code' => 500, 'message' => 'Failed to regenerate model: ' . $result['message']];
            }

            return [
                'code' => 200,
                'message' => 'Model regenerated successfully',
                'backup' => $backupFile
            ];
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error regenerating model: ' . $e->getMessage()];
        }
    }
}

/**
 * Generate migration file for a table
 * @param string $table Table name
 * @return array Status and message
 */
if (!function_exists('generateMigration')) {
    function generateMigration($table, $filename = null)
    {
        $CI = &get_instance();

        try {
            // Load migration configuration
            $CI->load->config('migration');
            $migration_path = APPPATH . 'migrations/';

            if (!is_dir($migration_path)) {
                mkdir($migration_path, 0664, true);
            }

            $migration_type = $CI->config->item('migration_type');

            if (empty($filename)) {
                // Generate migration filename
                $prefix = ($migration_type === 'timestamp') ? date('YmdHis') : sprintf("%03d", getNextMigrationNumber($migration_path));
                $filename = $prefix . '_create_' . $table . '.php';
            }

            $filepath = $migration_path . $filename;

            // Get table structure
            $fields = $CI->db->field_data($table);
            $indexes = $CI->db->query("SHOW INDEXES FROM `{$table}`")->result();

            // Start building migration content
            $content = "<?php \n\n defined('BASEPATH') OR exit('No direct script access allowed');\n\n";
            $content .= "class Migration_create_" . $table . " extends CI_Migration {\n\n";
            $content .= "\tpublic function __construct() {\n";
            $content .= "\t\tparent::__construct();\n";
            $content .= "\t\t\$this->load->dbforge();\n";
            $content .= "\t\t\$this->table_name = '$table';\n";
            $content .= "\t}\n\n";

            // Up method
            $content .= "\tpublic function up() {\n";
            $content .= "\t\t\$this->dbforge->add_field([\n";

            // Add fields inline
            foreach ($fields as $field) {
                $content .= "\t\t\t'" . $field->name . "' => ['type' => '" . strtoupper($field->type) . "'";
                if ($field->primary_key) $content .= ", 'unsigned' => TRUE, 'auto_increment' => TRUE";
                if ($field->max_length) $content .= ", 'constraint' => " . $field->max_length;
                if ($field->default !== null) $content .= ", 'default' => '" . $field->default . "'";
                if (!$field->primary_key) $content .= ", 'null' => TRUE";
                $content .= ", 'comment' => ''],\n";
            }
            $content .= "\t\t]);\n\n";

            // Add primary key
            $primaryKey = 'id';
            foreach ($fields as $field) {
                if ($field->primary_key) {
                    $primaryKey = $field->name;
                    $content .= "\t\t\$this->dbforge->add_key('" . $field->name . "', TRUE);\n";
                }
            }

            // Add indexes
            $processed_indexes = [];
            foreach ($indexes as $index) {
                if ($index->Key_name !== 'PRIMARY' && !in_array($index->Key_name, $processed_indexes)) {
                    $content .= "\t\t\$this->dbforge->add_key('" . $index->Column_name . "');\n";
                    $processed_indexes[] = $index->Key_name;
                }
            }

            $content .= "\n\t\t\$this->dbforge->create_table(\$this->table_name, FALSE, ['ENGINE' => 'InnoDB', 'COLLATE' => 'utf8mb4_general_ci']);\n";
            $content .= "\t}\n\n";

            // Down method
            $content .= "\tpublic function down() {\n";
            $content .= "\t\t\$this->dbforge->drop_table(\$this->table_name, TRUE);\n";
            $content .= "\t}\n";

            // Check if the table has data
            $data_count = $CI->db->count_all($table);

            // Add seeder method if there is data
            if ($data_count > 0) {
                // Fetch data from the table
                $data = $CI->db->limit(2000)->get($table)->result_array();

                $content .= "\n\tpublic function seeder() {\n";
                $content .= "\t\t\$data = [\n";

                foreach ($data as $index => $row) {
                    $content .= "\t\t\t[\n";
                    foreach ($row as $column => $value) {
                        if (in_array($column, ['updated_at', 'deleted_at'])) {
                            continue; // Skip updated_at and deleted_at
                        }

                        // Ensure that string values are quoted
                        if (is_string($value)) {
                            $value = $CI->db->escape($value);
                        } else {
                            $value = $value; // Leave numbers unquoted
                        }

                        $content .= "\t\t\t\t'$column' => $value,\n";
                    }
                    $content .= "\t\t\t]";
                    // Add a comma only if this is not the last row
                    if ($index < count($data) - 1) {
                        $content .= ",";
                    }
                    $content .= "\n";
                }

                $content .= "\t\t];\n\n";
                $content .= "\t\t\$this->db->insert_batch(\$this->table_name, \$data);\n";
                $content .= "\t}\n";
            }

            $content .= "}\n";

            // Write file
            if (file_put_contents($filepath, $content)) {
                return [
                    'code' => 200,
                    'message' => 'Migration file created successfully: ' . $filename
                ];
            } else {
                return [
                    'code' => 500,
                    'message' => 'Failed to write migration file'
                ];
            }
        } catch (Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error generating migration: ' . $e->getMessage()
            ];
        }
    }

    function getNextMigrationNumber($migration_path)
    {
        $migrations = glob($migration_path . '*.php');
        $next_num = 1;

        if (!empty($migrations)) {
            $numbers = array_map(function ($file) {
                return intval(basename($file, '.php'));
            }, $migrations);
            $next_num = max($numbers) + 1;
        }

        return $next_num;
    }
}

/**
 * Regenerate a migration file by backing up the existing one and creating a new version
 * 
 * @param string $fileName The name of the migration file to regenerate
 * @param string $table The database table name
 * @return array Status array with code and message
 */
if (!function_exists('regenerateMigration')) {
    function regenerateMigration($fileName, $table)
    {
        try {
            // Check if file exists
            $migrationPath = APPPATH . "migrations/{$fileName}";
            if (!file_exists($migrationPath)) {
                return ['code' => 404, 'message' => "Migration file not found: {$fileName}"];
            }

            // Create backup
            $backupPath = APPPATH . "migrations/backups";
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0664, true);
            }

            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $backupPath . '/' . pathinfo($fileName, PATHINFO_FILENAME) . "_{$timestamp}.php";

            if (!copy($migrationPath, $backupFile)) {
                return ['code' => 500, 'message' => 'Failed to create backup of migration file'];
            }

            // Delete existing migration file
            unlink($migrationPath);

            // Generate new migration
            $result = generateMigration($table, $fileName);
            if ($result['code'] !== 200) {
                // Restore from backup if generation fails
                copy($backupFile, $migrationPath);
                return ['code' => 500, 'message' => 'Failed to regenerate migration: ' . $result['message']];
            }

            return [
                'code' => 200,
                'message' => 'Migration regenerated successfully',
                'backup' => $backupFile
            ];
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error regenerating migration: ' . $e->getMessage()];
        }
    }
}

/**
 * Get table structure details
 * @param string $table Table name
 * @return string HTML formatted table structure
 */
if (!function_exists('getTableStructure')) {
    function getTableStructure($table)
    {
        $CI = &get_instance();

        try {
            // Get table fields
            // $fields = $CI->db->field_data($table);
            $sql = "SHOW FULL COLUMNS FROM $table";
            $query = $CI->db->query($sql);
            $fields = $query->result();

            // Get table indexes
            $indexes = [];
            $index_query = $CI->db->query("SHOW INDEXES FROM `{$table}`");
            if ($index_query) {
                $indexes = $index_query->result();
            }

            // Get foreign keys if using MySQL/MariaDB
            $foreign_keys = [];
            if ($CI->db->dbdriver == 'mysqli') {
                $fk_query = $CI->db->query("
                    SELECT 
                        COLUMN_NAME as 'column',
                        REFERENCED_TABLE_NAME as 'ref_table',
                        REFERENCED_COLUMN_NAME as 'ref_column'
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = '{$table}'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                if ($fk_query) {
                    $foreign_keys = $fk_query->result();
                }
            }

            // Build HTML structure
            $html = '<div class="structure-title">Table: ' . $table . '</div>';

            // Fields section
            $html .= '<div class="mb-3">
                        <h6 class="mb-3"><i class="fas fa-columns me-2"></i>Columns</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Column</th>
                                        <th>Type</th>
                                        <th>Primary</th>
                                        <th>Null</th>
                                        <th>Default</th>
                                        <th>Comment</th>
                                        <th>Extra</th>
                                    </tr>
                                </thead>
                                <tbody>';
            $no = 1;
            foreach ($fields as $field) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $field->Field . '</td>
                            <td>' . $field->Type . '</td>
                            <td>' . ($field->Key ? '<i class="fas fa-check text-success"></i>' : '-') . '</td>
                            <td>' . ($field->Null ? 'YES' : 'NO') . '</td>
                            <td>' . ($field->Default ?? 'NULL') . '</td>
                            <td>' . ($field->Comment ?? '-') . '</td>
                            <td>' . ($field->Extra ?? '-') . '</td>
                        </tr>';
                $no++;
            }

            $html .= '</tbody></table></div></div>';

            // Indexes section
            if (!empty($indexes)) {
                $html .= '<div class="mb-4">
                            <h6 class="mb-3"><i class="fas fa-key me-2"></i>Indexes</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Column</th>
                                            <th>Type</th>
                                            <th>Unique</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                foreach ($indexes as $index) {
                    $html .= '<tr>
                                <td>' . $index->Key_name . '</td>
                                <td>' . $index->Column_name . '</td>
                                <td>' . ($index->Index_type ?? '-') . '</td>
                                <td>' . (!$index->Non_unique ? '<i class="fas fa-check text-success"></i>' : '-') . '</td>
                            </tr>';
                }

                $html .= '</tbody></table></div></div>';
            }

            // Foreign Keys section
            if (!empty($foreign_keys)) {
                $html .= '<div class="mb-4">
                            <h6 class="mb-3"><i class="fas fa-link me-2"></i>Foreign Keys</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Column</th>
                                            <th>References</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                foreach ($foreign_keys as $fk) {
                    $html .= '<tr>
                                <td>' . $fk->column . '</td>
                                <td>' . $fk->ref_table . '(' . $fk->ref_column . ')</td>
                            </tr>';
                }

                $html .= '</tbody></table></div></div>';
            }

            return ['code' => 200, 'data' => $html];
        } catch (Exception $e) {
            return ['code' => 500, 'message' => $e->getMessage()];
        }
    }
}

/**
 * Truncates a specified table.
 *
 * This function removes all rows from the specified table.
 *
 * @param string $table The name of the table to truncate.
 *
 * @return array Returns an array with 'code' and 'message' keys:
 *   - `code`: 200 for success, 500 for failure.
 *   - `message`: Success or error message.
 */
if (!function_exists('truncateTable')) {
    function truncateTable($table)
    {
        $CI = &get_instance();

        try {
            $CI->load->database(); // Load the database library
            if ($CI->db->query("TRUNCATE TABLE `" . $table . "`")) {
                return ['code' => 200, 'message' => 'Table ' . $table . ' truncated successfully'];
            } else {
                return ['code' => 500, 'message' => 'Failed to truncate table ' . $table];
            }
        } catch (Exception $e) {
            return ['code' => 500, 'message' => $e->getMessage()];
        }
    }
}

/**
 * Creates a backup of a database table or generates SQL files and zips them.
 *
 * @param string $table The name of the table to backup or the database name to generate SQL files for.
 * @param string $type The type of backup: 'table' (default) for table backup, 'files' for SQL file generation and zipping.
 * @return array Status array with code, message, and additional information.
 */
if (!function_exists('backupTable')) {
    function backupTable($table, $type = 'files')
    {
        $CI = &get_instance();

        try {
            // Get the size of the backup table
            $size = $CI->db->query("SELECT 
             round(((data_length + index_length) / 1024 / 1024), 2) AS size_mb 
             FROM information_schema.TABLES 
             WHERE table_schema = '{$CI->db->database}' 
             AND table_name = '{$table}'")->row()->size_mb;

            if ($size >= 100) {
                return ['code' => 400, 'message' => 'Table too big to backup using this function.'];
            }

            if ($type === 'table') {
                // Generate backup table name
                $timestamp = date('Ymd_His');
                $backupTable = "{$table}_backup_{$timestamp}";

                // Create backup table
                $CI->db->query("CREATE TABLE {$backupTable} LIKE {$table}");
                $CI->db->query("INSERT INTO {$backupTable} SELECT * FROM {$table}");

                return [
                    'code' => 200,
                    'message' => 'Table backup ' . $backupTable . ' (size: ' . $size . 'MB) created successfully',
                    'backup_table' => $backupTable,
                    'size_mb' => $size,
                ];
            } else if ($type === 'files') {

                $backupPath = APPPATH . "database/backup/";
                if (!is_dir($backupPath)) {
                    mkdir($backupPath, 0664, true);
                }

                // SQL file generation and zipping
                $timestamp = date('Ymd_His');
                $zip_filename = "{$table}_{$timestamp}.zip";
                $zip_path = $backupPath . $zip_filename;

                $CI->load->dbutil();

                // Generate SQL format file from a databasse
                $prefs = array(
                    'format' => 'sql',
                    'filename' => $table . '.sql',
                    'newline' => "\n",
                    'foreign_key_checks' => FALSE,
                    'compression' => FALSE,
                    'tables' => array($table) // Specify the database or table
                );

                $sql = $CI->dbutil->backup($prefs);

                // Write the file to a path
                write_file(APPPATH . 'database/backup/' . $table . '.sql', $sql);

                return [
                    'code' => 200,
                    'message' => 'SQL files generated and zipped successfully',
                    'zip_file' => $zip_filename,
                    'zip_path' => $zip_path,
                    'timestamp' => $timestamp
                ];
            } else {
                return ['code' => 400, 'message' => 'Invalid backup type'];
            }
        } catch (Exception $e) {
            if ($type === 'table') {
                // If backup fails, try to clean up
                if (isset($backupTable)) {
                    $CI->db->query("DROP TABLE IF EXISTS {$backupTable}");
                }
            }

            return ['code' => 500, 'message' => 'Error backing up table: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('migrateAllTable')) {
    function migrateAllTable()
    {
        $CI = &get_instance();
        $CI->load->library('migration');

        try {
            // Get the latest migration version from the migration config
            $CI->config->load('migration');
            $latest_version = $CI->config->item('migration_version');

            if ($CI->migration->current() === FALSE) {
                // Log error if migration fails
                return ['code' => 500, 'message' => $CI->migration->error_string()];
            }

            // Migrate to the latest version
            if ($CI->migration->version($latest_version) === FALSE) {
                // Log error if migration fails
                return ['code' => 500, 'message' => $CI->migration->error_string()];
            }

            return ['code' => 200, 'message' => 'Migration completed successfully'];
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error migrating all tables: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('dropAllTable')) {
    function dropAllTable()
    {
        $CI = &get_instance();
        $CI->load->dbforge();

        try {
            // Get list of all tables
            $tables = $CI->db->list_tables();

            // Loop through and drop each table
            foreach ($tables as $table) {
                $CI->dbforge->drop_table($table, TRUE); // TRUE to add IF EXISTS
            }

            return ['code' => 200, 'message' => 'All tables dropped successfully'];
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error dropping all tables: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('migrateTable')) {
    function migrateTable($filename)
    {
        try {
            if (filter_var(env('MIGRATION'), FILTER_VALIDATE_BOOLEAN)) {

                $CI = &get_instance();
                $CI->load->library('migration');

                $file = APPPATH . 'migrations/' . $filename;

                try {
                    include_once($file);

                    $class = 'Migration_' . strtolower(_get_migration_name(basename($file, '.php')));

                    $migration = array($class, 'up');
                    $migration[0] = new $migration[0];

                    call_user_func($migration);

                    return ['code' => 200, 'message' => ''];
                } catch (Throwable $e) {
                    return ['code' => 500, 'message' => 'Error migrate table: ' . $e->getMessage()];
                }
            } else {
                return ['code' => 500, 'message' => 'No access to this url'];
            }
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error migrate table: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('dropTable')) {
    function dropTable($filename)
    {
        try {
            if (filter_var(env('MIGRATION'), FILTER_VALIDATE_BOOLEAN)) {

                $CI = &get_instance();
                $CI->load->library('migration');

                $file = APPPATH . 'migrations/' . $filename;

                try {
                    include_once($file);

                    $class = 'Migration_' . strtolower(_get_migration_name(basename($file, '.php')));

                    $migration = array($class, 'down');
                    $migration[0] = new $migration[0];

                    call_user_func($migration);

                    return ['code' => 200, 'message' => ''];
                } catch (Throwable $e) {
                    return ['code' => 500, 'message' => 'Error drop table: ' . $e->getMessage()];
                }
            } else {
                return ['code' => 500, 'message' => 'No access to this url'];
            }
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error drop table: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('seedTable')) {
    function seedTable($filename)
    {
        try {
            if (filter_var(env('MIGRATION'), FILTER_VALIDATE_BOOLEAN)) {

                $CI = &get_instance();
                $CI->load->library('migration');

                $file = APPPATH . 'migrations/' . $filename;

                try {
                    include_once($file);

                    $class = 'Migration_' . strtolower(_get_migration_name(basename($file, '.php')));

                    $migration = array($class, 'seeder');
                    $migration[0] = new $migration[0];

                    if (!method_exists($migration[0], 'seeder')) {
                        return ['code' => 400, 'message' => 'No seeder data found.'];
                    }

                    call_user_func($migration);
                    return ['code' => 200, 'message' => ''];
                } catch (Throwable $e) {
                    return ['code' => 500, 'message' => 'Error seed table: ' . $e->getMessage()];
                }
            } else {
                return ['code' => 500, 'message' => 'No access to this url'];
            }
        } catch (Exception $e) {
            return ['code' => 500, 'message' => 'Error seed table: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('_get_migration_name')) {
    function _get_migration_name($migration)
    {
        $parts = explode('_', $migration);
        array_shift($parts);
        return implode('_', $parts);
    }
}
