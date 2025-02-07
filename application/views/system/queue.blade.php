@extends('_generals.templates._main')

@section('content')

@if($permission['queue-list-view'])
<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-4">
            <h5 class="card-title mb-3">
                <i class="fas fa-dashboard text-info me-2"></i> Queue Section
            </h5>
        </div>

        <div class="mb-4">
            <div id="nodatadiv" style="display: none;"> <?php nodata() ?> </div>
            <div id="dataListDiv" class="card-datatable table-responsive" style="display: none;">
                <table id="dataList" class="table nowrap dt-responsive align-middle table-hover table-bordered dataTable no-footer dtr-inline collapsed" width="100%">
                    <thead class="table-dark">
                        <tr>
                            <th> UUID </th>
                            <th> Type </th>
                            <th> Attempt </th>
                            <th> Payload </th>
                            <th> Action </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@else
{{ nodataAccess() }}
@endif

<script>
    $(document).ready(async function() {
        await getDataList();
    });

    async function getDataList() {
        generateServerDt('dataList', 'queue/list', 'nodatadiv', 'bodyDiv');
    }

    async function editRecord(id) {
        const res = await callApi('get', 'queue/show/' + id, {}, 'queue-update');

        if (isSuccess(res)) {
            // formModal('update', res.data)
        }
    }

    async function deleteRecord(id) {
        Swal.fire({
            title: 'Are you sure?',
            html: "You’re about to delete this item permanently. Continue?",
            icon: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Confirm!',
            reverseButtons: true
        }).then(
            async (result) => {
                if (result.isConfirmed) {
                    const res = await actionApi('delete', 'queue/delete/' + id, {
                        showAlertMessage: true, // if false, no toastr will show.
                        reloadFunction: getDataList,
                        permissions: 'queue-delete',
                        actionType: 'delete' // force to show the notify with operation upload (usually will auto detect by MY_Model response).
                    });
                }
            })
    }
</script>

@endsection