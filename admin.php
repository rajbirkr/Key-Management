<?php
require_once __DIR__ . '/includes/config.php';

// Ensure user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("<div style='padding: 30px; font-family: sans-serif; color: red;'>Access Denied: You must be logged in as an Administrator to view this page. <a href='login.php'>Login here</a></div>");
}

$msg = "";

// Handle Approve / Reject action
// NOTE: this used to also be duplicated inside the row-rendering loop below,
// which meant it re-ran the same update (using the URL's booking_id, not the
// row being rendered) once per pending row on the page, and only synced the
// floor tied to the URL's booking rather than the row in question. That is
// what was corrupting the floors.occupied / occupancy_rate figures and, in
// turn, the progress bar on the dashboard. It now runs exactly once here.
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = $_GET['action'];
    $booking_id = intval($_GET['booking_id']);

    $booking_lookup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT floor_id, room_number, user_name, user_phone FROM bookings WHERE id = $booking_id"));

    if ($booking_lookup) {
        $fid = intval($booking_lookup['floor_id']);

        if ($action === 'approve') {
            mysqli_query($conn, "UPDATE bookings SET status = 'approved' WHERE id = $booking_id");
            $msg = "Booking #$booking_id successfully APPROVED!";

            // Create (or re-open) the key record for this room
            $room_number = mysqli_real_escape_string($conn, $booking_lookup['room_number']);
            $holder_name = mysqli_real_escape_string($conn, $booking_lookup['user_name']);
            $holder_phone = mysqli_real_escape_string($conn, $booking_lookup['user_phone']);

            $existing_key = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM key_status WHERE floor_id = $fid AND room_number = '$room_number'"));
            if ($existing_key) {
                mysqli_query($conn, "UPDATE key_status SET booking_id = $booking_id, status = 'issued', held_by = '$holder_name', held_by_phone = '$holder_phone', issued_at = NOW(), returned_at = NULL WHERE id = " . intval($existing_key['id']));
            } else {
                mysqli_query($conn, "INSERT INTO key_status (floor_id, room_number, booking_id, status, held_by, held_by_phone, issued_at) VALUES ($fid, '$room_number', $booking_id, 'issued', '$holder_name', '$holder_phone', NOW())");
            }
        } elseif ($action === 'reject') {
            mysqli_query($conn, "UPDATE bookings SET status = 'rejected' WHERE id = $booking_id");
            $msg = "Booking #$booking_id REJECTED.";
        }

        // Recompute occupied AND occupancy_rate together from the same count,
        // so the two columns can never drift apart from each other.
        $occ_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM bookings WHERE floor_id = $fid AND status = 'approved'"));
        $occupied_count = intval($occ_row['cnt']);

        $floor_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT total_rooms FROM floors WHERE id = $fid"));
        $total_rooms = intval($floor_row['total_rooms']);
        $rate = $total_rooms > 0 ? min(100, round(($occupied_count / $total_rooms) * 100)) : 0;

        mysqli_query($conn, "UPDATE floors SET occupied = $occupied_count, occupancy_rate = $rate WHERE id = $fid");
    }
}

// Handle Key Status actions (Return / Report Lost)
if (isset($_GET['key_action']) && isset($_GET['key_id'])) {
    $key_action = $_GET['key_action'];
    $key_id = intval($_GET['key_id']);

    if ($key_action === 'return') {
        mysqli_query($conn, "UPDATE key_status SET status = 'in_cabinet', held_by = NULL, held_by_phone = NULL, returned_at = NOW() WHERE id = $key_id");
        $msg = "Key #$key_id marked as RETURNED.";
    } elseif ($key_action === 'lost') {
        mysqli_query($conn, "UPDATE key_status SET status = 'lost' WHERE id = $key_id");
        $msg = "Key #$key_id reported LOST.";
    }
}

// Maps a key_status value onto the existing badge-* CSS classes
function key_badge_class($status) {
    switch ($status) {
        case 'in_cabinet': return 'approved';
        case 'issued': return 'pending';
        case 'lost': return 'rejected';
        default: return 'pending';
    }
}

// Fetch all bookings
$all_bookings_sql = "SELECT b.*, f.floor_name FROM bookings b JOIN floors f ON b.floor_id = f.id ORDER BY b.id DESC";
$all_bookings_res = mysqli_query($conn, $all_bookings_sql);

// Fetch all key statuses
$all_keys_sql = "SELECT k.*, f.floor_name FROM key_status k JOIN floors f ON k.floor_id = f.id ORDER BY f.id ASC, k.room_number ASC";
$all_keys_res = mysqli_query($conn, $all_keys_sql);

include __DIR__ . '/includes/header.php';
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

<div class="table-container" style="margin-top: 30px;">
    <h3 style="font-size: 18px; color: #ffffff; margin-bottom: 15px;">Key Status Register</h3>

    <?php if (mysqli_num_rows($all_keys_res) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Floor</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Held By</th>
                    <th>Held Phone</th>
                    <th>Issued At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($k = mysqli_fetch_assoc($all_keys_res)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($k['floor_name']); ?></td>
                        <td><strong>Room <?php echo htmlspecialchars($k['room_number']); ?></strong></td>
                        <td>
                            <span class="badge-status badge-<?php echo key_badge_class($k['status']); ?>">
                                <?php echo strtoupper(htmlspecialchars(str_replace('_', ' ', $k['status']))); ?>
                            </span>
                        </td>
                        <td><?php echo $k['held_by'] ? htmlspecialchars($k['held_by']) : '-'; ?></td>
                        <td><?php echo $k['held_by_phone'] ? htmlspecialchars($k['held_by_phone']) : '-'; ?></td>
                        <td><?php echo $k['issued_at'] ? htmlspecialchars($k['issued_at']) : '-'; ?></td>
                        <td>
                            <?php if ($k['status'] === 'issued'): ?>
                                <a href="admin.php?key_action=return&key_id=<?php echo $k['id']; ?>" class="btn btn-sm btn-success">Mark Returned</a>
                                <a href="admin.php?key_action=lost&key_id=<?php echo $k['id']; ?>" class="btn btn-sm btn-danger">Report Lost</a>
                            <?php elseif ($k['status'] === 'lost'): ?>
                                <a href="admin.php?key_action=return&key_id=<?php echo $k['id']; ?>" class="btn btn-sm btn-success">Mark Recovered</a>
                            <?php else: ?>
                                <span style="color: #64748b; font-size: 12px;">In Cabinet</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #94a3b8; font-size: 13px; padding: 20px;">
            No keys have been issued yet. Approving a booking automatically creates a key record here.
        </p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
