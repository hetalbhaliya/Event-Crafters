<?php

session_start();

include("../config/db.php");

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email == "" || $password == "") {

        $error = "Please enter email and password.";

    } else {

        $email = mysqli_real_escape_string($conn, $email);
        $password = mysqli_real_escape_string($conn, $password);

        $query = "SELECT * FROM admins
                  WHERE email='$email'
                  AND password='$password'
                  LIMIT 1";

        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) == 1) {

            $admin = mysqli_fetch_assoc($result);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_email'] = $admin['email'];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Invalid email or password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Event Crafters</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

 



    <style>

        body {
            min-height: 100vh;
            margin: 0;

            background: #0b1020;
            color: #ffffff;

            font-family: Arial, Helvetica, sans-serif;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-box {

            width: 90%;
            max-width: 450px;

            padding: 40px;

            background: rgba(18, 25, 45, 0.95);

            border: 1px solid rgba(53, 214, 255, 0.25);

            border-radius: 20px;

            box-shadow: 0 0 40px rgba(53, 214, 255, 0.08);
        }

        .admin-title {

            text-align: center;

            color: #35d6ff;

            letter-spacing: 3px;

            font-size: 14px;

            font-weight: bold;

            margin-bottom: 10px;
        }

        .admin-login-box h1 {

            text-align: center;

            font-size: 34px;

            margin-bottom: 8px;
        }

        .admin-subtitle {

            text-align: center;

            color: #8f9bb8;

            margin-bottom: 30px;
        }

        .form-group label {

            color: #dce5ff;

            font-weight: 600;
        }

        .form-control {

            height: 48px;

            background: #0b1020;

            border: 1px solid #263451;

            color: #ffffff;

            border-radius: 10px;
        }

        .form-control:focus {

            background: #0b1020;

            color: #ffffff;

            border-color: #35d6ff;

            box-shadow: 0 0 10px rgba(53, 214, 255, 0.15);
        }

        .login-btn {

            width: 100%;

            height: 48px;

            border: none;

            border-radius: 10px;

            background: #35d6ff;

            color: #07101d;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;
        }

        .login-btn:hover {

            transform: translateY(-2px);

            box-shadow: 0 0 20px rgba(53, 214, 255, 0.3);
        }

        .error-message {

            background: rgba(255, 70, 70, 0.10);

            border: 1px solid rgba(255, 70, 70, 0.30);

            color: #ff8c8c;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;
        }

        .back-home {

            display: block;

            text-align: center;

            margin-top: 20px;

            color: #8f9bb8;

            text-decoration: none;
        }

        .back-home:hover {

            color: #35d6ff;

            text-decoration: none;
        }

    </style>

</head>

<body>

    <div class="admin-login-box">

        <div class="admin-title">
            ADMIN PANEL
        </div>

        <h1>Event Crafters</h1>

        <p class="admin-subtitle">
            Sign in to manage your events
        </p>

        <?php if ($error != "") { ?>

            <div class="error-message">
                <?php echo $error; ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="form-group">

                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control"
                       placeholder="Enter admin email"
                       required>

            </div>

            <div class="form-group">

                <label>Password</label>

                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Enter password"
                       required>

            </div>

            <button type="submit"
                    name="login"
                    class="login-btn">

                Login to Admin Panel

            </button>

        </form>

        <a href="../index.php"
           class="back-home">

            ← Back to Website

        </a>

    </div>

</body>

</html>