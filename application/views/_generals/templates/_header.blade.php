<!-- Header Section -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-6">Welcome <span class="text-primary">{{ currentUserFullName() }}</span></h1>
        <p class="text-muted"><span id="currentTime"></span> - <strong>{{ $title }}</strong></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ url('logout') }}" class="btn btn-danger"> <i class="fas fa-sign-out-alt me-2"></i> Log Out </a>
    </div>
</div>