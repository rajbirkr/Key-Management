<?php
require_once __DIR__ . '/includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($phone) || empty($password)) {
        $error_message = "Please enter both phone number and password.";
    } else {
        // Query user from database
        $sql = "SELECT * FROM users WHERE phone = '$phone' AND role = '$role' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Simple password check (For real production, use password_verify)
            if ($password === $user['password']) {
                // Set Session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_dept'] = $user['department'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_role'] = $user['role'];

                if($role=='admin'){
                    header("Location: admin.php");
                }else{
                    header("Location: index.php");
                }
                exit;
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "No account found with this phone number and role. Please register first.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-box">
    <h2>Access Portal</h2>
    <p class="subtitle">Campus Space Manager - Login</p>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" class="form-control">
                <option value="user">Student / User</option>
                <!-- <option value="admin">Administrator</option> -->
            </select>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" class="form-control" placeholder="e.g. 9876543210" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-block">Login to Portal</button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 13px; color: #94a3b8;">
        Don't have an account? <a href="/Key-Management-main/register.php" style="color: #6366f1; text-decoration: none; font-weight: bold;">Register here</a>
    </div>

    <!-- Demo Credentials box -->
    <!-- <div style="margin-top: 25px; padding: 12px; background-color: #0f172a; border-radius: 8px; font-size: 12px; color: #94a3b8;">
        <strong style="color: #cbd5e1;">Demo Credentials:</strong><br>
        • User Role: Phone <code>9876543210</code> / Pass <code>user</code><br>
        • Admin Role: Phone <code>1234567890</code> / Pass <code>admin</code>
    </div> -->
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
