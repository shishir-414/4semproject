<?php
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$access_denied = false;
$claim_success = false;
$claim_error = '';

$item_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.id WHERE items.id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    redirect('index.php');
}

// Handle Claim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_message'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }

    $message = sanitize($_POST['claim_message']);

    // Check if user already claimed
    $stmt = $pdo->prepare("SELECT id FROM claims WHERE item_id = ? AND user_id = ?");
    $stmt->execute([$item_id, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        $claim_error = "You have already submitted a claim for this item.";
    }
    else {
        $stmt = $pdo->prepare("INSERT INTO claims (item_id, user_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$item_id, $_SESSION['user_id'], $message])) {
            $claim_success = true;
        }
        else {
            $claim_error = "Failed to submit claim.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <?php if ($item['image_path']): ?>
        <img src="assets/uploads/<?= $item['image_path']?>" class="img-fluid rounded shadow"
            alt="<?= sanitize($item['title'])?>">
        <?php
else: ?>
        <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded shadow"
            style="height: 400px;">
            <h3>No Image Available</h3>
        </div>
        <?php
endif; ?>
    </div>
    <div class="col-md-6">
        <h2>
            <?= sanitize($item['title'])?>
        </h2>
        <p class="text-muted">Posted by
            <?= sanitize($item['username'])?> on
            <?= date('F j, Y', strtotime($item['created_at']))?>
        </p>

        <div class="mb-3">
            <span class="badge bg-<?= $item['status'] === 'lost' ? 'danger' : 'success'?> fs-5">
                <?= ucfirst($item['status'])?>
            </span>
            <span class="badge bg-info text-dark fs-5 ms-2">
                <?= sanitize($item['category'])?>
            </span>
            <span class="badge bg-secondary fs-5 ms-2"><i class="bi bi-geo-alt"></i>
                <?= sanitize($item['location'])?>
            </span>
            <?php if ($item['is_claimed']): ?>
            <span class="badge bg-warning text-dark fs-5 ms-2">Claimed</span>
            <?php
endif; ?>
        </div>

        <p class="lead">
            <?= nl2br(sanitize($item['description']))?>
        </p>

        <hr>

        <?php if ($claim_success): ?>
        <div class="alert alert-success">Your claim has been submitted successfully! The owner will review it.</div>
        <?php
elseif ($item['is_claimed']): ?>
        <div class="alert alert-warning">This item has already been marked as claimed.</div>
        <?php
elseif (isLoggedIn() && $_SESSION['user_id'] !== $item['user_id']): ?>
        <?php if ($claim_error): ?>
        <div class="alert alert-danger">
            <?= $claim_error?>
        </div>
        <?php
    endif; ?>
        <div class="card">
            <div class="card-header">
                <strong>Claim this item</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Message to Owner</label>
                        <textarea name="claim_message" class="form-control" rows="3" required
                            placeholder="Describe the item or provide proof of ownership..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Claim</button>
                </form>
            </div>
        </div>
        <?php
elseif (!isLoggedIn()): ?>
        <div class="alert alert-info">
            Please <a href="login.php">login</a> to claim this item.
        </div>
        <?php
endif; ?>

        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>