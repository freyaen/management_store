<?php
include 'config/database.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek user di database
    $stmt = $conn->prepare("
        SELECT u.*, r.name as role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Simpan data user di cookie (berlaku 1 hari)
            setcookie("user_id", $user['id'], time() + 86400, "/");
            setcookie("username", $user['username'], time() + 86400, "/");
            setcookie("name", $user['name'], time() + 86400, "/");
            setcookie("role_id", $user['role_id'], time() + 86400, "/");
            setcookie("role_name", $user['role_name'], time() + 86400, "/");

            // Redirect ke halaman utama
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            max-width: 150px;
        }
    </style>
</head>
<body class="bg-primary">
    <div class="container">
        <div class="row justify-content-center">
                <div class="login-container">
                    <div class="logo">
                        <img src="assets/images/logo.png" alt="Company Logo">
                        <h2 class="mt-3">Management Store</h2>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
            </div>
        </div>
    </div>
</body>
</html>