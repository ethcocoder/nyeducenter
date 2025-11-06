<?php
// Sponsor Dashboard View: $currentUser, $stats (favorites, messages, sponsored)
?>
<div class="container my-4">
    <h2 class="mb-4">Sponsor Dashboard</h2>
    <div class="alert alert-info">Welcome, <?= htmlspecialchars($currentUser['name']) ?>!</div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Favorites</h5>
                    <p class="display-5"> <?= $stats['favorites'] ?? 0 ?> </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Messages</h5>
                    <p class="display-5"> <?= $stats['messages'] ?? 0 ?> </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Sponsored Innovations</h5>
                    <p class="display-5"> <?= $stats['sponsored'] ?? 0 ?> </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <a href="/innovations" class="btn btn-outline-primary w-100">Browse Innovations</a>
        </div>
        <div class="col-md-4 mb-2">
            <a href="/favorites" class="btn btn-outline-success w-100">View Favorites</a>
        </div>
        <div class="col-md-4 mb-2">
            <a href="/messages" class="btn btn-outline-info w-100">Messages</a>
        </div>
    </div>
</div> 