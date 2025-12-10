<?php
session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Other Side Cafe ☕</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* GENERAL */
body {
  font-family: 'Poppins', sans-serif;
  background-color: #f7efe6; /* light latte background */
  margin: 0;
}

/* NAVBAR (Dark Mocha Brown) */
.navbar {
  background-color: #5b3a29 !important;
}
.navbar-brand, .nav-link {
  color: #f8e7d2 !important;
  font-weight: 500;
}
.navbar-brand:hover, .nav-link:hover {
  color: #ffeac7 !important;
}

/* HERO SECTION (Rich Coffee Brown Gradient) */
.hero {
  background: linear-gradient(
      rgba(40, 26, 18, 0.6),
      rgba(40, 26, 18, 0.6)
    ),
    url('cafe-bg.jpg') center/cover no-repeat;
  height: 430px;
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  color: #ffeac7;
}
.hero h1 {
  font-size: 52px;
  font-weight: 900;
  margin-bottom: 10px;
}
.hero p {
  font-size: 18px;
  max-width: 600px;
  margin: 0 auto 20px;
}

/* BUTTON (Coffee Caramel Color) */
.btn-coffee {
  background-color: #c69c6d;
  color: white;
  padding: 12px 26px;
  border-radius: 6px;
  font-weight: 600;
  border: none;
}
.btn-coffee:hover {
  background-color: #b08962;
}

/* FEATURES SECTION */
.feature-box {
  text-align: center;
  padding: 25px;
}
.feature-box i {
  font-size: 42px;
  color: #5b3a29;
}
.feature-box h5 {
  margin-top: 15px;
  font-weight: 700;
  color: #5b3a29;
}
.feature-box p {
  color: #6b4a38;
}

/* FOOTER (Dark Mocha) */
footer {
  text-align: center;
  padding: 20px;
  background-color: #5b3a29;
  color: #ffeac7;
  margin-top: 40px;
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">☕ The Other Side Cafe</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>

        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
          <li class="nav-item"><a class="nav-link" href="#">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="auth.php">Login / Sign Up</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div>
    <h1>Experience Coffee on the Other Side</h1>
    <p>Where rich flavors, warm ambience, and crafted perfection meet every cup.</p>
    <a href="menu.php" class="btn-coffee">Browse Menu</a>
  </div>
</div>

<!-- FEATURES -->
<div class="container my-5">
  <div class="row">
    <div class="col-md-4 feature-box">
      <i class="bi bi-cup-hot-fill"></i>
      <h5>Artisan Coffee</h5>
      <p>Handcrafted blends made fresh for every customer.</p>
    </div>
    <div class="col-md-4 feature-box">
      <i class="bi bi-emoji-smile-fill"></i>
      <h5>Cozy Atmosphere</h5>
      <p>Relax, study, or unwind with our warm café-style vibe.</p>
    </div>
    <div class="col-md-4 feature-box">
      <i class="bi bi-gift-fill"></i>
      <h5>Rewards & Perks</h5>
      <p>Enjoy exclusive offers when you create an account.</p>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <p class="mb-2">© 2025 The Other Side Café | Prototype System</p>
    <p class="mb-0">
      <a href="about.php" class="text-light me-3">About Us</a> | 
      <a href="contact.php" class="text-light ms-3">Contact</a>
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>