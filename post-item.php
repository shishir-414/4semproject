<?php
require_once 'includes/functions.php';

requireLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $status = $_POST['status'];
    $category = sanitize($_POST['category']);
    $location = sanitize($_POST['location']);
    $user_id = $_SESSION['user_id'];
    $image_path = null;

    // Validation
    if (empty($title) || empty($status) || empty($location)) {
        $errors[] = "Title, Status, and Location are required.";
    }

    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        // Debug: Check if upload dir is writable
        if (!is_writable('assets/uploads/')) {
            $errors[] = "Upload directory is not writable. Please contact admin.";
        }
        elseif (in_array($file_ext, $allowed)) {
            $new_name = uniqid() . '.' . $file_ext;
            $upload_dir = 'assets/uploads/';
            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $image_path = $new_name;
            }
            else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
        else {
            $errors[] = "Invalid image format. Only JPG, PNG, GIF are allowed.";
        }
    }
    elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errors[] = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errors[] = "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errors[] = "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errors[] = "A PHP extension stopped the file upload.";
                break;
            default:
                $errors[] = "Unknown upload error.";
                break;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, status, category, location, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $title, $description, $status, $category, $location, $image_path])) {
            setFlashMessage('success', 'Item posted successfully!');
            redirect('dashboard.php');
        }
        else {
            $errors[] = "Failed to post item.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Post Lost/Found Item</h4>
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
                            value="<?= isset($title) ? $title : ''?>">
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control" list="categories"
                            placeholder="e.g. Electronics, Pets">
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
                            placeholder="Where was it lost/found?" value="<?= isset($location) ? $location : ''?>">
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"
                            rows="4"><?= isset($description) ? $description : ''?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Image (Optional)</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Post Item</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>