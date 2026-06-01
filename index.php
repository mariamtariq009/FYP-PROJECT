<?php
include 'send_mail.php';

$message_status = "";

if(isset($_POST['send_message'])){

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $subject = "New Contact Message";

    $body = "
        <h3>New Contact Message</h3>
        <p><b>Name:</b> $full_name</p>
        <p><b>Email:</b> $email</p>
        <p><b>Message:</b><br>$message</p>
    ";

    $send = sendMail(
        'manoeman1015@gmail.com', // admin email receive karega
        $subject,
        $body
    );

    if($send){
        $message_status = "<div class='alert alert-success mt-3'>Message sent successfully!</div>";
    }else{
        $message_status = "<div class='alert alert-danger mt-3'>Email failed to send!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UAF Vehicle Management System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="Assets/css/frontend-css.css">
<style>
    .chat-toggle{
    display:flex;
    justify-content:center;
    align-items:center;
    }

    .chat-icon{
        width:70px;
        height:70px;
        object-fit:contain;
    }
</style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid px-lg-5">

        <a class="navbar-brand" href="#">
            <img src="Assets/logo.png" class="main-logo" alt="">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link" href="#">HOME</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" href="schedule.html">Schedule</a>
                    
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" href="fare.html">FARES</a>
                </li>

                

                <li class="nav-item">
                    <a class="nav-link" href="booking.php">BOOKING</a>
                </li>

            </ul>

            <a href="login.php" class="login-btn">Login</a>

        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-container">
    <div class="slide active"></div>
    <div class="overlay"></div>

    <div class="hero-content">
        <h1>UNIVERSITY FLEET MANAGEMENT SYSTEM</h1>
        <p>EFFICIENT TRANSPORT SOLUTIONS FOR STUDENTS & TRIPS</p>
    </div>
</section>

<!-- Services -->
<section class="services-wrapper">
    <div class="container">

        <h2 class="main-heading text-center mb-5">OUR SERVICES</h2>

        <div class="row g-4">

            <div class="col-lg-6 col-md-12">
                <div class="card large-card h-100">
                    <div>
                        <span class="brand-tag">ECOTRANS</span>
                        <h2>Get a <span>special price</span> on your journey</h2>
                        <p>
                            Experience premium university transport services with affordable rates.
                        </p>
                        <a href="booking.php" class="btn-green">Booking now</a>
                    </div>

                    <img src="Assets/services1.jpg" class="img-fluid mt-3" alt="">
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="row g-4">

                    <div class="col-md-6 col-sm-12">
                        <div class="card small-card h-100">
                            <h3>Fast Booking</h3>
                            <p>Book seats instantly.</p>
                            <img src="Assets/service2.jpg" alt="">
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="card small-card h-100">
                            <h3>Admin & Driver Tools</h3>
                            <p>Advanced management tools.</p>
                            <img src="Assets/services3.jpeg" alt="">
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="card small-card h-100">
                            <h3>Safe Journey</h3>
                            <p>Professional drivers.</p>
                            <img src="Assets/service 4.png" alt="">
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="card small-card h-100">
                            <h3>Live Bus Tracking</h3>
                            <p>Track bus in real-time.</p>
                            <img src="Assets/rout track.jpg" alt="">
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- About -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6 col-md-12">
                <img src="Assets/Karad_Bus_Stand.jpg" class="img-fluid about-img" alt="">
            </div>

            <div class="col-lg-6 col-md-12">
                <span class="about-tag">ABOUT OUR SYSTEM</span>
                <h2>Providing Reliable Transport Since 2022</h2>

                <p>
                    Our University Bus Management System provides efficient transportation
                    for students and faculty.
                </p>

                <p>
                    We ensure every journey is comfortable and on time.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row g-5">

            <div class="col-lg-6 col-md-12">
                <h2>University of Agriculture Faisalabad</h2>
                <p>Providing the best university transport experience.</p>

                <p>UAF Road, Faisalabad</p>
                <p>+92 312 0000000</p>
                <p>info@uafbus.com</p>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="contact-form">
                    <h3>Contact us</h3>

                    <?= $message_status; ?>

                    <form method="POST">

                        <input type="text" name="full_name" placeholder="Full Name" required>

                        <input type="email" name="email" placeholder="Email Address" required>

                        <textarea name="message" rows="4" placeholder="Your Message" required></textarea>

                        <button type="submit" name="send_message">Send Message</button>

                    </form>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            © 2026 UAF Bus Service
        </div>
    </div>
</footer>

<!-- AI CHATBOT BUTTON -->

<div class="ai-chat-wrapper">

    <!-- CHAT BUTTON -->

    <button class="chat-toggle" onclick="toggleChat()">

        <img src="Assets/chatbot.png" alt="Chat Bot" class="chat-icon">

    </button>

    <!-- CHAT BOX -->

    <div class="chatbot-container" id="chatbot">

        <div class="chat-header">

            <span>
                AI Shuttle Assistant
            </span>

            <button onclick="toggleChat()" class="close-btn">
                ✖
            </button>

        </div>

        <div class="chat-body" id="chatArea">

            <div class="bot-message">
                Hello 👋 Ask me about shuttle schedule, fares or booking.
            </div>

        </div>

        <div class="chat-footer">

            <input type="text" id="userInput"
                placeholder="Ask something...">

            <button onclick="sendMessage()">
                Send
            </button>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="Assets/js/frontend-js.js"></script>

</body>
</html>