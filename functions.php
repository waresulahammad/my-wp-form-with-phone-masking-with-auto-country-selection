
// Add the contact form shortcode
function custom_contact_form() {
    ob_start(); // Start output buffering
    ?>
    <!-- Add your form -->
    <form id="contactForm" method="post">
        <p>
            <label for="cf_name">Name:</label><br>
            <input type="text" name="cf_name" required>
        </p>
        <p>
            <label for="cf_email">Email:</label><br>
            <input type="email" name="cf_email" required>
        </p>
        <p>
            <label for="cf_phone">Phone Number:</label><br>
            <input 
                id="phone" 
                type="tel" 
                name="cf_phone" 
                required>
        </p>
        <p>
            <label for="cf_message">Message:</label><br>
            <textarea name="cf_message" required></textarea>
        </p>
        <p>
            <input type="submit" name="cf_submitted" value="Send">
        </p>
    </form>

    <!-- Include Intl-Tel-Input JavaScript & CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>

    <!-- Initialize the intl-tel-input with GeoIP -->
    <script>
        const phoneInput = document.querySelector("#phone");

        // Initialize IntlTelInput
        const iti = intlTelInput(phoneInput, {
            initialCountry: "auto", // Automatically detect user's country
            geoIpLookup: (callback) => {
                fetch("https://ipinfo.io/json?token=5658771b6290d8")
                    .then(response => response.json())
                    .then(data => {
                        callback(data.country);
                    })
                    .catch(() => callback("us")); // Default to US on error
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" // Util script for formatting
        });

        // Form validation on submit
        document.getElementById("contactForm").addEventListener("submit", (e) => {
            const isValid = iti.isValidNumber();
            if (!isValid) {
                e.preventDefault(); // Stop form submission
                alert("Please enter a valid phone number.");
            }
        });
    </script>
    <?php

    // Handle the form submission
    if (isset($_POST['cf_submitted'])) {
        custom_handle_form_submission();
    }

    return ob_get_clean(); // Output buffering ends here
}

// Handle form submission
function custom_handle_form_submission() {
    $name = sanitize_text_field($_POST['cf_name']);
    $email = sanitize_email($_POST['cf_email']);
    $phone_number = sanitize_text_field($_POST['cf_phone']);
    $message = sanitize_textarea_field($_POST['cf_message']);

    // Additional server-side phone validation (as backup)
    if (!preg_match('/^\+[1-9][0-9]{6,14}$/', $phone_number)) {
        echo '<p style="color: red;">Invalid phone number format. Please enter a valid international number.</p>';
        return;
    }

    $to = get_option('admin_email'); // Admin email address
    $subject = 'New Contact Form Submission';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    $email_body = "Name: $name<br>Email: $email<br>Phone: $phone_number<br>Message: $message";

    if (wp_mail($to, $subject, $email_body, $headers)) {
        echo '<p style="color: green;">Message sent successfully.</p>';
    } else {
        echo '<p style="color: red;">Failed to send the message. Please try again later.</p>';
    }
}

// Register the shortcode
add_shortcode('custom_contact_form', 'custom_contact_form');
