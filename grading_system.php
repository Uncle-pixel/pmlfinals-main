<?php
session_start();

// Handle session variable naming inconsistency between pages
if (!isset($_SESSION['role']) && isset($_SESSION['user_role'])) {
    $_SESSION['role'] = $_SESSION['user_role'];
}

if (!isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Create a placeholder user_id if it doesn't exist but user is logged in
    $_SESSION['user_id'] = $_SESSION['logged_in'];
}

// Consolidated authentication check
if ((!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) && 
    (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true)) {
    header("Location: login.php");
    exit();
}

// Get user role
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : $_SESSION['user_role']; // 'admin', 'faculty', or 'student'

// Process grade updates if form submitted (admin or faculty only)
if (($user_role == 'admin' || $user_role == 'faculty') && isset($_POST['update_grades'])) {
    // In a real application, this is where you would update the database
    // For this example, we'll just show a success message
    $update_message = "Grades updated successfully!";
}

// Example grades data (replace with DB logic)
$grades = [
    ['id' => 1, 'student_name' => 'John Doe', 'course' => 'Introduction to Programming', 'grade' => 'A', 'status' => 'Passed'],
    ['id' => 2, 'student_name' => 'Jane Smith', 'course' => 'Calculus II', 'grade' => 'B', 'status' => 'Passed'],
    ['id' => 3, 'student_name' => 'Mark Johnson', 'course' => 'Physics I', 'grade' => 'F', 'status' => 'Failed']
];

// Define grade options for dropdown
$grade_options = ['A', 'B', 'C', 'D', 'F'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grading System - SPCF PORTAL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073e6;
            --secondary-color: #f9f9f9;
            --accent-color: #00bfa5;
            --text-color: #333;
            --light-text: #777;
            --border-color: #ddd;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
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
        
        .welcome-message {
            font-size: 24px;
            font-weight: 600;
        }
        
        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-left: 10px;
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
        
        .user-actions {
            display: flex;
            align-items: center;
        }
        
        .notification-bell {
            background: none;
            border: none;
            color: #666;
            font-size: 18px;
            margin-right: 20px;
            position: relative;
            cursor: pointer;
        }
        
        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent-color);
            color: white;
            font-size: 10px;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        /* Content Styles */
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
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Table styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .data-table th {
            background-color: #f9f9f9;
            font-weight: 600;
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-passed {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-failed {
            color: #e74c3c;
            font-weight: bold;
        }
        
        /* Form elements */
        select, input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            font-family: inherit;
            font-size: 14px;
        }
        
        select:focus, input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #005bb5;
        }
        
        .btn-edit {
            background-color: #fff8e1;
            color: #f57f17;
            border: 1px solid #ffecb3;
        }
        
        .btn-edit:hover {
            background-color: #ffecb3;
        }
        
        .action-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        
        /* Notification */
        .update-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
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
                <a href="student_info.php" class="menu-item">
                    <i class="fas fa-user-graduate"></i> Student Information
                </a>
                <a href="grading_system.php" class="menu-item active">
                    <i class="fas fa-chart-line"></i> Grading System
                </a>
                
                <div class="menu-category">Administration</div>
                <a href="faculty_management.php" class="menu-item">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty Management
                </a>
                <a href="course_registration.php" class="menu-item">
                    <i class="fas fa-book"></i> Course Registration
                </a>
                <a href="class_scheduling.php" class="menu-item">
                    <i class="fas fa-calendar-alt"></i> Class Scheduling
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
                <div>
                    <h1 class="welcome-message">Grading System 
                        <span class="role-badge role-<?php echo strtolower($user_role); ?>"><?php echo ucfirst($user_role); ?></span>
                    </h1>
                </div>
                <div class="user-actions">
                    <button class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </button>
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U'; ?>
                        </div>
                        <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Grading System Content -->
            <div class="content-card">
                <?php if (isset($update_message)): ?>
                <div class="update-message">
                    <i class="fas fa-check-circle"></i> <?php echo $update_message; ?>
                </div>
                <?php endif; ?>
                
                <div class="card-header">
                    <h2 class="card-title">Student Grades</h2>
                    <?php if ($user_role == 'admin' || $user_role == 'faculty'): ?>
                    <div>
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Grades
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
                <p>
                    <?php if ($user_role == 'student'): ?>
                        View your current grades and academic status.
                    <?php else: ?>
                        Manage and update student grades across all courses.
                    <?php endif; ?>
                </p>
                
                <form method="post" action="">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <?php if ($user_role == 'admin' || $user_role == 'faculty'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($grade['course']); ?></td>
                                <td>
                                    <?php if ($user_role == 'admin' || $user_role == 'faculty'): ?>
                                        <select name="grade[<?php echo $grade['id']; ?>]">
                                            <?php foreach ($grade_options as $option): ?>
                                                <option value="<?php echo $option; ?>" <?php echo ($option == $grade['grade']) ? 'selected' : ''; ?>>
                                                    <?php echo $option; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($grade['grade']); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="<?php echo $grade['status'] === 'Passed' ? 'status-passed' : 'status-failed'; ?>">
                                    <?php echo htmlspecialchars($grade['status']); ?>
                                </td>
                                <?php if ($user_role == 'admin' || $user_role == 'faculty'): ?>
                                <td>
                                    <button type="button" class="btn btn-edit" onclick="editGradeDetails(<?php echo $grade['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit Details
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($user_role == 'admin' || $user_role == 'faculty'): ?>
                    <div class="action-buttons">
                        <button type="submit" name="update_grades" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save All Changes
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Grading System Info Card -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Grading System Information</h2>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Percentage</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>A</td>
                            <td>90% - 100%</td>
                            <td>Excellent</td>
                        </tr>
                        <tr>
                            <td>B</td>
                            <td>80% - 89%</td>
                            <td>Very Good</td>
                        </tr>
                        <tr>
                            <td>C</td>
                            <td>70% - 79%</td>
                            <td>Good</td>
                        </tr>
                        <tr>
                            <td>D</td>
                            <td>60% - 69%</td>
                            <td>Satisfactory</td>
                        </tr>
                        <tr>
                            <td>F</td>
                            <td>Below 60%</td>
                            <td>Failed</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> SPCF Portal. All rights reserved.</p>
            </div>
        </main>
    </div>
    
    <script>
        function editGradeDetails(gradeId) {
            // In a real application, this could open a modal for more detailed editing
            alert("Opening detailed grade editor for ID: " + gradeId);
            // You could implement a modal here for more advanced editing
        }
    </script>
</body>
</html>