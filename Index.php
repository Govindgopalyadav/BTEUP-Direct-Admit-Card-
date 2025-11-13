<?php
// =========================================================
// PHP Server-Side Logic for Global Visitor Counter
// =========================================================

// Define the file to store the visitor count (MUST BE WRITABLE by the web server)
$counter_file = 'visitor_count.txt';
// Capture the exact time the server processes this page
$server_time = date('F j, Y, g:i:s a T');

$display_count = 'N/A'; // Default value in case of error

try {
    // 1. Read the current count
    if (file_exists($counter_file)) {
        // Read contents, trim whitespace, and convert to integer
        $current_count = (int)trim(file_get_contents($counter_file));
    } else {
        // If file doesn't exist, start count at 0
        $current_count = 0;
    }

    // 2. Increment the count
    // Use a unique session key to prevent a single user refresh from counting multiple times
    // Note: session_start() must be called before any output, but is often already handled by hosting environment.
    // For maximum compatibility in a simple file, we will rely on file incrementing on every page load.
    
    $new_count = $current_count + 1;

    // 3. Write the new count back to the file
    // LOCK_EX prevents race conditions if multiple users hit the page simultaneously
    file_put_contents($counter_file, $new_count, LOCK_EX); 
    
    // Set the count variable for display, formatted with commas
    $display_count = number_format($new_count);

} catch (Exception $e) {
    // Display error message if file access failed
    $display_count = 'Error: File access failed. Check permissions!';
}

// =========================================================
// HTML/CSS/JavaScript output starts here
// =========================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alphanumeric Link Generator (PHP)</title>
    <!-- Use a standard, readable mobile-friendly font stack -->
    <style>
        :root {
            --primary-color: #007aff;
            --primary-color-dark: #005bb5;
            --secondary-color: #ff9500;
            --secondary-color-dark: #cc7000;
            --disabled-color: #ccc;
            --error-color: #ff3b30;
            --text-color: #333;
            --spacing-unit: 16px;
        }

        /* 1. Overall Layout & Mobile Focus */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100dvh;
            background-color: #f0f0f5;
        }

        .container {
            width: 100%;
            max-width: 768px;
            padding: var(--spacing-unit);
            box-sizing: border-box;
            border-radius: 12px;
            margin-top: var(--spacing-unit);
        }

        /* 2. Form Styling */
        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
            font-weight: 600;
        }

        /* 3. Input Field (Large, Tappable Target) */
        #trackingId {
            width: 100%;
            height: 48px;
            padding: 0 12px;
            font-size: 1.1rem;
            line-height: 48px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        #trackingId:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.25);
        }

        /* 4. Feedback/Error Styling */
        .feedback {
            min-height: 1.5rem;
            margin-top: -8px;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: var(--error-color);
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .feedback.visible {
            visibility: visible;
            opacity: 1;
        }

        /* 5. Action Button Styling (NON-STICKY) */
        .conversion-btn {
            width: 100%;
            min-height: 56px;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s, opacity 0.3s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 16px;
            display: block;
        }
        
        #convertBtn {
            background-color: var(--primary-color);
            color: white;
        }

        #convertBtn:not([disabled]):active {
            background-color: var(--primary-color-dark);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        #convertBtn2 {
            background-color: var(--secondary-color);
            color: white;
        }

        #convertBtn2:not([disabled]):active {
            background-color: var(--secondary-color-dark);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        /* Disabled State (Applies to both) */
        .conversion-btn[disabled] {
            background-color: var(--disabled-color) !important;
            color: #666;
            cursor: not-allowed;
            opacity: 0.6;
            box-shadow: none;
        }

        /* --- Image Styling for Responsive Fit --- */
        #displayImage {
            width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
        }

        /* --- Footer/Stats Styling --- */
        .footer-stats {
            margin-top: 24px;
            border-top: 1px solid #ddd;
            padding-top: 12px;
            font-size: 0.8rem;
            color: #666;
            text-align: center;
        }
        .footer-stats p {
            margin: 4px 0;
        }

        /* Blogger CSS (Commented for brevity) */
        .sidebar, .post-footer, .blog-pager, .comments, .post-share-buttons-container, .widget, .footer-outer, #navbar-iframe {
            display: none !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Link Generator (PHP)</h1>

        <div class="form-group">
            <label for="trackingId">Enter Your Tracking ID</label>
            <input type="text" id="trackingId" placeholder="Alpha-Numeric ID (5-25 chars)" maxlength="25" autofocus>
        </div>

        <div id="feedback" class="feedback"></div>

        <!-- Button 1 element -->
        <button id="convertBtn" class="conversion-btn" disabled>Convert 1 (Default)</button>
        
        <!-- Button 2 element -->
        <button id="convertBtn2" class="conversion-btn" disabled>Convert 2 (Secondary)</button>
        
        <!-- The image element is below the buttons and fits perfectly -->
        <img id="displayImage" src="https://placehold.co/600x200/4c4c4c/ffffff?text=Your+Image+Goes+Here" alt="Placeholder for content image" />

        <!-- PHP-powered statistics section: values are inserted by PHP when the page is served -->
        <div class="footer-stats">
            <p>Server Last Updated: <span><?php echo $server_time; ?></span></p>
            <p>Global Total Visitors: <span><?php echo $display_count; ?></span></p>
        </div>
    </div>

    <script>
        // --- App Variables (Client-Side) ---
        const BASE_URL = "https://your-provided-link.com/?q=";
        const BASE_URL_2 = "https://alternative-link-tracker.org/id="; 

        const inputField = document.getElementById('trackingId');
        const convertBtn = document.getElementById('convertBtn');
        const convertBtn2 = document.getElementById('convertBtn2');
        const feedbackElement = document.getElementById('feedback');
        
        // --- Validation Logic (Unchanged) ---
        
        function validateInput(value) {
            const minLength = 5;
            const maxLength = 25;
            
            if (value.length < minLength) {
                return `ID must be at least ${minLength} characters long.`;
            }

            if (value.length > maxLength) {
                 return `ID cannot exceed ${maxLength} characters.`;
            }
            
            const alphanumericRegex = /^[a-zA-Z0-9]+$/;
            if (!alphanumericRegex.test(value)) {
                return "Only letters (A-Z, a-z) and numbers (0-9) are allowed.";
            }

            return null; // Valid
        }

        function updateUI() {
            const inputValue = inputField.value.trim();
            const error = validateInput(inputValue);
            const isValid = !error;

            convertBtn.disabled = !isValid;
            convertBtn2.disabled = !isValid; 

            if (error) {
                feedbackElement.textContent = error;
                feedbackElement.classList.add('visible');
            } else {
                feedbackElement.textContent = '';
                feedbackElement.classList.remove('visible');
            }
        }
        
        // --- Event Listeners (Unchanged) ---

        inputField.addEventListener('input', updateUI);

        convertBtn.addEventListener('click', (event) => {
            event.preventDefault();
            const inputValue = inputField.value.trim();
            if (validateInput(inputValue)) { return; }
            const encodedValue = encodeURIComponent(inputValue);
            window.open(BASE_URL + encodedValue, '_blank');
        });

        convertBtn2.addEventListener('click', (event) => {
            event.preventDefault();
            const inputValue = inputField.value.trim();
            if (validateInput(inputValue)) { return; }
            const encodedValue = encodeURIComponent(inputValue);
            window.open(BASE_URL_2 + encodedValue, '_blank');
        });

        // --- Initialization ---
        window.onload = () => {
            inputField.focus(); 
            updateUI(); 
        };

        updateUI();

    </script>

</body>
</html>
