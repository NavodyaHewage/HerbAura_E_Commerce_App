<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Candidate Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link" href="../candidate/profile.php">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../candidate/jobs.php">Jobs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../candidate/applied_jobs.php">Applied Jobs</a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="../public/logout.php" id="logout">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<style>
    #logout {
        color: red;
        padding-right: 100px;
    }
    .navbar-brand{
        padding-left: 20px;
    }
    .navbar-nav{
        padding-left: 100px;
    }
    .navbar-light .navbar-nav .nav-link {
        padding-left: 20px;
        color: black; /* Set text color to black */
    }

    .navbar-light .navbar-nav .nav-link:hover {
        color: #f4623a; /* Change text color to orange on hover */
    }

    .navbar-light .navbar-toggler {
        border-color: rgba(0,0,0,.1); /* Optional: Adjust toggle button color */
    }
</style>