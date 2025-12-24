package main

import (
	"fmt"
	"html/template"
	"net/http"
	"os/exec"
)

var tmpl = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
        h1 { color: #fa709a; margin-bottom: 20px; }
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
            background: #fa709a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav a:hover { background: #fee140; color: #333; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            padding: 12px 30px;
            background: #fa709a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover { background: #fee140; color: #333; }
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
            background: #fff0f3;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #fa709a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 System Monitor</h1>
        <div class="nav">
            <a href="/">Home</a>
            <a href="/processes">Processes</a>
            <a href="/disk">Disk Usage</a>
            <a href="/network">Network</a>
            <a href="/services">Services</a>
            <a href="/diagnostics">Diagnostics</a>
        </div>
        {{.Content}}
    </div>
</body>
</html>
`

type PageData struct {
	Content template.HTML
}

func homeHandler(w http.ResponseWriter, r *http.Request) {
	content := `
        <h2>Welcome to System Monitor</h2>
        <div class="info-box">
            <p>Monitor system resources and performance metrics.</p>
        </div>
        <ul style="margin-top: 20px; line-height: 2;">
            <li>View running processes</li>
            <li>Check disk usage</li>
            <li>Monitor network connections</li>
            <li>Manage system services</li>
            <li>Run diagnostic tools</li>
        </ul>
    `

	t := template.Must(template.New("page").Parse(tmpl))
	t.Execute(w, PageData{Content: template.HTML(content)})
}

func processesHandler(w http.ResponseWriter, r *http.Request) {
	var content string

	if r.Method == "POST" {
		processName := r.FormValue("process")

		// RCE Vulnerability #1: Command injection via ps and grep
		cmdStr := fmt.Sprintf("ps aux | grep %s", processName)
		cmd := exec.Command("sh", "-c", cmdStr)
		output, _ := cmd.CombinedOutput()

		content = fmt.Sprintf(`
            <h2>Process Monitor</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Search Process:</label>
                    <input type="text" name="process" value="%s" placeholder="e.g., apache, mysql" required>
                </div>
                <button type="submit" class="btn">Search</button>
            </form>
            <div class="output">%s</div>
        `, processName, template.HTMLEscapeString(string(output)))
	} else {
		content = `
            <h2>Process Monitor</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Search Process:</label>
                    <input type="text" name="process" placeholder="e.g., apache, mysql" required>
                </div>
                <button type="submit" class="btn">Search</button>
            </form>
        `
	}

	t := template.Must(template.New("page").Parse(tmpl))
	t.Execute(w, PageData{Content: template.HTML(content)})
}

func diskHandler(w http.ResponseWriter, r *http.Request) {
	var content string

	if r.Method == "POST" {
		path := r.FormValue("path")

		// Command injection via du command
		cmdStr := fmt.Sprintf("du -sh %s 2>&1", path)
		cmd := exec.Command("sh", "-c", cmdStr)
		output, _ := cmd.CombinedOutput()

		content = fmt.Sprintf(`
            <h2>Disk Usage</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Directory Path:</label>
                    <input type="text" name="path" value="%s" placeholder="/var/log" required>
                </div>
                <button type="submit" class="btn">Check Usage</button>
            </form>
            <div class="output">%s</div>
        `, path, template.HTMLEscapeString(string(output)))
	} else {
		content = `
            <h2>Disk Usage</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Directory Path:</label>
                    <input type="text" name="path" placeholder="/var/log" required>
                </div>
                <button type="submit" class="btn">Check Usage</button>
            </form>
        `
	}

	t := template.Must(template.New("page").Parse(tmpl))
	t.Execute(w, PageData{Content: template.HTML(content)})
}

func networkHandler(w http.ResponseWriter, r *http.Request) {
	var content string

	if r.Method == "POST" {
		port := r.FormValue("port")

		// Command injection via netstat
		cmdStr := fmt.Sprintf("netstat -an | grep %s", port)
		cmd := exec.Command("sh", "-c", cmdStr)
		output, _ := cmd.CombinedOutput()

		content = fmt.Sprintf(`
            <h2>Network Connections</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Filter by Port:</label>
                    <input type="text" name="port" value="%s" placeholder="80" required>
                </div>
                <button type="submit" class="btn">Show Connections</button>
            </form>
            <div class="output">%s</div>
        `, port, template.HTMLEscapeString(string(output)))
	} else {
		content = `
            <h2>Network Connections</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Filter by Port:</label>
                    <input type="text" name="port" placeholder="80" required>
                </div>
                <button type="submit" class="btn">Show Connections</button>
            </form>
        `
	}

	t := template.Must(template.New("page").Parse(tmpl))
	t.Execute(w, PageData{Content: template.HTML(content)})
}

func servicesHandler(w http.ResponseWriter, r *http.Request) {
	var content string

	if r.Method == "POST" {
		serviceName := r.FormValue("service")
		action := r.FormValue("action")

		// RCE Vulnerability #2: Command injection via service management
		var cmdStr string
		if action == "status" {
			cmdStr = fmt.Sprintf("systemctl status %s 2>&1", serviceName)
		} else if action == "restart" {
			cmdStr = fmt.Sprintf("systemctl restart %s 2>&1", serviceName)
		} else {
			cmdStr = fmt.Sprintf("service %s %s 2>&1", serviceName, action)
		}

		cmd := exec.Command("sh", "-c", cmdStr)
		output, _ := cmd.CombinedOutput()

		content = fmt.Sprintf(`
            <h2>Service Manager</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Service Name:</label>
                    <input type="text" name="service" value="%s" placeholder="e.g., apache2, mysql" required>
                </div>
                <div class="form-group">
                    <label>Action:</label>
                    <select name="action">
                        <option value="status">Status</option>
                        <option value="start">Start</option>
                        <option value="stop">Stop</option>
                        <option value="restart">Restart</option>
                    </select>
                </div>
                <button type="submit" class="btn">Execute</button>
            </form>
            <div class="output">%s</div>
        `, serviceName, template.HTMLEscapeString(string(output)))
	} else {
		content = `
            <h2>Service Manager</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Service Name:</label>
                    <input type="text" name="service" placeholder="e.g., apache2, mysql" required>
                </div>
                <div class="form-group">
                    <label>Action:</label>
                    <select name="action">
                        <option value="status">Status</option>
                        <option value="start">Start</option>
                        <option value="stop">Stop</option>
                        <option value="restart">Restart</option>
                    </select>
                </div>
                <button type="submit" class="btn">Execute</button>
            </form>
        `
	}

	t := template.Must(template.New("page").Parse(tmpl))
	t.Execute(w, PageData{Content: template.HTML(content)})
}

func diagnosticsHandler(w http.ResponseWriter, r *http.Request) {
	var content string

	if r.Method == "POST" {
		diagType := r.FormValue("type")
		target := r.FormValue("target")

		// Command injection in diagnostics
		var cmdStr string
		switch diagType {
		case "ping":
			cmdStr = fmt.Sprintf("ping -c 4 %s", target)
		case "traceroute":
			cmdStr = fmt.Sprintf("traceroute -m 15 %s", target)
		case "nslookup":
			cmdStr = fmt.Sprintf("nslookup %s", target)
		default:
			cmdStr = "echo 'Invalid diagnostic type'"
		}

		cmd := exec.Command("sh", "-c", cmdStr)
		output, _ := cmd.CombinedOutput()

		content = fmt.Sprintf(`
            <h2>Network Diagnostics</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Diagnostic Type:</label>
                    <select name="type">
                        <option value="ping">Ping</option>
                        <option value="traceroute">Traceroute</option>
                        <option value="nslookup">DNS Lookup</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Target:</label>
                    <input type="text" name="target" value="%s" placeholder="e.g., google.com" required>
                </div>
                <button type="submit" class="btn">Run Diagnostic</button>
            </form>
            <div class="output">%s</div>
        `, target, template.HTMLEscapeString(string(output)))
	} else {
		content = `
            <h2>Network Diagnostics</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Diagnostic Type:</label>
                    <select name="type">
                        <option value="ping">Ping</option>
                        <option value="traceroute">Traceroute</option>
                        <option value="nslookup">DNS Lookup</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Target:</label>
                    <input type="text" name="target" placeholder="e.g., google.com" required>
                </div>
                <button type="submit" class="btn">Run Diagnostic</button>
            </form>
        `
	}

	t := template.Must(template.New("page").Parse(tmpl))
	t.Execute(w, PageData{Content: template.HTML(content)})
}

func main() {
	http.HandleFunc("/", homeHandler)
	http.HandleFunc("/processes", processesHandler)
	http.HandleFunc("/disk", diskHandler)
	http.HandleFunc("/network", networkHandler)
	http.HandleFunc("/services", servicesHandler)
	http.HandleFunc("/diagnostics", diagnosticsHandler)

	fmt.Println("Server starting on :80")
	http.ListenAndServe(":80", nil)
}
