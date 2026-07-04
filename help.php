<?php
require_once("connection.php");
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIZA Event Ticket | Support</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Smooth Accordion Transitions */
        .faq-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
            background: #fff;
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: var(--primary-yellow);
        }
        .faq-question {
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            user-select: none;
            background: #fff;
            transition: background 0.3s;
        }
        .faq-question:hover {
            background: #fafafa;
        }
        .faq-question i {
            transition: transform 0.3s ease;
            color: #888;
        }
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
            background: #f9f9f9;
            border-top: 1px solid transparent;
            padding: 0 24px;
        }
        .faq-answer p {
            padding: 18px 0;
            margin: 0;
            line-height: 1.6;
            color: #555;
        }
        
        /* Active State */
        .faq-item.active {
            border-color: var(--primary-yellow);
        }
        .faq-item.active .faq-question {
            background: #fffdf5;
        }
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
            color: var(--primary-yellow);
        }
        .faq-item.active .faq-answer {
            max-height: 200px; /* Adjust if answer is extremely long */
            padding: 0 24px;
            border-top: 1px solid #eee;
        }

        /* 2-Column Split Layout for Contact */
        .help-contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            align-items: start;
        }
        
        .contact-info-panel {
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }

        .contact-info-panel h3 {
            margin-bottom: 20px;
            font-size: 20px;
            color: #1a1a1a;
            border-bottom: 2px solid var(--primary-yellow);
            padding-bottom: 8px;
            display: inline-block;
        }

        .info-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            align-items: start;
        }

        .info-row i {
            font-size: 20px;
            color: var(--primary-yellow);
            background: #fff8e1;
            padding: 12px;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-text-details h4 {
            margin-bottom: 5px;
            font-size: 16px;
            color: #333;
        }

        .info-text-details p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        @media (max-width: 768px) {
            .help-contact-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }
    </style>
</head>
<body>

    <?php include("includes/header.php"); ?>

    <section class="support-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/background1.jpg'); background-size: cover; background-repeat: no-repeat; background-position: center; height: auto; padding: 60px 20px 80px 20px;">
        <h1>How can we help you?</h1>
        <p style="margin-bottom: 35px;">Search our knowledge base or select a category below.</p>

        <div class="support-grid" style="display: flex; gap: 20px; width: 100%; max-width: 1100px; justify-content: center; flex-wrap: wrap; margin: 30px auto 0;">
            <div class="support-card" style="cursor: default; flex: 1; min-width: 250px; background: rgba(30, 30, 30, 0.75); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 25px; text-align: center; color: white;">
                <i class="fas fa-ticket-alt" style="font-size: 30px; color: var(--primary-yellow); margin-bottom: 15px; display: block;"></i>
                <h3 style="margin-bottom: 10px; font-size: 18px; color: white;">Tickets & Bookings</h3>
                <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 0; line-height: 1.5;">Track your purchase, download invoices, or view entry tickets details.</p>
            </div>

            <div class="support-card" style="cursor: default; flex: 1; min-width: 250px; background: rgba(30, 30, 30, 0.75); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 25px; text-align: center; color: white;">
                <i class="fas fa-user-shield" style="font-size: 30px; color: var(--primary-yellow); margin-bottom: 15px; display: block;"></i>
                <h3 style="margin-bottom: 10px; font-size: 18px; color: white;">Account Support</h3>
                <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 0; line-height: 1.5;">Manage your login details, configure profiles, and update security parameters.</p>
            </div>

            <div class="support-card" style="cursor: default; flex: 1; min-width: 250px; background: rgba(30, 30, 30, 0.75); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 25px; text-align: center; color: white;">
                <i class="fas fa-calendar-check" style="font-size: 30px; color: var(--primary-yellow); margin-bottom: 15px; display: block;"></i>
                <h3 style="margin-bottom: 10px; font-size: 18px; color: white;">Event Organizers</h3>
                <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 0; line-height: 1.5;">Access templates for publishing events, checking lists, and controlling capacity.</p>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="faq-container">
            <div class="faq-header">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions about DIZA Event Ticket.</p>
            </div>

            <div id="faqList">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How do I create an account?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Click on the "Register" button at the top right corner. You can choose to register as an <b>Attendee</b> to buy tickets or an <b>Organizer</b> to host your own events.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can I get a refund for my ticket?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Refund policies depend on the specific event organizer. Generally, tickets are non-refundable unless the event is cancelled. Please check the specific event details page.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>How do I download my ticket?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Once you have purchased a ticket, log in as an Attendee and go to the <b>My Tickets</b> page from your profile dropdown menu. You will find booking receipts, invoices, and your entry QR codes there.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>I am an Organizer. How do I get verified?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>After registering as an organizer, you must submit your organization details in the profile settings. Our Admin team will review your application within 24-48 hours.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>I forgot my password. What should I do?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Go to the Login page and click "Forgot Password?". Enter your registered email address, and we will send you a link to reset your password.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-section">
        <div class="contact-container">
            <h2 style="text-align: center; margin-bottom: 10px;">Still need help?</h2>
            <p style="text-align: center; margin-bottom: 40px; color:#666;">Send us a message and we'll get back to you shortly.</p>
            
            <div class="help-contact-grid">
                
                <!-- Left column: Support Details Panel -->
                <div class="contact-info-panel">
                    <h3>Contact Information</h3>
                    
                    <div class="info-row">
                        <i class="fas fa-phone-alt"></i>
                        <div class="info-text-details">
                            <h4>Support Hotlines</h4>
                            <p>+60 16-262 4834</p>
                            <p>+60 11-6551 3006</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <i class="fas fa-envelope"></i>
                        <div class="info-text-details">
                            <h4>Email Address</h4>
                            <p>support@grab.uitm.edu.my</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <i class="fas fa-clock"></i>
                        <div class="info-text-details">
                            <h4>Operating Hours</h4>
                            <p>Monday - Friday</p>
                            <p>9:00 AM - 5:00 PM (GMT +8)</p>
                        </div>
                    </div>

                    <div class="info-row" style="margin-bottom:0;">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="info-text-details">
                            <h4>Location</h4>
                            <p>UiTM Kampus Machang,</p>
                            <p>Kelantan, Malaysia</p>
                        </div>
                    </div>
                </div>

                <!-- Right column: Message Form -->
                <form class="contact-form" style="margin: 0; padding: 30px; border: 1px solid #eee; border-radius: 10px; background:#fff;" onsubmit="event.preventDefault(); alert('Message sent successfully! We will contact you soon.');">
                    <div class="form-group">
                        <label>Your Email</label>
                        <input type="email" class="form-control" placeholder="name@example.com" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; margin-top:5px;">
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Subject</label>
                        <input type="text" class="form-control" placeholder="Brief summary of issue" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; margin-top:5px;">
                    </div>
                    <div class="form-group" style="margin-top: 15px; margin-bottom: 20px;">
                        <label>Message</label>
                        <textarea class="form-control" placeholder="Describe your issue in detail..." required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; height: 120px; font-family:inherit; margin-top:5px;"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; border:none; border-radius:6px; padding: 14px; font-size:16px; cursor:pointer;">Send Message</button>
                </form>
                
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
                    <p><b>Email:</b> <br>support@grab.uitm.edu.my</p>
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

    <script>
        // FAQ Accordion toggling
        const faqQuestions = document.querySelectorAll('.faq-question');
        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                
                // Toggle the active class
                item.classList.toggle('active');
            });
        });
    </script>

</body>
</html>