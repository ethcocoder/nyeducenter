<?php
// Admin Dashboard View: $currentUser, $stats (users, innovations, messages)
?>
<div class="container my-4">
    <h2 class="mb-4">Admin Dashboard</h2>
    <div class="alert alert-info">Welcome, <?= htmlspecialchars($currentUser['name']) ?>! You have admin privileges.</div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="display-5"> <?= $stats['users'] ?? 0 ?> </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Innovations</h5>
                    <p class="display-5"> <?= $stats['innovations'] ?? 0 ?> </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Messages</h5>
                    <p class="display-5"> <?= $stats['messages'] ?? 0 ?> </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <a href="/admin/users" class="btn btn-outline-primary w-100">Manage Users</a>
        </div>
        <div class="col-md-4 mb-2">
            <a href="/admin/innovations" class="btn btn-outline-success w-100">Manage Innovations</a>
        </div>
        <div class="col-md-4 mb-2">
            <a href="/admin/messages" class="btn btn-outline-info w-100">View Messages</a>
        </div>
    </div>
</div> 