<?php
require_once 'includes/functions.php';

// Search and Filter logic can be added here
$where = "is_claimed = 0";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $where .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where .= " AND status = ?";
    $params[] = $_GET['status'];
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$items = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="jumbotron text-center bg-light p-5 rounded mb-4">
    <h1 class="display-4">Lost & Found</h1>
    <p class="lead">Report lost items or help others find theirs.</p>
    <a class="btn btn-primary btn-lg" href="post-item.php" role="button">Post an Item</a>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search items..."
                    value="<?= isset($_GET['search']) ? sanitize($_GET['search']) : ''?>">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="lost" <?=isset($_GET['status']) && $_GET['status']=='lost' ? 'selected' : ''?>>Lost
                    </option>
                    <option value="found" <?=isset($_GET['status']) && $_GET['status']=='found' ? 'selected' : ''?>
                        >Found</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Items Grid -->
<div class="row">
    <?php if (empty($items)): ?>
    <div class="col-12 text-center">
        <h3>No items found matching your criteria.</h3>
    </div>
    <?php
else: ?>
    <?php foreach ($items as $item): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <?php if ($item['image_path']): ?>
            <img src="assets/uploads/<?= $item['image_path']?>" class="card-img-top"
                alt="<?= sanitize($item['title'])?>">
            <?php
        else: ?>
            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white">
                <span>No Image</span>
            </div>
            <?php
        endif; ?>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-<?= $item['status'] === 'lost' ? 'danger' : 'success'?>">
                        <?= ucfirst($item['status'])?>
                    </span>
                    <small class="text-muted">
                        <?= date('M d', strtotime($item['created_at']))?>
                    </small>
                </div>
                <h5 class="card-title">
                    <?= sanitize($item['title'])?>
                </h5>
                <p class="card-text mb-1"><small class="text-muted"><i class="bi bi-geo-alt"></i>
                        <?= sanitize($item['location'])?>
                    </small></p>
                <p class="card-text text-truncate">
                    <?= sanitize($item['description'])?>
                </p>
                <div class="d-flex gap-2">
                    <a href="item-details.php?id=<?= $item['id']?>"
                        class="btn btn-outline-primary btn-sm flex-grow-1">View Details</a>
                    <?php if (!isLoggedIn() || $_SESSION['user_id'] !== $item['user_id']): ?>
                    <a href="item-details.php?id=<?= $item['id']?>"
                        class="btn btn-primary btn-sm flex-grow-1">Claim</a>
                    <?php
        endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    endforeach; ?>
    <?php
endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>