@extends('_generals.templates._main')

@section('content')

<!-- @ if($permission['dashboard-view']) -->
<div id="container">
    <h1>Welcome {{ currentUserFullName() }} to {{ $title }}!</h1>
    <div id="body">
        <a href="{{ url('logout') }}" class="btn btn-sm btn-danger"> Log Out </a>
    </div>
</div>
<!-- @ else -->
<!-- {{ nodataAccess() }} -->
<!-- @ endif -->

<script>
    $(document).ready(function() {});
</script>

@endsection