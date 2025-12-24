#!/usr/bin/perl
use strict;
use warnings;
use CGI;
use MIME::Base64;

my $q = CGI->new;

# Configuration Management System
print $q->header('text/html');

my $page = $q->param('page') || 'home';

print <<'HTML';
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px;
        }
        h1 { color: #f5576c; margin-bottom: 20px; }
        h2 { color: #333; margin-bottom: 15px; }
        .nav {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
        }
        .nav a {
            padding: 10px 20px;
            background: #f5576c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav a:hover { background: #f093fb; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            padding: 12px 30px;
            background: #f5576c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover { background: #f093fb; }
        .output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin-top: 20px;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .info-box {
            background: #ffe0e6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #f5576c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Configuration Manager</h1>
        <div class="nav">
            <a href="?page=home">Home</a>
            <a href="?page=configs">Configurations</a>
            <a href="?page=calculator">Calculator</a>
            <a href="?page=validator">Validator</a>
            <a href="?page=backup">Backup</a>
            <a href="?page=logs">Logs</a>
        </div>
HTML

if ($page eq 'home') {
    print <<'HTML';
        <h2>Welcome to Configuration Manager</h2>
        <div class="info-box">
            <p>Manage server configurations, validate settings, and perform calculations.</p>
        </div>
        <ul style="margin-top: 20px; line-height: 2;">
            <li>View and edit configuration files</li>
            <li>Validate configuration syntax</li>
            <li>Perform mathematical calculations</li>
            <li>Backup and restore configurations</li>
            <li>View system logs</li>
        </ul>
HTML

} elsif ($page eq 'configs') {
    print <<'HTML';
        <h2>Configuration Files</h2>
        <div class="info-box">
            <p>View and manage system configuration files.</p>
        </div>
        <table>
            <tr>
                <th>File</th>
                <th>Description</th>
                <th>Last Modified</th>
            </tr>
            <tr>
                <td>database.conf</td>
                <td>Database connection settings</td>
                <td>2024-01-15</td>
            </tr>
            <tr>
                <td>network.conf</td>
                <td>Network configuration</td>
                <td>2024-01-10</td>
            </tr>
            <tr>
                <td>security.conf</td>
                <td>Security policies</td>
                <td>2024-01-05</td>
            </tr>
        </table>
HTML

} elsif ($page eq 'calculator') {
    my $expression = $q->param('expr') || '';
    my $result = '';
    
    print <<HTML;
        <h2>Configuration Calculator</h2>
        <div class="info-box">
            <p>Calculate values for configuration parameters.</p>
        </div>
        <form method="GET">
            <input type="hidden" name="page" value="calculator">
            <div class="form-group">
                <label>Expression:</label>
                <input type="text" name="expr" value="$expression" placeholder="e.g., 2+2, 10*5">
            </div>
            <button type="submit" class="btn">Calculate</button>
        </form>
HTML
    
    if ($expression) {
        # RCE Vulnerability #1: eval() allows arbitrary Perl code execution
        eval {
            $result = eval($expression);
        };
        
        if ($@) {
            $result = "Error: $@";
        }
        
        print "<div class='output'>Result: " . CGI::escapeHTML($result) . "</div>";
    }

} elsif ($page eq 'validator') {
    my $config = $q->param('config') || '';
    my $validation = '';
    
    print <<HTML;
        <h2>Configuration Validator</h2>
        <div class="info-box">
            <p>Validate configuration syntax and values.</p>
        </div>
        <form method="GET">
            <input type="hidden" name="page" value="validator">
            <div class="form-group">
                <label>Configuration Code:</label>
                <textarea name="config" rows="5" placeholder="Enter Perl configuration code...">$config</textarea>
            </div>
            <button type="submit" class="btn">Validate</button>
        </form>
HTML
    
    if ($config) {
        # RCE Vulnerability #2: eval() on user input
        my $safe_result;
        eval {
            $safe_result = eval($config);
        };
        
        if ($@) {
            $validation = "Validation Error: $@";
        } else {
            $validation = "Configuration is valid. Result: " . (defined $safe_result ? $safe_result : 'undef');
        }
        
        print "<div class='output'>" . CGI::escapeHTML($validation) . "</div>";
    }

} elsif ($page eq 'backup') {
    my $backup_cmd = $q->param('backup_cmd') || '';
    my $output = '';
    
    print <<HTML;
        <h2>Backup Manager</h2>
        <div class="info-box">
            <p>Create and restore configuration backups.</p>
        </div>
        <form method="GET">
            <input type="hidden" name="page" value="backup">
            <div class="form-group">
                <label>Backup Command:</label>
                <select name="backup_cmd">
                    <option value="">Select action...</option>
                    <option value="list">List Backups</option>
                    <option value="create">Create Backup</option>
                    <option value="restore">Restore Latest</option>
                </select>
            </div>
            <button type="submit" class="btn">Execute</button>
        </form>
HTML
    
    if ($backup_cmd) {
        # Command execution based on selection
        if ($backup_cmd eq 'list') {
            $output = `ls -lh /tmp/backups 2>&1`;
        } elsif ($backup_cmd eq 'create') {
            $output = `tar -czf /tmp/backups/config_backup.tar.gz /etc/*.conf 2>&1`;
        } elsif ($backup_cmd eq 'restore') {
            $output = `tar -xzf /tmp/backups/config_backup.tar.gz 2>&1`;
        }
        
        print "<div class='output'>" . CGI::escapeHTML($output) . "</div>";
    }

} elsif ($page eq 'logs') {
    my $log_file = $q->param('logfile') || '/var/log/syslog';
    my $lines = $q->param('lines') || '50';
    my $filter = $q->param('filter') || '';
    
    print <<HTML;
        <h2>Log Viewer</h2>
        <div class="info-box">
            <p>View and search system logs.</p>
        </div>
        <form method="GET">
            <input type="hidden" name="page" value="logs">
            <div class="form-group">
                <label>Log File:</label>
                <input type="text" name="logfile" value="$log_file">
            </div>
            <div class="form-group">
                <label>Number of Lines:</label>
                <input type="text" name="lines" value="$lines">
            </div>
            <div class="form-group">
                <label>Filter Pattern:</label>
                <input type="text" name="filter" value="$filter" placeholder="Optional grep pattern">
            </div>
            <button type="submit" class="btn">View Logs</button>
        </form>
HTML
    
    if ($log_file) {
        my $cmd;
        if ($filter) {
            # Command injection via filter
            $cmd = "tail -n $lines $log_file | grep '$filter' 2>&1";
        } else {
            $cmd = "tail -n $lines $log_file 2>&1";
        }
        
        my $output = `$cmd`;
        print "<div class='output'>" . CGI::escapeHTML($output) . "</div>";
    }
}

print <<'HTML';
    </div>
</body>
</html>
HTML
