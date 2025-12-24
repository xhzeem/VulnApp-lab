<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VulnApp - Penetration Testing Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 1200px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 40px; }
        .nav {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .nav a {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .post {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .post h3 { color: #333; margin-bottom: 10px; }
        .post-meta { color: #666; font-size: 0.9em; margin-bottom: 10px; }
        .user-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #667eea;
            color: white;
        }
        table tr:hover { background: #f8f9fa; }
    </style>
</head>
<body>
<!--
Internal SSH Key - For maintenance access to internal-server-2
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAyFqVN8kL3vH5JxPZ8mKj9wX2nQ7RtYuP3lKm4vN8sW9xQzEr
TnM2pL4kJ9vR6sX8wY3nT7mP5kL9vH2jQ8xR4tN6sP9wX5kL7vM3nQ9xY2tR8sP
-----END RSA PRIVATE KEY-----
-->
    <div class="container">
        <div class="header">
            <h1>🎯 VulnApp</h1>
            <p>Vulnerable Web Application - Penetration Testing Lab</p>
        </div>
        <div class="content">
            <?php
            require_once 'config.php';
            
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
            $message = '';
            $error = '';
            
            // Handle login
            if (isset($_POST['login'])) {
                $username = $_POST['username'];
                $password = md5($_POST['password']);
                
                // SQL Injection vulnerability - no prepared statements!
                $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
                $result = getDB()->query($query);
                
                if ($result && $result->rowCount() > 0) {
                    $user = $result->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $message = "Welcome back, " . htmlspecialchars($user['username']) . "!";
                } else {
                    $error = "Invalid credentials!";
                }
            }
            
            // Handle logout
            if (isset($_GET['logout'])) {
                session_destroy();
                header("Location: index.php");
                exit();
            }
            
            // Handle registration
            if (isset($_POST['register'])) {
                $username = $_POST['reg_username'];
                $password = md5($_POST['reg_password']);
                $email = $_POST['reg_email'];
                
                try {
                    $stmt = getDB()->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $password, $email]);
                    $message = "Registration successful! You can now login.";
                } catch(PDOException $e) {
                    $error = "Registration failed: " . $e->getMessage();
                }
            }
            
            // Handle search - SQL Injection vulnerability
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
                // Vulnerable to SQL injection
                $searchQuery = "SELECT * FROM posts WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
            }
            
            // Handle comment submission - Stored XSS vulnerability
            if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
                $post_id = $_POST['post_id'];
                $comment = $_POST['comment']; // No sanitization!
                
                $stmt = getDB()->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
                $stmt->execute([$post_id, $_SESSION['user_id'], $comment]);
                $message = "Comment added!";
            }
            
            if ($message) echo "<div class='alert alert-success'>$message</div>";
            if ($error) echo "<div class='alert alert-error'>$error</div>";
            ?>
            
            <div class="nav">
                <a href="index.php?page=home">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=profile">Profile</a>
                    <a href="index.php?page=posts">Posts</a>
                    <a href="upload.php">Upload</a>
                    <a href="view.php">File Viewer</a>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="admin.php">Admin Panel</a>
                    <?php endif; ?>
                    <a href="index.php?logout=1">Logout</a>
                <?php else: ?>
                    <a href="index.php?page=login">Login</a>
                    <a href="index.php?page=register">Register</a>
                <?php endif; ?>
            </div>
            
            <?php
            // Page routing
            switch($page) {
                case 'home':
                    ?>
                    <h2>Welcome to VulnApp</h2>
                    <p>This is an intentionally vulnerable web application designed for penetration testing practice.</p>
                    
                    <div class="alert alert-info">
                        <strong>🎯 Challenge:</strong> Find and exploit all vulnerabilities in this application!
                    </div>
                    
                    <h3>Features:</h3>
                    <ul style="line-height: 2;">
                        <li>User Authentication System</li>
                        <li>Post Management</li>
                        <li>File Upload Functionality</li>
                        <li>Search Feature</li>
                        <li>Admin Panel</li>
                        <li>File Viewer</li>
                    </ul>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-info">
                            <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?> 
                            (Role: <?php echo htmlspecialchars($_SESSION['role']); ?>)
                        </div>
                    <?php endif; ?>
                    <?php
                    break;
                    
                case 'login':
                    if (isset($_SESSION['user_id'])) {
                        echo "<p>You are already logged in!</p>";
                    } else {
                        ?>
                        <h2>Login</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label>Username:</label>
                                <input type="text" name="username" required>
                            </div>
                            <div class="form-group">
                                <label>Password:</label>
                                <input type="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn">Login</button>
                        </form>
                        <?php
                    }
                    break;
                    
                case 'register':
                    ?>
                    <h2>Register</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="reg_username" required>
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="reg_password" required>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="reg_email" required>
                        </div>
                        <button type="submit" name="register" class="btn">Register</button>
                    </form>
                    <?php
                    break;
                    
                case 'profile':
                    if (!isset($_SESSION['user_id'])) {
                        echo "<p>Please login first!</p>";
                    } else {
                        // IDOR vulnerability - can view any user's profile
                        $user_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
                        
                        $stmt = getDB()->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user) {
                            ?>
                            <h2>User Profile</h2>
                            <div class="user-info">
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                                <p><strong>Member since:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
                            </div>
                            <?php
                        }
                    }
                    break;
                    
                case 'posts':
                    if (!isset($_SESSION['user_id'])) {
                        echo "<p>Please login first!</p>";
                    } else {
                        ?>
                        <h2>Posts</h2>
                        
                        <!-- Search form - SQL Injection -->
                        <form method="GET" style="margin-bottom: 20px;">
                            <input type="hidden" name="page" value="posts">
                            <div class="form-group">
                                <label>Search Posts:</label>
                                <input type="text" name="search" placeholder="Search...">
                            </div>
                            <button type="submit" class="btn">Search</button>
                        </form>
                        
                        <?php
                        if (isset($_GET['search'])) {
                            $search = $_GET['search'];
                            $query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
                        } else {
                            $query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE is_private = 0";
                        }
                        
                        $posts = getDB()->query($query);
                        
                        foreach ($posts as $post) {
                            ?>
                            <div class="post">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <div class="post-meta">
                                    By <?php echo htmlspecialchars($post['username']); ?> on <?php echo htmlspecialchars($post['created_at']); ?>
                                </div>
                                <p><?php echo htmlspecialchars($post['content']); ?></p>
                                
                                <!-- Comments section -->
                                <h4 style="margin-top: 15px;">Comments:</h4>
                                <?php
                                $stmt = getDB()->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ?");
                                $stmt->execute([$post['id']]);
                                $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($comments as $comment) {
                                    // XSS vulnerability - no output encoding!
                                    echo "<div style='background: white; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                    echo "<strong>" . htmlspecialchars($comment['username']) . ":</strong> ";
                                    echo $comment['comment']; // XSS here!
                                    echo "</div>";
                                }
                                ?>
                                
                                <!-- Add comment form -->
                                <form method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <div class="form-group">
                                        <input type="text" name="comment" placeholder="Add a comment..." required>
                                    </div>
                                    <button type="submit" name="add_comment" class="btn">Comment</button>
                                </form>
                            </div>
                            <?php
                        }
                        ?>
                        <?php
                    }
                    break;
            }
            ?>
        </div>
    </div>
</body>
</html>
