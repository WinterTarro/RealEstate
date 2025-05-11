<?php
$pageTitle = "Contact Us";
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Process contact form
$formSubmitted = false;
$formSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    
    // Validate form inputs
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitize($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error_message'] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Please enter a valid email address.';
    } else {
        // Send email
        $to = "info@realestate.com"; // Change this to your actual email
        $subject = "Contact Form: " . $subject;
        $email_message = "Name: " . $name . "\n";
        $email_message .= "Email: " . $email . "\n\n";
        $email_message .= "Message:\n" . $message;
        
        $headers = "From: " . $email . "\r\n";
        
        if(mail($to, $subject, $email_message, $headers)) {
            $formSuccess = true;
            $_SESSION['success_message'] = 'Your message has been sent successfully. We will get back to you soon.';
        } else {
            $_SESSION['error_message'] = 'There was an error sending your message. Please try again later.';
        }
    }
}

require_once 'includes/header.php';
?>

<h1>Contact Us</h1>

<div class="contact-container">
    <div class="contact-info">
        <h3>Get in Touch</h3>
        <p>Have questions about our properties or services? Reach out to our team and we'll be happy to assist you.</p>
        
        <div class="contact-info-item">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <h4>Our Office</h4>
                <p>123 Real Estate Avenue<br>Los Angeles, CA 90001<br>United States</p>
            </div>
        </div>
        
        <div class="contact-info-item">
            <i class="fas fa-phone"></i>
            <div>
                <h4>Phone</h4>
                <p>+1 (555) 123-4567</p>
                <p>Mon-Fri: 9:00 AM - 5:00 PM</p>
            </div>
        </div>
        
        <div class="contact-info-item">
            <i class="fas fa-envelope"></i>
            <div>
                <h4>Email</h4>
                <p>info@realestate.com</p>
                <p>support@realestate.com</p>
            </div>
        </div>
        
        <div class="contact-info-item">
            <i class="fas fa-clock"></i>
            <div>
                <h4>Working Hours</h4>
                <p>Monday - Friday: 9:00 AM - 5:00 PM</p>
                <p>Saturday: 10:00 AM - 2:00 PM</p>
                <p>Sunday: Closed</p>
            </div>
        </div>
    </div>
    
    <div class="contact-form-container">
        <h3>Send a Message</h3>
        
        <?php if ($formSubmitted && $formSuccess): ?>
            <div class="alert alert-success">
                Your message has been sent successfully. We will get back to you soon.
            </div>
        <?php else: ?>
            <form action="contact.php" method="POST" class="needs-validation">
                <div class="form-group">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<section>
    <h2 class="section-title">Our Location</h2>
    <div id="office-map" style="height: 400px; border-radius: 8px; margin-bottom: 40px;"></div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize office location map
    const officeMap = document.getElementById('office-map');
    if (officeMap) {
        // Los Angeles coordinates
        const latitude = 34.0522;
        const longitude = -118.2437;
        
        // Create map centered on office
        const map = L.map('office-map').setView([latitude, longitude], 13);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add marker for office
        const marker = L.marker([latitude, longitude]).addTo(map);
        
        // Add popup
        marker.bindPopup('<strong>Real Estate Headquarters</strong><br>123 Real Estate Avenue<br>Los Angeles, CA 90001').openPopup();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
