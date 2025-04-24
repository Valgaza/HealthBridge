<?php
// Include database configuration
require_once 'config.php';

// Check if user is logged in and is a doctor
require_doctor();

// Check if the ID is provided
if (isset($_GET['id'])) {
    $tip_id = sanitize_input($_GET['id']);

    // Check if the health tip exists and belongs to the logged-in doctor
    $stmt = $conn->prepare("SELECT id FROM health_tips WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param('ii', $tip_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // The health tip exists, proceed with deletion
        $delete_stmt = $conn->prepare("DELETE FROM health_tips WHERE id = ?");
        $delete_stmt->bind_param('i', $tip_id);
        
        if ($delete_stmt->execute()) {
            // Redirect back to health tips page after successful deletion
            header("Location: ../doctor/health-tips.php");
            exit();
        } else {
            echo "Error deleting health tip: " . $delete_stmt->error;
        }
        
        $delete_stmt->close();
    } else {
        echo "Health tip not found or you do not have permission to delete it.";
    }
    
    $stmt->close();
} else {
    echo "No health tip ID provided.";
}

// Close the database connection
mysqli_close($conn);
?>
