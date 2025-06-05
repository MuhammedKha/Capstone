<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to OABS</title>

  <!-- Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f7f9fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .main-container {
      margin-top: 100px;
    }
    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
      padding: 50px 30px;
      text-align: center;
    }
    h1 {
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    p.lead {
      font-size: 1.2rem;
      color: #555;
      margin-bottom: 30px;
    }
    .btn-custom {
      width: 120px;
      font-weight: 500;
    }
    .btn-primary {
      background-color: #1a73e8;
      border-color: #1a73e8;
    }
    .btn-primary:hover {
      background-color: #155ac3;
      border-color: #155ac3;
    }
    .btn-secondary {
      background-color: #e2e6ea;
      border-color: #e2e6ea;
      color: #333;
    }
    .btn-secondary:hover {
      background-color: #d6d8db;
      border-color: #c6c8ca;
    }
  </style>
</head>
<body>

<div class="container main-container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <h1>Welcome to <span class="text-primary">OABS</span></h1>
        <p class="lead">Effortlessly schedule and manage your appointments online.<br>Secure, fast, and user-friendly for clients and providers.</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="users/login.php" class="btn btn-primary btn-custom">Login</a>
          <a href="users/register.php" class="btn btn-secondary btn-custom">Register</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>