# nlp-nexterp-scraper-local

Locally scrape your NextERP feeds.

This is an optimized, experimental version of the legacy local tool that was previously accessible via `curl -sSL rijash.com/i/install.sh | bash`. This updated project features an enhanced user interface and significantly faster scraping performance, reducing processing time from approximately 60 seconds to roughly 4 seconds.

## Requirements

Ensure you have the following prerequisites installed on your system:

* **Node.js**: Version 18 or higher
* **PHP**: Installed and accessible via the command line

## Installation and Setup

Follow these steps to clone the repository and start the local server:

```bash
# Clone the repository
git clone https://github.com/Deadly-BLOCK/nlp-nexterp-scraper-local

# Navigate into the project directory
cd nlp-nexterp-scraper-local

# Start the PHP built-in server
php -S localhost:<port>

```

After starting the server, open your web browser and navigate to `http://localhost:<port>` (or replace `localhost` with your system's IP address if accessing from another device on the network).

## Environment Variables

To enable advanced features and ensure proper execution, configure the following environment variables before running the server:

### 1. AI Integration (Optional)

To enable AI functionality within the user interface, provide your Gemini API key:

```bash
export GEMINI_API_KEY="<your-api-key>"

```

### 2. Node.js Binary Path (Required)

The application requires the specific path to your Node.js binary. You can locate this path by running `which node` (Linux/macOS) or `where node` (Windows) in your terminal.

```bash
export NODE_BIN="<your-node-path>"
```

> **Note:** Depending on your operating system, you can set these variables temporarily in your terminal session or add them to your environment configuration file (such as `.bashrc`, `.zshrc`, or system environment variables).
