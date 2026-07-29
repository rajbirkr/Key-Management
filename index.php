<?php
require_once __DIR__ . '/includes/config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['user_role'];
$user_phone = $_SESSION['user_phone'];

// Fetch stats metrics
if ($user_role === 'admin') {
    $total_sql = "SELECT COUNT(*) as cnt FROM bookings";
    $approved_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE status='approved'";
    $pending_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE status='pending'";
} else {
    $total_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE user_phone='$user_phone'";
    $approved_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE user_phone='$user_phone' AND status='approved'";
    $pending_sql = "SELECT COUNT(*) as cnt FROM bookings WHERE user_phone='$user_phone' AND status='pending'";
}

$total_res = mysqli_fetch_assoc(mysqli_query($conn, $total_sql))['cnt'];
$approved_res = mysqli_fetch_assoc(mysqli_query($conn, $approved_sql))['cnt'];
$pending_res = mysqli_fetch_assoc(mysqli_query($conn, $pending_sql))['cnt'];

// Fetch floors with occupancy rate computed live from approved bookings
$floors_sql = "SELECT * FROM floors ORDER BY id ASC";
$floors_result = mysqli_query($conn, $floors_sql);
// Fetch user's bookings
$bookings_sql = "SELECT b.*, f.floor_name FROM bookings b JOIN floors f ON b.floor_id = f.id WHERE b.user_phone = '$user_phone' ORDER BY b.id DESC";
$bookings_result = mysqli_query($conn, $bookings_sql);

include __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div style="margin-bottom: 25px;">
    <h1 style="font-size: 26px; color: #ffffff;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
    <p style="color: #94a3b8; font-size: 14px; margin-top: 5px;">
        Explore campus floors below to request classrooms and laboratories for your academic study groups.
    </p>
</div>

<!-- Metrics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h4>Total Bookings</h4>
        <div class="number"><?php echo $total_res; ?></div>
    </div>
    <div class="stat-card">
        <h4>Approved Requests</h4>
        <div class="number" style="color: #34d399;"><?php echo $approved_res; ?></div>
    </div>
    <div class="stat-card">
        <h4>Pending Approval</h4>
        <div class="number" style="color: #fbbf24;"><?php echo $pending_res; ?></div>
    </div>
    <div class="stat-card">
        <h4>System Status</h4>
        <div class="number" style="color: #818cf8; font-size: 20px; margin-top: 6px;">ACTIVE NODE</div>
    </div>
</div>

<!-- Section Heading -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 20px; color: #ffffff;">Floor Infrastructure Status</h2>
        <p style="color: #94a3b8; font-size: 13px;">Click any floor to view available classrooms and laboratories</p>
    </div>
</div>

<!-- Floors Grid -->
<div class="floor-grid">
    <?php while ($floor = mysqli_fetch_assoc($floors_result)): ?>
        <div class="floor-card">
            <div>
                <h3><?php echo htmlspecialchars($floor['floor_name']); ?></h3>
                <p><?php echo htmlspecialchars($floor['description']); ?></p>
                
                <div style="font-size: 12px; color: #cbd5e1; display: flex; justify-content: space-between;">
                    <span>Occupancy Rate</span>
                    <?php $rate = $floor['total_rooms'] > 0 ? min(100, round(($floor['occupied'] / $floor['total_rooms']) * 100)) : 0; ?>
                    <strong><?php echo $rate; ?>%</strong>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $rate; ?>%;"></div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; color: #94a3b8;"><?php echo $floor['total_rooms']; ?> Rooms</span>
                <a href="floor.php?id=<?php echo $floor['id']; ?>" class="btn btn-sm">View Rooms &rarr;</a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- User Bookings Log -->
<h2 style="font-size: 20px; color: #ffffff; margin-bottom: 15px;">Your Booking Requests</h2>
<div class="table-container">
    <?php if (mysqli_num_rows($bookings_result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Floor</th>
                    <th>Room</th>
                    <th>Category</th>
                    <th>Department</th>
                    <th>Contact Phone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($b = mysqli_fetch_assoc($bookings_result)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($b['floor_name']); ?></strong></td>
                        <td>Room <?php echo htmlspecialchars($b['room_number']); ?></td>
                        <td><span style="text-transform: uppercase; font-size: 11px;"><?php echo htmlspecialchars($b['category']); ?></span></td>
                        <td><?php echo htmlspecialchars($b['department']); ?></td>
                        <td><?php echo htmlspecialchars($b['phone_number']); ?></td>
                        <td>
                            <span class="badge-status badge-<?php echo strtolower($b['status']); ?>">
                                <?php echo htmlspecialchars($b['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #94a3b8; font-size: 13px; padding: 20px;">
            You have not made any room booking requests yet. Click a floor above to get started!
        </p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
