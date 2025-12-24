<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access denied! Admin only.");
}

$output = '';
$error = '';

// Command injection vulnerability
if (isset($_POST['ping'])) {
    $host = $_POST['host'];
    
    // Vulnerable to command injection - no sanitization!
    $command = "ping -c 4 " . $host;
    $output = shell_exec($command);
}

// SSRF vulnerability
if (isset($_POST['fetch_url'])) {
    $url = $_POST['url'];
    
    // SSRF - can access internal services
    $content = file_get_contents($url);
    $output = htmlspecialchars($content);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - VulnApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
        }
        h1 { color: #667eea; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .panel {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .panel h2 { color: #333; margin-bottom: 15px; font-size: 1.3em; }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin-top: 15px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        .hint {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ffc107;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Admin Panel</h1>
        <p class="subtitle">System Administration Tools</p>
        
        <div class="panel">
            <h2>🌐 Network Diagnostics</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Host to ping:</label>
                    <input type="text" name="host" placeholder="e.g., google.com" required>
                </div>
                <button type="submit" name="ping" class="btn">Ping Host</button>
            </form>
        </div>
        
        <div class="panel">
            <h2>🔗 URL Fetcher</h2>
            <form method="POST">
                <div class="form-group">
                    <label>URL to fetch:</label>
                    <input type="text" name="url" placeholder="e.g., http://example.com" required>
                </div>
                <button type="submit" name="fetch_url" class="btn">Fetch URL</button>
            </form>
        </div>
        
        <?php if ($output): ?>
            <div class="panel">
                <h2>📤 Output:</h2>
                <div class="output"><?php echo $output; ?></div>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
</body>
</html>
