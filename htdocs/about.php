<?php
session_start();
// REMOVED LOGIN REQUIREMENT - anyone can view about page
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us | The other Side Cafe</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f9f6f1; font-family: 'Poppins', sans-serif; }
    .navbar { background-color: #6f4e37 !important; }
    .navbar-brand, .nav-link { color: #fff !important; font-weight: 500; }
    .navbar-brand:hover, .nav-link:hover { color: #f3e5ab !important; }
    .about-hero { background: linear-gradient(rgba(111, 78, 55, 0.8), rgba(111, 78, 55, 0.8)), url('https://scontent.fdvo6-1.fna.fbcdn.net/v/t39.30808-6/574276436_122102264415093797_3386396307161979542_n.jpg?stp=cp6_dst-jpg_tt6&_nc_cat=102&ccb=1-7&_nc_sid=833d8c&_nc_ohc=rQcxb1_dsDAQ7kNvwGOaPod&_nc_oc=AdmrrLML7KJHRkr8BJMbVa0cNp7f4iJNExJJo1tER4yNSsHZponr9iUnO2o5OZNfpWE&_nc_zt=23&_nc_ht=scontent.fdvo6-1.fna&_nc_gid=8RkqKFlWH3NgXhqOsfFyZg&oh=00_AfjCi64cdKW1za5oOHYNr24iUQYM36TAuwJaKTyIqLM4ug&oe=69220D82'); background-size: cover; background-position: center; color: white; padding: 100px 0; text-align: center; }
    .mission-section { padding: 80px 0; background-color: #fff8f0; }
    .values-section { padding: 80px 0; }
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
        <li class="nav-item"><a class="nav-link active" href="about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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

<!-- REST OF YOUR ABOUT.PHP CONTENT REMAINS THE SAME -->
<div class="about-hero">
  <div class="container">
    <h1 class="display-4 fw-bold mb-4">Our Story</h1>
    <p class="lead">Brewing happiness since 2025</p>
  </div>
</div>

<section class="mission-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h2 class="fw-bold mb-4">Our Mission</h2>
        <p class="lead mb-4">To create a welcoming space where community and coffee come together, serving quality beverages and snacks that brighten your day.</p>
        <p>Founded in 2025, The Other Side Cafe started as a small dream to bring people together over great coffee. Today, we've grown into a community hub where students, professionals, and families gather to enjoy our carefully crafted beverages and homemade snacks.</p>
        <p>Our online ordering system extends this experience beyond our physical walls, allowing you to enjoy the cafe atmosphere wherever you are.</p>
      </div>
      <div class="col-lg-4">
        <img src="https://scontent.fdvo6-1.fna.fbcdn.net/v/t39.30808-6/571483339_122098185921093797_546604355048795698_n.jpg?stp=cp6_dst-jpg_tt6&_nc_cat=111&ccb=1-7&_nc_sid=833d8c&_nc_ohc=ThXMiQJPeK4Q7kNvwFUQdEM&_nc_oc=AdlXQ8mLbVuQN7O1-byI3RGXmmn0gw8cjCB88yXsK5y8U6FkQ4H89XYgn5WTmVPqY3A&_nc_zt=23&_nc_ht=scontent.fdvo6-1.fna&_nc_gid=JAeJ7NcqbuwTV2KNWRg79w&oh=00_Afg-C7WvGvgEJx5qoqaAgvJHEZz8N2r2lFY0EcYZWcwNwA&oe=6921F8DF" alt="Coffee Beans" class="img-fluid rounded shadow">
      </div>
    </div>
  </div>
</section>

<section class="values-section">
  <div class="container">
    <h2 class="text-center fw-bold mb-5">Our Values</h2>
    <div class="row">
      <div class="col-md-4 text-center mb-4">
        <div class="card border-0 h-100">
          <div class="card-body">
            <h5 class="card-title">Quality</h5>
            <p class="card-text">We source the finest coffee and ingredients to ensure every cup and bite exceeds expectations.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-center mb-4">
        <div class="card border-0 h-100">
          <div class="card-body">
            <h5 class="card-title">Community</h5>
            <p class="card-text">We believe in building relationships and creating spaces where people feel at home.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 text-center mb-4">
        <div class="card border-0 h-100">
          <div class="card-body">
            <h5 class="card-title">Innovation</h5>
            <p class="card-text">From our online ordering system to our unique recipes, we're always looking for better ways to serve you.</p>
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