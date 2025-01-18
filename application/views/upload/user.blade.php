@extends('_generals.templates._main')

@section('content')

@if($permission['upload-user-view'])

<!-- Main Card -->
<div class="card shadow-sm">
    <div class="card-body">
        <!-- Template Download Section -->
        <div class="mb-4">
            <h5 class="card-title mb-3">
                <i class="fas fa-file-excel text-success me-2"></i>Download Template
            </h5>
            <a href="{{ asset('downloads/example_user.xlsx') }}" class="btn btn-success" download>
                <i class="fas fa-download me-2"></i>Download Excel Template
            </a>
        </div>

        <!-- Instructions Section -->
        <div class="mb-4">
            <div class="alert alert-info border-0 shadow-sm">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Instructions
                </h5>
                <hr>
                <ol class="list-group list-group-numbered border-0">
                    <li class="list-group-item border-0 bg-transparent">Download the Excel template using the button above</li>
                    <li class="list-group-item border-0 bg-transparent">Fill out the template with the required information</li>
                    <li class="list-group-item border-0 bg-transparent">Upload the completed file using the form below</li>
                    <li class="list-group-item border-0 bg-transparent">Click "Upload" to begin processing</li>
                </ol>
                <div class="mt-3">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Fill in the NRIC without dashes (-)
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="mb-4">
            <h5 class="card-title mb-3">
                <i class="fas fa-upload text-primary me-2"></i>Upload File
            </h5>
            <form id="uploadForm" class="needs-validation" novalidate>
                <div class="mb-3">
                    <div class="input-group">
                        <input type="file" class="form-control" id="file" name="file_upload" accept=".csv" required>
                        <button class="btn btn-primary" type="submit" id="uploadButton">
                            <i class="fas fa-upload me-2"></i>Upload
                        </button>
                    </div>
                    <div class="invalid-feedback">Please select a file first.</div>
                </div>
            </form>
        </div>

        <!-- Progress Section -->
        <div id="progressWrapper" class="mb-4 d-none">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="card-title text-primary">
                        <i class="fas fa-spinner fa-spin me-2"></i>Upload Progress
                    </h6>
                    <div class="progress mb-3">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <p id="progressStatus" class="mb-0 text-muted small"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Form validation
        const form = document.getElementById('uploadForm');
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            if (!validateUploadData()) {
                validationJsError('toastr', 'single'); // single or multi
                return;
            }

            uploadFile();
        });

        // File input change handler
        $('#file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            if (fileName) {
                $(this).next('.custom-file-label').html(fileName);
            }
        });

        async function uploadFile() {

            // Show progress section
            $('#progressWrapper').removeClass('d-none');
            $('#uploadForm').addClass('d-none');

            const res = await actionApi('post', 'upload/import-user', {
                formId: 'uploadForm',
                loadingBtnId: 'uploadButton',
                showAlertMessage: true,
                closedModal: true,
                allowValidationMessage: true,
                uploadForm: true,
                uploadProgressId: 'progressBar',
                actionType: 'upload'
            });

            if (isSuccess(res)) {
                toastr.success(response.message);
                setTimeout(function() {
                    $('#progressWrapper').addClass('d-none');
                    $('#progressBar').css('width', '0%').text('0%');
                    $('#progressStatus').text('');
                    $('#uploadForm').removeClass('d-none');
                    $('#file').val('');
                    form.classList.remove('was-validated');
                }, 1500);
            }

        }
    });

    function validateUploadData() {

        const rules = {
            'file_upload': 'required|file|size:10|mimes:csv',
        };

        const message = {
            'file_upload': 'Upload File',
        };

        return validationJs(rules, message);
    }
</script>
@else
{{ nodataAccess() }}
@endif


@endsection