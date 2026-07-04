<?php
require_once("connection.php");
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIZA Event Ticket | About Us</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <section class="about-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/background1.jpg'); background-size: cover; background-repeat: no-repeat; background-repeat: no-repeat; background-position: center">
        <h1>We Are DIZAET</h1>
        <p>Connecting people through experiences. The future of campus event management is here.</p>
    </section>

    <section class="mission-section">
        <div class="mission-container">
            <h2 style="margin-bottom: 20px;">Our Mission</h2>
            <p style="line-height: 1.8; color: #555; font-size: 1.1rem;">
                <b>DIZA Event Ticket (DIZAET)</b> is a web-based platform developed to streamline the event ticketing process for university students and organizers. Born from a collaboration with <b>UiTM</b>, our goal is to eliminate long queues and manual booking hassles.
                <br><br>
                Whether you are an organizer looking to manage attendees efficiently or a student wanting to catch the next big campus concert, DIZAET provides a secure, fast, and user-friendly solution.
            </p>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <h2>Meet The DIZAZ Group</h2>
            <p style="margin-bottom: 30px;">The developers behind the platform.</p>

            <div class="team-grid">
                
                <div class="team-card">
                    <img src="images/mohamad-nur-ikhmal.png" alt="Member" class="member-img">
                    <h3 class="member-name">Mohamad Nur Ikhmal</h3>
                    <p class="member-role">Project Manager</p>
                    <div class="social-links">
                        <a href="https://github.com/Ikhmal0511"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="team-card">
                    <img src="images/tengku-ahmad-danial.png" alt="Member" class="member-img">
                    <h3 class="member-name">Tengku Ahmad Danial</h3>
                    <p class="member-role">Head Developer & Designer</p>
                    <div class="social-links">
                        <a href="https://github.com/KuDanial"><i class="fab fa-github"></i></a>
                        <a href="https://www.linkedin.com/in/tengkuahmaddanial/"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="team-card">
                    <img src="images/muhammad-alif-daniel.png" alt="Member" class="member-img">
                    <h3 class="member-name">Muhammad Alif Daniel</h3>
                    <p class="member-role">UI/UX Designer</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="team-card">
                    <img src="images/muhammad-syafiq-ikhwan.png" alt="Member" class="member-img">
                    <h3 class="member-name">Muhammad Syafiq Ikhwan</h3>
                    <p class="member-role">Quality Assurance (QA) Tester</p>
                    <div class="social-links">
                        <a href="https://github.com/amierzhafran"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="registrations-section">
        <div class="registrations-container">
            <div class="registration-block">
                <img src="images/ssm-logo.png" alt="SSM Logo">
                <div class="registration-text">
                    <p>202003058291</p>
                    <p>(KT0459281-D)</p>
                </div>
            </div>

            <div class="registration-block">
                <img src="images/mof-logo.png" alt="Ministry of Finance Logo">
                <div class="registration-text">
                    <p>Registration with</p>
                    <p><strong>Ministry Of Finance (MOF)</strong></p>
                    <p>(357-0104827364)</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-reach-section">
        <div class="contact-reach-container">
            <div class="contact-reach-header">
                <h2>Find Us & Reach Us</h2>
                <p>Looking for help? Contact us today for all the details you need!</p>
            </div>

            <div class="contact-details-grid">
                <div class="contact-detail-item">
                    <h3><i class="fas fa-phone-alt"></i> Phone / Mobile</h3>
                    <p>
                        <a href="tel:+60162624834">+60 16-262 4834</a> / <br>
                        <a href="tel:+601165513006">+60 11-6551 3006</a>
                    </p>
                </div>

                <div class="contact-detail-item">
                    <h3><i class="fas fa-map-marker-alt"></i> Address</h3>
                    <p>
                        UiTM Cawangan Kelantan,<br>
                        Kampus Machang,<br>
                        18500 Machang, Kelantan,<br>
                        Malaysia
                    </p>
                </div>

                <div class="contact-detail-item">
                    <h3><i class="fas fa-envelope"></i> Email</h3>
                    <p>
                        <a href="mailto:support@grab.uitm.edu.my">support@grab.uitm.edu.my</a>
                    </p>
                </div>
            </div>

            <div class="map-container">
                <iframe src="https://maps.google.com/maps?width=100%25&height=600&hl=en&q=UiTM%20Cawangan%20Kelantan%20Kampus%20Machang&t=&z=14&ie=UTF8&iwloc=B&output=embed" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>
    
    <footer>
        <div class="container footer-content">
            <div class="footer-col brand-col">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZA Event Ticket" class="footer-logo">
                <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 8px;"> In Collaboration With </p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="images/LOGO UiTM OUTLINE 3 (WHITE).png" alt="UiTM" style="height: 40px; opacity: 0.8;">
                    <span style="color:#555;">|</span>
                    <img src="images/Google-Gemini-White-Logo.png" alt="Gemini" style="height: 40px; opacity: 0.8;">
                </div>
                <p>Your one-stop platform for all event ticketing needs.</p>
            </div>

            <div class="footer-col contact-col">
                <h4>Contact Us</h4>
                <div class="contact-details">
                    <p><b>Hotline:</b> <br>+60 16-262 4834 / +60 11-6551 3006</p>
                    <p><b>Email:</b> <br>support@diza.uitm.edu.my</p>
                </div>
            </div>

            <div class="footer-col">
                <h4>Support</h4>
                <ul class="footer-links-list">
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="help.php">Contact</a></li>
                    <li><a href="help.php">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Follow Us</h4>
                <div class="footer-social">
                    <a href="https://www.facebook.com/modannie"><i class="fab fa-facebook"></i></a>
                    <a href="https://x.com/modannyyy"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/tadanny._"><i class="fab fa-instagram"></i></a>
                    <a href="https://discord.gg/95K89KDhTm"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 DIZAZ Group. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>