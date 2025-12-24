<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$content = '';
$error = '';

// Local File Inclusion vulnerability
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    
    // Vulnerable to LFI - no sanitization!
    // Can be used for log poisoning to achieve RCE
    if (file_exists($file)) {
        $content = file_get_contents($file);
    } else {
        $error = "File not found!";
    }
}

// Path traversal vulnerability
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    
    // Vulnerable to path traversal
    include($page . ".php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Viewer - VulnApp</title>
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
        h1 { color: #667eea; margin-bottom: 20px; }
        .form-group {
            margin-bottom: 20px;
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
        .content {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin-top: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 500px;
            overflow-y: auto;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .hint {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ffc107;
            margin-top: 15px;
        }
        .examples {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .examples h3 { color: #333; margin-bottom: 10px; font-size: 1.1em; }
        .examples ul { margin-left: 20px; line-height: 1.8; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 File Viewer</h1>
        
        <form method="GET">
            <div class="form-group">
                <label>File path to view:</label>
                <input type="text" name="file" placeholder="e.g., /etc/passwd" 
                       value="<?php echo isset($_GET['file']) ? htmlspecialchars($_GET['file']) : ''; ?>">
            </div>
            <button type="submit" class="btn">View File</button>
        </form>
        
        <div class="examples">
            <h3>📋 Try these files:</h3>
            <ul>
                <li><a href="?file=/etc/passwd">/etc/passwd</a> - System users</li>
                <li><a href="?file=/etc/hosts">/etc/hosts</a> - Host configuration</li>
                <li><a href="?file=/var/log/apache2/access.log">/var/log/apache2/access.log</a> - Apache access logs</li>
                <li><a href="?file=config.php">config.php</a> - Database configuration</li>
            </ul>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($content): ?>
            <div class="content"><?php echo htmlspecialchars($content); ?></div>
        <?php endif; ?>
        
        <a href="index.php" class="back-link">← Back to Home</a>
    </div>
</body>
</html>
