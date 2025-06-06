<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to OABS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">OABS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="faq.php">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<div class="container my-5 text-center">
    <div class="p-5 bg-white rounded shadow hero-box">
        <h1 class="fw-bold mb-3">Welcome to <span class="text-primary">OABS</span></h1>
        <p class="lead mb-4">Effortlessly schedule and manage your appointments online.<br>Secure, fast, and user-friendly for clients and providers.</p>
        <a href="login.php" class="btn btn-primary me-2">Login</a>
        <a href="register.php" class="btn btn-outline-secondary">Register</a>
    </div>
</div>

<!-- Recommended Providers -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4">Recommended Providers</h2>
        <div class="row justify-content-center">
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <img src="assets/img/provider1.jpg" class="card-img-top" alt="Health Care">
                    <div class="card-body">
                        <h5 class="card-title">Health Care</h5>
                        <p class="card-text">Specialist appointments with top ratings.</p>
                        <span class="badge bg-success">★★★★★</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <img src="assets/img/provider2.jpg" class="card-img-top" alt="Wellness Studio">
                    <div class="card-body">
                        <h5 class="card-title">Wellness Studio</h5>
                        <p class="card-text">Trusted by 400+ happy clients.</p>
                        <span class="badge bg-success">★★★★★</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="mb-4">5-Star Reviews</h2>
        <div class="row justify-content-center">
            <div class="col-md-3">
                <div class="card p-3 shadow-sm">
                    <p class="text-muted">"Amazing experience! Booking is quick and easy."</p>
                    <small class="text-secondary">– Sarah, Sydney</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm">
                    <p class="text-muted">"Great platform for both clients and providers."</p>
                    <small class="text-secondary">– Ayesha, Melbourne</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 shadow-sm">
                    <p class="text-muted">"Love the interface and booking flow."</p>
                    <small class="text-secondary">– Jake, Brisbane</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4">
    <p class="mb-1">Find us on social media</p>
    <a href="https://instagram.com/yourbrand" class="text-white" target="_blank">📸 Instagram</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
