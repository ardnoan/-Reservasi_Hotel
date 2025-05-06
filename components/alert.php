<?php
/**
 * Alert Component for displaying messages to the user
 * 
 * Usage:
 * include 'components/alert.php';
 * displayAlert('success', 'Your reservation has been confirmed!');
 * displayAlert('error', 'Something went wrong.');
 * displayAlert('info', 'Please check your email for confirmation.');
 * displayAlert('warning', 'Room availability is limited.');
 */

function displayAlert($type, $message) {
    // Determine the correct CSS class based on alert type
    $alertClass = 'alert';
    
    switch ($type) {
        case 'success':
            $alertClass .= ' alert-success';
            break;
        case 'info':
            $alertClass .= ' alert-info';
            break;
        case 'warning':
            $alertClass .= ' alert-warning';
            break;
        case 'error':
        case 'danger':
            $alertClass .= ''; // Default is danger
            break;
        default:
            $alertClass .= ''; // Default is danger
    }
    
    // Display the alert
    echo '<div class="' . $alertClass . '">';
    echo '<span class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</span>';
    echo htmlspecialchars($message);
    echo '</div>';
}

// Check for any flash messages in the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    displayAlert($alert['type'], $alert['message']);
    // Clear the flash message
    unset($_SESSION['alert']);
}

/**
 * Set a flash message to be displayed on the next page
 */
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}
?>

<style>
    /* Additional styles for close button not in the main CSS */
    .alert .close-btn {
        float: right;
        cursor: pointer;
        font-weight: bold;
    }
</style>
