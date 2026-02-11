<?php
require_once 'includes/functions.php';

requireLogin();

if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$item_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch Item
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    setFlashMessage('danger', 'Item not found or access denied.');
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $status = $_POST['status'];
    $category = sanitize($_POST['category']);
    $location = sanitize($_POST['location']);
    $image_path = $item['image_path']; // Keep existing image by default

    // Validation
    if (empty($title) || empty($status) || empty($location)) {
        $errors[] = "Title, Status, and Location are required.";
    }

    // Image Upload (Optional Replacement)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_name = uniqid() . '.' . $file_ext;
            $upload_dir = 'assets/uploads/';
            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $image_path = $new_name; // Update image path
            // Optional: Delete old image file if it exists
            }
            else {
                $errors[] = "Failed to upload new image.";
            }
        }
        else {
            $errors[] = "Invalid image format. Only JPG, PNG, GIF are allowed.";
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE items SET title = ?, description = ?, status = ?, category = ?, location = ?, image_path = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$title, $description, $status, $category, $location, $image_path, $item_id, $user_id])) {
            setFlashMessage('success', 'Item updated successfully!');
            redirect('dashboard.php');
        }
        else {
            $errors[] = "Failed to update item.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4>Edit Item</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li>
                            <?= $error?>
                        </li>
                        <?php
    endforeach; ?>
                    </ul>
                </div>
                <?php
endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required
                            value="<?= sanitize($item['title'])?>">
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="lost" <?=$item['status']==='lost' ? 'selected' : ''?>>Lost</option>
                            <option value="found" <?=$item['status']==='found' ? 'selected' : ''?>>Found</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control" list="categories"
                            value="<?= sanitize($item['category'])?>">
                        <datalist id="categories">
                            <option value="Electronics">
                            <option value="Documents">
                            <option value="Pets">
                            <option value="Wallet/Bag">
                            <option value="Clothing">
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" required
                            value="<?= sanitize($item['location'])?>">
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"
                            rows="4"><?= sanitize($item['description'])?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Current Image</label><br>
                        <?php if ($item['image_path']): ?>
                        <img src="assets/uploads/<?= $item['image_path']?>" width="100" class="mb-2">
                        <?php
else: ?>
                        <span class="text-muted">No Image</span>
                        <?php
endif; ?>
                        <div class="mt-2">
                            <label>Change Image (Optional)</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">Update Item</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>