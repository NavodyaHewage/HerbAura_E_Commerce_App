<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="HerbAura - Authentic Ayurvedic Wellness Products from Sri Lanka" />
    <meta name="keywords" content="Ayurveda, Organic, Sustainable, Sri Lankan Herbs, Wellness Products" />
    <meta name="author" content="HerbAura" />
    <title>HerbAura - Nature's Wisdom for Holistic Health</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <!-- Bootstrap Icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" />
    <!-- SimpleLightbox plugin CSS-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="assets/css/styles.css" rel="stylesheet" />
    <style>
        :root {
            --primary-green: #2d5a27;
            --secondary-beige: #f5e6d3;
        }
        body {
            background-color: var(--secondary-beige);
        }
        .masthead {
            background: linear-gradient(rgba(45, 90, 39, 0.7), rgba(45, 90, 39, 0.7)),
                        url('assets/assets/img/herbal-bg.jpg') center/cover;
        }
        /* ... keep previous notification styles ... */
    </style>
</head>
<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="#page-top">
                <img src="assets/assets/img/logo.png" alt="HerbAura Logo" width="40" class="d-inline-block align-text-top">
                HERBAURA
            </a>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto my-2 my-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="public/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="public/register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Masthead-->
    <header class="masthead">
        <div class="container px-4 px-lg-5 h-100">
            <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
                <div class="col-lg-8 align-self-end">
                    <h1 class="text-white font-weight-bold">Ancient Wisdom, Modern Wellness</h1>
                    <hr class="divider" />
                </div>
                <div class="col-lg-8 align-self-baseline">
                    <p class="text-white-75 mb-5">Experience Authentic Sri Lankan Ayurveda</p>
                    <a class="btn btn-light btn-xl" href="#products">Explore Products</a>
                </div>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section class="page-section bg-primary" id="about">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="text-white mt-0">Our Journey</h2>
                    <hr class="divider divider-light" />
                    <p class="text-white-75 mb-4">
                        Rooted in Sri Lanka's rich herbal traditions, HerbAura combines centuries-old Ayurvedic wisdom 
                        with modern sustainable practices. We craft organic wellness solutions that nurture both 
                        body and environment.
                    </p>
                    
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <h3 class="text-white">Our Vision</h3>
                            <p class="text-white-75">To be the global ambassador of authentic Sri Lankan Ayurveda</p>
                        </div>
                        <div class="col-md-6">
                            <h3 class="text-white">Our Promise</h3>
                            <p class="text-white-75">100% Organic • Ethically Sourced • Sustainable Packaging</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="page-section" id="products">
        <div class="container px-4 px-lg-5">
            <h2 class="text-center mt-0">Our Wellness Solutions</h2>
            <hr class="divider" />
            <div class="row gx-4 gx-lg-5">
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi-heart fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Immunity Boosters</h3>
                        <p class="text-muted mb-0">Ancient herbal formulations for modern immunity</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi-flower1 fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Stress Relief</h3>
                        <p class="text-muted mb-0">Natural solutions for mental wellness</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi-sun fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Daily Care</h3>
                        <p class="text-muted mb-0">Ayurvedic essentials for everyday wellness</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi-recycle fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Eco-Packaging</h3>
                        <p class="text-muted mb-0">Sustainable solutions for conscious living</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="page-section" id="contact">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 text-center">
                    <h2 class="mt-0">Connect With Nature's Wisdom</h2>
                    <hr class="divider" />
                    <p class="text-muted mb-5">Have questions about our products or practices? We're here to help!</p>
                </div>
            </div>
            <div class="row gx-4 gx-lg-5 justify-content-center mb-5">
                <div class="col-lg-6">
                    <!-- Updated Contact Form -->
                    <form id="contactForm" action="send-email.php" method="POST">
                        <!-- Form fields same as before but with wellness-focused labels -->
                    </form>
                </div>
            </div>
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-4 text-center mb-5 mb-lg-0">
                    <i class="bi-geo-alt fs-2 mb-3 text-muted"></i>
                    <div>No.23, Galle Street, Matara<br>Sri Lanka</div>
                    <i class="bi-telephone mt-4 fs-2 mb-3 text-muted"></i>
                    <div>+94 76 945 5673</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light py-5">
        <div class="container px-4 px-lg-5">
            <div class="small text-center text-muted">
                © 2024 HerbAura Pvt Ltd. | Sustainable Wellness from Sri Lanka<br>
                <div class="mt-2">
                    <a href="#privacy" class="text-muted mx-2">Privacy Policy</a>
                    <a href="#terms" class="text-muted mx-2">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts remain same -->
</body>
</html>