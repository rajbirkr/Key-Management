<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($name) || empty($department) || empty($phone) || empty($password)) {
        $error_message = "All fields are required!";
    } else {
        // Check if phone already registered
        $check_sql = "SELECT id FROM users WHERE phone = '$phone' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Phone number already registered. Please login.";
        } else {
            // Insert user into database
            $insert_sql = "INSERT INTO users (name, department, phone, password, role) VALUES ('$name', '$department', '$phone', '$password', '$role')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $success_message = "Registration successful! You can now login.";
            } else {
                $error_message = "Error registering user: " . mysqli_error($conn);
            }
        }
    }
}

include 'header.php';
?>

<div class="auth-box">
    <h2>Create Account</h2>
    <p class="subtitle">Join ATLAS HUB Campus Space System</p>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
            <p style="margin-top: 8px;"><a href="login.php" style="color: #10b981; font-weight: bold;">Click here to Login</a></p>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="role">Select Role</label>
            <select name="role" id="role" class="form-control">
                <option value="user">Student / User</option>
                <!-- <option value="admin">Administrator</option> -->
            </select>
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Raj Thakur" required>
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" name="department" id="department" class="form-control" placeholder="e.g. Computer Science" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" class="form-control" placeholder="e.g. 9876543210" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Create password" required>
        </div>

        <button type="submit" class="btn btn-block">Register Account</button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 13px; color: #94a3b8;">
        Already registered? <a href="login.php" style="color: #6366f1; text-decoration: none; font-weight: bold;">Login here</a>
    </div>
</div>

<?php include 'footer.php'; ?>
