<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$floor_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch floor details
$floor_sql = "SELECT * FROM floors WHERE id = $floor_id LIMIT 1";
$floor_result = mysqli_query($conn, $floor_sql);

if (!$floor_result || mysqli_num_rows($floor_result) === 0) {
    die("Invalid Floor ID.");
}

$floor = mysqli_fetch_assoc($floor_result);

// Handle new booking submission
$alert_msg = "";
$alert_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $category = trim($_POST['category']);
    $department = trim($_POST['department']);
    $phone_number = trim($_POST['phone_number']);
    $user_phone = $_SESSION['user_phone'];
    $user_name = $_SESSION['user_name'];

    if (empty($room_number) || empty($department) || empty($phone_number)) {
        $alert_msg = "Please fill in all booking details.";
        $alert_type = "danger";
    } else {
        // Check for existing pending/approved booking
        $check_sql = "SELECT id FROM bookings WHERE floor_id = $floor_id AND room_number = '$room_number' AND status IN ('pending', 'approved') LIMIT 1";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $alert_msg = "Room $room_number is already booked or awaiting approval.";
            $alert_type = "danger";
        } else {
            // Save booking
            $insert_sql = "INSERT INTO bookings (floor_id, room_number, category, department, phone_number, user_phone, user_name, status) 
                           VALUES ($floor_id, '$room_number', '$category', '$department', '$phone_number', '$user_phone', '$user_name', 'pending')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $alert_msg = "Room $room_number booking request submitted successfully! Awaiting admin approval.";
                $alert_type = "success";
            } else {
                $alert_msg = "Database Error: " . mysqli_error($conn);
                $alert_type = "danger";
            }
        }
    }
}

// Fetch all bookings for this floor to mark booked/pending rooms
$floor_bookings_sql = "SELECT room_number, status FROM bookings WHERE floor_id = $floor_id AND status IN ('approved', 'pending')";
$floor_bookings_res = mysqli_query($conn, $floor_bookings_sql);

$booked_rooms = [];
while ($row = mysqli_fetch_assoc($floor_bookings_res)) {
    $booked_rooms[$row['room_number']] = $row['status'];
}

// Generate room list based on floor id
$base = $floor_id * 100;
$classrooms = [$base + 1, $base + 2, $base + 3, $base + 4];
$laboratories = [$base + 21, $base + 22, $base + 23, $base +24,$base +25];

include __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="/Key-Management-main/index.php" style="color: #6366f1; text-decoration: none; font-size: 13px; font-weight: bold;">&larr; Back to Dashboard</a>
    <h1 style="font-size: 26px; color: #ffffff; margin-top: 10px;"><?php echo htmlspecialchars($floor['floor_name']); ?></h1>
    <p style="color: #94a3b8; font-size: 14px;"><?php echo htmlspecialchars($floor['description']); ?></p>
</div>

<?php if (!empty($alert_msg)): ?>
    <div class="alert alert-<?php echo $alert_type; ?>">
        <?php echo $alert_msg; ?>
    </div>
<?php endif; ?>

<!-- Classrooms Grid -->
<h3 style="font-size: 18px; color: #ffffff; margin-bottom: 12px; margin-top: 25px;">Classroom Directory</h3>
<div class="room-grid">
    <?php foreach ($classrooms as $rm): ?>
        <?php 
            $status = isset($booked_rooms[$rm]) ? $booked_rooms[$rm] : 'available';
            $card_class = "room-card " . ($status === 'approved' ? 'booked' : ($status === 'pending' ? 'pending' : ''));
        ?>
        <div class="<?php echo $card_class; ?>" onclick="selectRoom('<?php echo $rm; ?>', 'classroom')">
            <div style="font-size: 11px; color: #94a3b8; font-weight: bold;">CLASSROOM</div>
            <div style="font-size: 20px; font-weight: bold; color: #ffffff; margin: 6px 0;">Room <?php echo $rm; ?></div>
            <span class="badge-status badge-<?php echo $status === 'approved' ? 'rejected' : ($status === 'pending' ? 'pending' : 'approved'); ?>">
                <?php echo strtoupper($status); ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Laboratories Grid -->
<h3 style="font-size: 18px; color: #ffffff; margin-bottom: 12px; margin-top: 30px;">Laboratory Units</h3>
<div class="room-grid">
    <?php foreach ($laboratories as $rm): ?>
        <?php 
            $status = isset($booked_rooms[$rm]) ? $booked_rooms[$rm] : 'available';
            $card_class = "room-card " . ($status === 'approved' ? 'booked' : ($status === 'pending' ? 'pending' : ''));
        ?>
        <div class="<?php echo $card_class; ?>" onclick="selectRoom('<?php echo $rm; ?>', 'laboratory')">
            <div style="font-size: 11px; color: #94a3b8; font-weight: bold;">LABORATORY</div>
            <div style="font-size: 20px; font-weight: bold; color: #ffffff; margin: 6px 0;">Room <?php echo $rm; ?></div>
            <span class="badge-status badge-<?php echo $status === 'approved' ? 'rejected' : ($status === 'pending' ? 'pending' : 'approved'); ?>">
                <?php echo strtoupper($status); ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<!-- Booking Form Box -->
<div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 25px; margin-top: 40px;">
    <h3 style="font-size: 18px; color: #ffffff; margin-bottom: 15px;">Book a Room on <?php echo htmlspecialchars($floor['floor_name']); ?></h3>

    <form action="floor.php?id=<?php echo $floor_id; ?>" method="POST">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group">
                <label for="room_number">Room Number</label>
                <input type="text" name="room_number" id="selected_room_input" class="form-control" placeholder="Click room above or type code e.g. 101" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="selected_category_input" class="form-control">
                    <option value="classroom">Classroom</option>
                    <option value="laboratory">Laboratory</option>
                </select>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" name="department" id="department" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_dept']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Contact Phone</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_phone']); ?>" required>
            </div>
        </div>

        <button type="submit" class="btn" style="margin-top: 10px;">Submit Room Reservation</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
