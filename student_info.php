<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Get user role from session
$user_role = $_SESSION['user_role'] ?? 'student';

// Define permissions based on role
$can_edit = ($user_role == 'admin' || $user_role == 'faculty');
$can_view = true; // All roles can view

// Handle form submission for updates
if ($can_edit && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    // In a real application, you would validate and sanitize all inputs
    
    // Connect to database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "spcf_portal";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Get student ID from form
    $student_id = $_POST['student_id'];
    
    // Update student basic info
    $sql = "UPDATE students SET 
            name = ?, 
            email = ?, 
            courses = ?, 
            year_level = ?,
            status = ?,
            birthdate = ?,
            gender = ?,
            address = ?,
            phone = ?
            WHERE student_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", 
        $_POST['name'],
        $_POST['email'],
        $_POST['courses'], // Changed from $_POST['course'] to $_POST['courses'] to match the field name
        $_POST['year_level'],
        $_POST['status'],
        $_POST['birthdate'],
        $_POST['gender'],
        $_POST['address'],
        $_POST['phone'],
        $student_id
    );
    $stmt->execute();
    
    // Update emergency contact info
    $sql = "UPDATE emergency_contacts SET
            contact_name = ?,
            relationship = ?,
            contact_phone = ?
            WHERE student_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", 
        $_POST['emergency_contact_name'],
        $_POST['emergency_contact_relationship'],
        $_POST['emergency_contact_phone'],
        $student_id
    );
    $stmt->execute();
    
    // Update education background
    $sql = "UPDATE education_background SET
            high_school = ?,
            college = ?
            WHERE student_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", 
        $_POST['high_school'],
        $_POST['college'],
        $student_id
    );
    $stmt->execute();
    
    // Update family and work info
    $sql = "UPDATE additional_info SET
            family_members = ?,
            work_experience = ?
            WHERE student_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", 
        $_POST['family_members'],
        $_POST['work_experience'],
        $student_id
    );
    $stmt->execute();
    
    $conn->close();
    
    // Redirect to the same page to prevent form resubmission
    header("Location: student_info.php?id=" . $student_id . "&updated=true");
    exit();
}

// Get student ID from URL parameter or use current user's ID
$view_student_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

// In a real application, fetch this data from the database using the student ID
// For demonstration, we'll use the sample data
$student_info = [
    'name' => 'John Doe',
    'student_id' => '20230001',
    'email' => 'john.doe@spcf.edu',
    'courses' => 'Bachelor of Science in Computer Science',
    'year_level' => '3rd Year',
    'status' => 'Active',
    'birthdate' => 'January 1, 2003',
    'gender' => 'Male',
    'address' => '123 Main Street, Cityville',
    'phone' => '09123456789',
    'emergency_contact_name' => 'Jane Doe',
    'emergency_contact_relationship' => 'Mother',
    'emergency_contact_phone' => '09876543210',
    'high_school' => 'City High School',
    'college' => 'SPCF University',
    'family_members' => 'Father: John Sr., Mother: Jane',
    'work_experience' => 'Intern at Tech Solutions Inc.'
];

// Display success message if student info was updated
$updated_message = isset($_GET['updated']) ? '<div class="success-message">Student information updated successfully!</div>' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - SPCF PORTAL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073e6;
            --secondary-color: #005bb5;
            --accent-color: #003d80;
            --background-color: #f4f4f4;
            --text-color: #333;
            --light-text: #777;
            --border-color: #ddd;
            --success-color: #4CAF50;
            --error-color: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #fff;
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .sidebar-header img {
            width: 40px;
            margin-right: 10px;
        }
        
        .school-name {
            font-size: 16px;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-category {
            color: var(--light-text);
            font-size: 12px;
            text-transform: uppercase;
            padding: 10px 20px;
            letter-spacing: 0.5px;
        }
        
        .menu-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: #f0f4f8;
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .role-student {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .role-faculty {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .role-admin {
            background-color: #fce4ec;
            color: #c2185b;
        }
        
        .content-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .info-table th,
        .info-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid var(--border-color);
        }
        
        .info-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            width: 30%;
        }
        
        .info-table tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 115, 230, 0.2);
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: var(--success-color);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid var(--success-color);
        }
        
        .error-message {
            background-color: #ffebee;
            color: var(--error-color);
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid var(--error-color);
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
            text-align: center;
            color: var(--light-text);
            font-size: 14px;
            margin-top: 30px;
        }
        
        /* Media queries */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="school-logo.png" alt="SPCF Logo">
                <div class="school-name">SPCF Portal</div>
            </div>
            
            <nav class="sidebar-menu">
                <a href="index.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="menu-category">Academic</div>
                <a href="student_info.php" class="menu-item active">
                    <i class="fas fa-user-graduate"></i> Student Information
                </a>
                <a href="grading_system.php" class="menu-item">
                    <i class="fas fa-chart-line"></i> Grading System
                </a>
                
                <div class="menu-category">Administration</div>
                <a href="faculty_management.php" class="menu-item">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty Management
                </a>
                <a href="notif.php" class="menu-item">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                
                <div class="menu-category">Account</div>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <a href="index.php?logout=1" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1 class="page-title">Student Information</h1>
                <span class="role-badge role-<?php echo strtolower($user_role); ?>"><?php echo ucfirst($user_role); ?></span>
            </div>
            
            <?php echo $updated_message; ?>
            
            <?php if ($can_edit): ?>
                <!-- Editable Form for Admin/Faculty -->
                <form method="POST" action="student_info.php">
                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_info['student_id']); ?>">
                    
                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">Basic Information</h2>
                        </div>
                        <table class="info-table">
                            <tr>
                                <th>Name</th>
                                <td>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student_info['name']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>Student ID</th>
                                <td><?php echo htmlspecialchars($student_info['student_id']); ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student_info['email']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>Course</th>
                                <td>
                                    <input type="text" name="courses" class="form-control" value="<?php echo htmlspecialchars($student_info['courses']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>Year Level</th>
                                <td>
                                    <select name="year_level" class="form-control">
                                        <option value="1st Year" <?php if($student_info['year_level'] == '1st Year') echo 'selected'; ?>>1st Year</option>
                                        <option value="2nd Year" <?php if($student_info['year_level'] == '2nd Year') echo 'selected'; ?>>2nd Year</option>
                                        <option value="3rd Year" <?php if($student_info['year_level'] == '3rd Year') echo 'selected'; ?>>3rd Year</option>
                                        <option value="4th Year" <?php if($student_info['year_level'] == '4th Year') echo 'selected'; ?>>4th Year</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <select name="status" class="form-control">
                                        <option value="Active" <?php if($student_info['status'] == 'Active') echo 'selected'; ?>>Active</option>
                                        <option value="Inactive" <?php if($student_info['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                                        <option value="On Leave" <?php if($student_info['status'] == 'On Leave') echo 'selected'; ?>>On Leave</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">Personal Information</h2>
                        </div>
                        <table class="info-table">
                            <tr>
                                <th>Birthdate</th>
                                <td>
                                    <input type="text" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($student_info['birthdate']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>
                                    <select name="gender" class="form-control">
                                        <option value="Male" <?php if($student_info['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                        <option value="Female" <?php if($student_info['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                        <option value="Other" <?php if($student_info['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>
                                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($student_info['address']); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>
                                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student_info['phone']); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">Emergency Contact</h2>
                        </div>
                        <table class="info-table">
                            <tr>
                                <th>Name</th>
                                <td>
                                    <input type="text" name="emergency_contact_name" class="form-control" value="<?php echo htmlspecialchars($student_info['emergency_contact_name']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>Relationship</th>
                                <td>
                                    <input type="text" name="emergency_contact_relationship" class="form-control" value="<?php echo htmlspecialchars($student_info['emergency_contact_relationship']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>
                                    <input type="text" name="emergency_contact_phone" class="form-control" value="<?php echo htmlspecialchars($student_info['emergency_contact_phone']); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">Education Background</h2>
                        </div>
                        <table class="info-table">
                            <tr>
                                <th>High School</th>
                                <td>
                                    <input type="text" name="high_school" class="form-control" value="<?php echo htmlspecialchars($student_info['high_school']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>College</th>
                                <td>
                                    <input type="text" name="college" class="form-control" value="<?php echo htmlspecialchars($student_info['college']); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">Family Background</h2>
                        </div>
                        <table class="info-table">
                            <tr>
                                <th>Family Members</th>
                                <td>
                                    <textarea name="family_members" class="form-control" rows="3"><?php echo htmlspecialchars($student_info['family_members']); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h2 class="card-title">Work Experience</h2>
                        </div>
                        <table class="info-table">
                            <tr>
                                <th>Experience</th>
                                <td>
                                    <textarea name="work_experience" class="form-control" rows="3"><?php echo htmlspecialchars($student_info['work_experience']); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" name="update_student" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <!-- View-only tables for Students -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Basic Information</h2>
                    </div>
                    <table class="info-table">
                        <tr>
                            <th>Name</th>
                            <td><?php echo htmlspecialchars($student_info['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Student ID</th>
                            <td><?php echo htmlspecialchars($student_info['student_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($student_info['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Course</th>
                            <td><?php echo htmlspecialchars($student_info['courses']); ?></td>
                        </tr>
                        <tr>
                            <th>Year Level</th>
                            <td><?php echo htmlspecialchars($student_info['year_level']); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><?php echo htmlspecialchars($student_info['status']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Personal Information</h2>
                    </div>
                    <table class="info-table">
                        <tr>
                            <th>Birthdate</th>
                            <td><?php echo htmlspecialchars($student_info['birthdate']); ?></td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td><?php echo htmlspecialchars($student_info['gender']); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo htmlspecialchars($student_info['address']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($student_info['phone']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Emergency Contact</h2>
                    </div>
                    <table class="info-table">
                        <tr>
                            <th>Name</th>
                            <td><?php echo htmlspecialchars($student_info['emergency_contact_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Relationship</th>
                            <td><?php echo htmlspecialchars($student_info['emergency_contact_relationship']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($student_info['emergency_contact_phone']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Education Background</h2>
                    </div>
                    <table class="info-table">
                        <tr>
                            <th>High School</th>
                            <td><?php echo htmlspecialchars($student_info['high_school']); ?></td>
                        </tr>
                        <tr>
                            <th>College</th>
                            <td><?php echo htmlspecialchars($student_info['college']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Family Background</h2>
                    </div>
                    <table class="info-table">
                        <tr>
                            <th>Family Members</th>
                            <td><?php echo htmlspecialchars($student_info['family_members']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Work Experience</h2>
                    </div>
                    <table class="info-table">
                        <tr>
                            <th>Experience</th>
                            <td><?php echo htmlspecialchars($student_info['work_experience']); ?></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> SPCF Portal. All rights reserved.</p>
            </div>
        </main>
    </div>
</body>
</html>