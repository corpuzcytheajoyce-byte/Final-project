<?php
session_start();
// REMOVED LOGIN REQUIREMENT - anyone can view contact page
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Contact | The other Side Cafe</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f9f6f1; font-family: 'Poppins', sans-serif; }
    .navbar { background-color: #6f4e37 !important; }
    .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
    .navbar-brand:hover, .nav-link:hover { color: #f3e5ab !important; }
    .contact-hero { background: linear-gradient(rgba(111, 78, 55, 0.8), rgba(111, 78, 55, 0.8)), url('https://scontent.fdvo6-1.fna.fbcdn.net/v/t39.30808-6/584470734_122106780435093797_7110990687719941218_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=UmE7UnDN2hsQ7kNvwGIk3xd&_nc_oc=Adm9I8gQRF0V_UcRb3oNqZV0WOzk7jRs_bZI307-fdgowqXH1MgQwacfLJOOCeUVADM&_nc_zt=23&_nc_ht=scontent.fdvo6-1.fna&_nc_gid=KTp_IuKdV_n7XSWgRih4lQ&oh=00_Afi-xe-6H2Bjm3hpxp_RbAmWJ17iPrGjJHQWethLUBH_Jw&oe=69220D2A'); background-size: cover; background-position: center; color: white; padding: 100px 0; text-align: center; }
    .contact-section { padding: 80px 0; }
    .contact-icon { font-size: 24px; margin-right: 10px; color: #6f4e37; }
    footer { text-align: center; padding: 20px; background-color: #6f4e37; color: white; margin-top: 40px; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">☕ The other Side Cafe</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['user_logged_in'])): ?>
          <li class="nav-item"><a class="nav-link" href="#">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login / Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- REST OF YOUR CONTACT.PHP CONTENT REMAINS THE SAME -->
<div class="contact-hero">
  <div class="container">
    <h1 class="display-4 fw-bold mb-4">Get In Touch</h1>
    <p class="lead">We'd love to hear from you</p>
  </div>
</div>

<section class="contact-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-8 mx-auto">
        <div class="row">
          <div class="col-md-6 mb-4">
            <div class="card text-center h-100 border-0 shadow-sm">
              <div class="card-body">
                <div class="contact-icon">📧</div>
                <h5 class="card-title">Email Us</h5>
                <p class="card-text">theothersidecafe@gmail.com</p>
                <small class="text-muted">We respond within 24 hours</small>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-4">
            <div class="card text-center h-100 border-0 shadow-sm">
              <div class="card-body">
                <div class="contact-icon">📱</div>
                <h5 class="card-title">Call Us</h5>
                <p class="card-text">+63 912 345 6789</p>
                <small class="text-muted">Mon-Sun: 7:00 AM - 10:00 PM</small>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-4">
            <div class="card text-center h-100 border-0 shadow-sm">
              <div class="card-body">
                <div class="contact-icon">💬</div>
                <h5 class="card-title">Facebook</h5>
                <p class="card-text">The Other Side Cafe</p>
                <small class="text-muted">Message us for inquiries</small>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-4">
            <div class="card text-center h-100 border-0 shadow-sm">
              <div class="card-body">
                <div class="contact-icon">📍</div>
                <h5 class="card-title">Visit Us</h5>
                <p class="card-text">Kialeg Magsaysay<br>Davao del sur, Philippines</p>
                <small class="text-muted">Come enjoy our cozy space</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <p class="mb-2">© 2025 The other Side Cafe</p>
    <p class="mb-0">
      <a href="about.php" class="text-light me-3">About Us</a> | 
      <a href="contact.php" class="text-light ms-3">Contact</a>
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>