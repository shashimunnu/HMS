<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../config/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

// Handle message status updates
if (isset($_POST['action']) && isset($_POST['message_id'])) {
    $message_id = filter_var($_POST['message_id'], FILTER_SANITIZE_NUMBER_INT);
    $action = $_POST['action'];
    
    if ($action === 'mark_read') {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
    } elseif ($action === 'mark_replied') {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $message_id);
        $stmt->execute();
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total number of messages
$result = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$row = $result->fetch_assoc();
$total_messages = $row['total'];
$total_pages = ceil($total_messages / $limit);

// Get messages with pagination
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .message-row { cursor: pointer; }
        .status-new { background-color: #f8f9fa; }
        .message-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <?php include '../../includes/admin_navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Contact Messages</h5>
                        <div>
                            <span class="badge bg-primary me-2">New: <?php 
                                $new = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")->fetch_assoc();
                                echo $new['count'];
                            ?></span>
                            <span class="badge bg-success me-2">Replied: <?php 
                                $replied = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'")->fetch_assoc();
                                echo $replied['count'];
                            ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                        <tr class="message-row <?php echo $message['status'] === 'new' ? 'status-new' : ''; ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                            <td><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                                            <td><?php echo htmlspecialchars($message['subject'] ?? 'No Subject'); ?></td>
                                            <td class="message-preview"><?php echo htmlspecialchars($message['message']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $message['status'] === 'new' ? 'primary' : 
                                                        ($message['status'] === 'read' ? 'warning' : 'success'); 
                                                ?>">
                                                    <?php echo ucfirst($message['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                        <?php if ($message['status'] === 'new'): ?>
                                                            <button type="submit" name="action" value="mark_read" 
                                                                    class="btn btn-sm btn-warning" title="Mark as Read">
                                                                <i class="bi bi-envelope-open"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($message['status'] !== 'replied'): ?>
                                                            <button type="submit" name="action" value="mark_replied" 
                                                                    class="btn btn-sm btn-success" title="Mark as Replied">
                                                                <i class="bi bi-reply"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="submit" name="action" value="delete" 
                                                                class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this message?')"
                                                                title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Message Modal -->
                                        <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Message from <?php echo htmlspecialchars($message['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>Date:</strong> 
                                                            <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Email:</strong> 
                                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                                <?php echo htmlspecialchars($message['email']); ?>
                                                            </a>
                                                        </div>
                                                        <?php if ($message['phone']): ?>
                                                            <div class="mb-3">
                                                                <strong>Phone:</strong> 
                                                                <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                                                    <?php echo htmlspecialchars($message['phone']); ?>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($message['subject']): ?>
                                                            <div class="mb-3">
                                                                <strong>Subject:</strong> 
                                                                <?php echo htmlspecialchars($message['subject']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="mb-3">
                                                            <strong>Message:</strong>
                                                            <div class="mt-2 p-3 bg-light rounded">
                                                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" 
                                                           class="btn btn-primary">
                                                            <i class="bi bi-reply"></i> Reply via Email
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
