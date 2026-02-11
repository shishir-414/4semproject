<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    }
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login Success
            session_regenerate_id(true); // Prevent Session Hijacking
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            setFlashMessage('success', 'Welcome back, ' . $user['username'] . '!');
            redirect('index.php');
        }
        else {
            $error = "Invalid email or password.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Login</h4>
            </div>
            <div class="card-body">
                <?= getFlashMessage('success')?>
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= $error?>
                </div>
                <?php
endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?= isset($email) ? $email : ''?>">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="register.php">Don't have an account? Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>