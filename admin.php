<?php
require_once 'config.php';

// Ensure user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("<div style='padding: 30px; font-family: sans-serif; color: red;'>Access Denied: You must be logged in as an Administrator to view this page. <a href='login.php'>Login here</a></div>");
}

$msg = "";

// Handle Approve / Reject action
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['booking_id']);

    if ($action === 'approve') {
        $update_sql = "UPDATE bookings SET status = 'approved' WHERE id = $booking_id";
        mysqli_query($conn, $update_sql);
        $msg = "Booking #$booking_id successfully APPROVED!";
    } elseif ($action === 'reject') {
        $update_sql = "UPDATE bookings SET status = 'rejected' WHERE id = $booking_id";
        mysqli_query($conn, $update_sql);
        $msg = "Booking #$booking_id REJECTED.";
    }
}

// Fetch all bookings
$all_bookings_sql = "SELECT b.*, f.floor_name FROM bookings b JOIN floors f ON b.floor_id = f.id ORDER BY b.id DESC";
$all_bookings_res = mysqli_query($conn, $all_bookings_sql);

include 'header.php';
?>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 26px; color: #ffffff;">Admin Approval Panel</h1>
    <p style="color: #94a3b8; font-size: 14px;">Review and manage incoming room clearance requests from students and faculty.</p>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success"><?php echo $msg; ?></div>
<?php endif; ?>

<div class="table-container">
    <h3 style="font-size: 18px; color: #ffffff; margin-bottom: 15px;">All Space Requests</h3>

    <?php if (mysqli_num_rows($all_bookings_res) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Floor</th>
                    <th>Room</th>
                    <th>Applicant Name</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($b = mysqli_fetch_assoc($all_bookings_res)): ?>
                    <tr>
                        <td>#<?php echo $b['id']; ?></td>
                        <td><?php echo htmlspecialchars($b['floor_name']); ?></td>
                        <td><strong>Room <?php echo htmlspecialchars($b['room_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($b['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($b['department']); ?></td>
                        <td><?php echo htmlspecialchars($b['phone_number']); ?></td>
                        <td>
                            <span class="badge-status badge-<?php echo strtolower($b['status']); ?>">
                                <?php echo htmlspecialchars($b['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'pending'): ?>
                                <a href="admin.php?action=approve&booking_id=<?php echo $b['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <a href="admin.php?action=reject&booking_id=<?php echo $b['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                             <?php   if ($action === 'approve') {
                                            $update_sql = "UPDATE bookings SET status = 'approved' WHERE id = $booking_id";
                                            mysqli_query($conn, $update_sql);
                                            $msg = "Booking #$booking_id successfully APPROVED!";
                                            // $select_occupied = "SELECT occupied from floors where id = "

                                        } elseif ($action === 'reject') {
                                            $update_sql = "UPDATE bookings SET status = 'rejected' WHERE id = $booking_id";
                                            mysqli_query($conn, $update_sql);
                                            $msg = "Booking #$booking_id REJECTED.";
                                        }
                                     // Sync the floor's occupied count based on this booking's floor_id
                                        $floor_lookup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT floor_id FROM bookings WHERE id = $booking_id"));
                                        if ($floor_lookup) {
                                            $fid = intval($floor_lookup['floor_id']);
                                            mysqli_query($conn, "UPDATE floors SET occupied = (
                                                SELECT COUNT(*) FROM bookings WHERE floor_id = $fid AND status = 'approved'
                                            ) WHERE id = $fid");
                                        }   
                                        
                                        ?>
                            <?php else: ?>
                
                                <span style="color: #64748b; font-size: 12px;">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #94a3b8; font-size: 13px; padding: 20px;">
            No booking requests in database.
        </p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
