<?php
include 'security_headers.php';
?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}

include 'Database.php';

if ($_POST) {
    try {
        $database = new Database();
        $db = $database->getConnection();

        // Get form data
        $user_id = $_SESSION['user_id'];
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $last_name = $_POST['last_name'];
        $contact_number = $_POST['contact_number'];
        $address = $_POST['address'];
        $course = $_POST['course'];
        $school_id_number = $_POST['school_id_number'];
        $school_year = $_POST['school_year']; // Now this is year level (First Year, Second Year, etc.)
        $semester = $_POST['semester'];
        $section = $_POST['section'];

        // Validate year level
        $valid_year_levels = ['First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Fifth Year'];
        if (!in_array($school_year, $valid_year_levels)) {
            throw new Exception("Please select a valid year level.");
        }

        // Check if user already has an application
        $check_query = "SELECT id FROM scholarship_applications WHERE user_id = :user_id";
        $stmt = $db->prepare($check_query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            throw new Exception("You have already submitted a scholarship application.");
        }

        // Handle file uploads
        $upload_errors = [];
        
        // School ID upload
        $school_id_path = null;
        if (isset($_FILES['school_id']) && $_FILES['school_id']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "uploads/school_id/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['school_id']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_school_id.' . $file_extension;
            $school_id_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['school_id']['tmp_name'], $school_id_path)) {
                throw new Exception("Failed to upload school ID file.");
            }
        } else {
            throw new Exception("Please upload your school ID.");
        }

        // Proof of Enrollment upload
        $proof_of_enrollment_path = null;
        if (isset($_FILES['proof_of_enrollment']) && $_FILES['proof_of_enrollment']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "uploads/proof_of_enrollment/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['proof_of_enrollment']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_enrollment.' . $file_extension;
            $proof_of_enrollment_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['proof_of_enrollment']['tmp_name'], $proof_of_enrollment_path)) {
                // Clean up the first file if second fails
                if ($school_id_path && file_exists($school_id_path)) {
                    unlink($school_id_path);
                }
                throw new Exception("Failed to upload proof of enrollment file.");
            }
        } else {
            // Clean up the first file if second is missing
            if ($school_id_path && file_exists($school_id_path)) {
                unlink($school_id_path);
            }
            throw new Exception("Please upload your proof of enrollment.");
        }

        // Insert scholarship application with new fields
        $query = "INSERT INTO scholarship_applications 
                  SET user_id=:user_id, first_name=:first_name, middle_name=:middle_name, 
                  last_name=:last_name, contact_number=:contact_number, address=:address, 
                  course=:course, school_id_number=:school_id_number, school_year=:school_year,
                  semester=:semester, section=:section, school_id_image=:school_id_image,
                  proof_of_enrollment=:proof_of_enrollment, application_date=NOW(), status='pending'";

        $stmt = $db->prepare($query);

        // Bind values
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':course', $course);
        $stmt->bindParam(':school_id_number', $school_id_number);
        $stmt->bindParam(':school_year', $school_year);
        $stmt->bindParam(':semester', $semester);
        $stmt->bindParam(':section', $section);
        $stmt->bindParam(':school_id_image', $school_id_path);
        $stmt->bindParam(':proof_of_enrollment', $proof_of_enrollment_path);

        // Execute query
        if ($stmt->execute()) {
            $_SESSION['application_success'] = "Your scholarship application has been submitted successfully! We will review your application and notify you once a decision has been made.";
            header("Location: Dashboard.php");
            exit();
        } else {
            // Clean up uploaded files if database insert fails
            if ($school_id_path && file_exists($school_id_path)) {
                unlink($school_id_path);
            }
            if ($proof_of_enrollment_path && file_exists($proof_of_enrollment_path)) {
                unlink($proof_of_enrollment_path);
            }
            throw new Exception("Unable to submit application. Please try again.");
        }

    } catch (Exception $exception) {
        $_SESSION['application_error'] = $exception->getMessage();
        header("Location: Dashboard.php");
        exit();
    }
} else {
    header("Location: Dashboard.php");
    exit();
}
?>