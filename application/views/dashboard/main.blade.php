@extends('_generals.templates._main')

@section('content')

@if($permission['dashboard-view'])
<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-4">
            <h5 class="card-title mb-3">
                <i class="fas fa-dashboard text-info me-2"></i> Dashboard Section
            </h5>
            {!! actionBtn('create', 'createUser', null, ['class' => 'btn-success btn-sm', 'text' => 'Sample Button']) !!}
        </div>
    </div>
</div>

@else
{{ nodataAccess() }}
@endif

<script>
    $(document).ready(function() {});
</script>

@endsection