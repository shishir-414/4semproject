<?php
require_once '../includes/functions.php';

requireAdmin();

// Handle Delete Item
if (isset($_POST['delete_item_id'])) {
    $delete_id = $_POST['delete_item_id'];
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        setFlashMessage('success', 'Item deleted successfully.');
    }
    else {
        setFlashMessage('danger', 'Failed to delete item.');
    }
    redirect('index.php');
}

// Handle Claim Action
if (isset($_POST['claim_id']) && isset($_POST['action'])) {
    $claim_id = $_POST['claim_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';

    $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $claim_id])) {
        if ($action === 'approve') {
            // Mark item as claimed
            $stmt = $pdo->prepare("SELECT item_id FROM claims WHERE id = ?");
            $stmt->execute([$claim_id]);
            $item_id = $stmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE items SET is_claimed = 1 WHERE id = ?");
            $stmt->execute([$item_id]);
        }
        setFlashMessage('success', 'Claim ' . $status . '.');
    }
    else {
        setFlashMessage('danger', 'Failed to update claim.');
    }
    redirect('index.php');
}

// Fetch All Items
$stmt = $pdo->query("SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.id ORDER BY created_at DESC");
$items = $stmt->fetchAll();

// Fetch Pending Claims
$stmt = $pdo->query("SELECT claims.*, items.title AS item_title, users.username AS claimer_name FROM claims JOIN items ON claims.item_id = items.id JOIN users ON claims.user_id = users.id WHERE claims.status = 'pending'");
$claims = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Lost & Found Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Back to Site</a>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        <?= getFlashMessage('success')?>
        <?= getFlashMessage('danger')?>

        <div class="row mt-4">
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4>Pending Claims</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($claims)): ?>
                        <p class="text-muted">No pending claims.</p>
                        <?php
else: ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Claimer</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($claims as $claim): ?>
                                <tr>
                                    <td>
                                        <?= sanitize($claim['item_title'])?>
                                    </td>
                                    <td>
                                        <?= sanitize($claim['claimer_name'])?>
                                    </td>
                                    <td>
                                        <?= sanitize($claim['message'])?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($claim['created_at']))?>
                                    </td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="claim_id" value="<?= $claim['id']?>">
                                            <button type="submit" name="action" value="approve"
                                                class="btn btn-sm btn-success">Approve</button>
                                            <button type="submit" name="action" value="reject"
                                                class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                        <?php
endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4>All Items</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Posted By</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <a href="../item-details.php?id=<?= $item['id']?>" target="_blank">
                                            <?= sanitize($item['title'])?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= sanitize($item['username'])?>
                                    </td>
                                    <td>
                                        <?= sanitize($item['location'])?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item['status'] === 'lost' ? 'danger' : 'success'?>">
                                            <?= ucfirst($item['status'])?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($item['created_at']))?>
                                    </td>
                                    <td>
                                        <form method="POST" action=""
                                            onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            <input type="hidden" name="delete_item_id" value="<?= $item['id']?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>