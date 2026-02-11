<?php
require_once 'includes/functions.php';

requireLogin();

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$delete_id, $_SESSION['user_id']])) {
        setFlashMessage('success', 'Item deleted successfully.');
    }
    else {
        setFlashMessage('danger', 'Failed to delete item.');
    }
    redirect('dashboard.php');
}

// Fetch User's Items
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<h2 class="mb-4">My Dashboard</h2>
<?= getFlashMessage('success')?>
<?= getFlashMessage('danger')?>

<div class="mb-3">
    <a href="post-item.php" class="btn btn-success">Post New Item</a>
</div>

<?php if (empty($items)): ?>
<div class="alert alert-info">You haven't posted any items yet.</div>
<?php
else: ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date</th>
                <th>Claimed?</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if ($item['image_path']): ?>
                    <img src="assets/uploads/<?= $item['image_path']?>" width="60" height="60"
                        style="object-fit: cover;">
                    <?php
        else: ?>
                    <span class="text-muted">No Image</span>
                    <?php
        endif; ?>
                </td>
                <td>
                    <?= sanitize($item['title'])?>
                </td>
                <td><small class="text-muted"><i class="bi bi-geo-alt"></i>
                        <?= sanitize($item['location'])?>
                    </small></td>
                <td>
                    <span class="badge bg-<?= $item['status'] === 'lost' ? 'danger' : 'success'?>">
                        <?= ucfirst($item['status'])?>
                    </span>
                </td>
                <td>
                    <?= date('M d, Y', strtotime($item['created_at']))?>
                </td>
                <td>
                    <?php if ($item['is_claimed']): ?>
                    <span class="badge bg-warning text-dark">Claimed</span>
                    <?php
        else: ?>
                    <span class="badge bg-secondary">Active</span>
                    <?php
        endif; ?>
                </td>
                <td>
                    <a href="item-details.php?id=<?= $item['id']?>" class="btn btn-sm btn-info text-white">View</a>
                    <a href="edit-item.php?id=<?= $item['id']?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="delete_id" value="<?= $item['id']?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php
    endforeach; ?>
        </tbody>
    </table>
</div>
<?php
endif; ?>

<?php require_once 'includes/footer.php'; ?>