<!DOCTYPE html>
<html>
<head>
    <title>HubSpot Integration - AtlasPress</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .step { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #0073aa; }
        .step h3 { margin-top: 0; color: #0073aa; }
        code { background: #fff; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; font-size: 14px; }
        .webhook-url { background: #fff; padding: 15px; border: 2px solid #0073aa; border-radius: 6px; font-family: monospace; font-size: 16px; margin: 15px 0; word-break: break-all; }
        .copy-btn { background: #0073aa; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .copy-btn:hover { background: #005a87; }
        .success { color: #46b450; font-weight: bold; }
        img { max-width: 100%; border: 1px solid #ddd; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔗 HubSpot Forms Integration</h1>
    <p>Automatically capture HubSpot form submissions in AtlasPress.</p>

    <div class="step">
        <h3>Step 1: Copy Your Webhook URL</h3>
        <p>This is your unique webhook endpoint:</p>
        <div class="webhook-url" id="webhookUrl">
            <?php echo home_url('/wp-json/atlaspress/v1/hubspot/webhook'); ?>
        </div>
        <button class="copy-btn" onclick="copyWebhook()">📋 Copy URL</button>
        <span id="copied" style="display:none; color: #46b450; margin-left: 10px;">✓ Copied!</span>
    </div>

    <div class="step">
        <h3>Step 2: Configure HubSpot Form</h3>
        <ol>
            <li>Go to your <a href="https://app.hubspot.com/forms" target="_blank">HubSpot Forms</a></li>
            <li>Select the form you want to integrate</li>
            <li>Click <strong>Options</strong> → <strong>Webhooks</strong></li>
            <li>Click <strong>Add webhook</strong></li>
            <li>Paste your webhook URL from Step 1</li>
            <li>Click <strong>Save</strong></li>
        </ol>
    </div>

    <div class="step">
        <h3>Step 3: Test the Integration</h3>
        <ol>
            <li>Submit a test form on your HubSpot form</li>
            <li>Go to <strong>AtlasPress → Content Types</strong></li>
            <li>Look for <strong>"HubSpot Forms"</strong> content type</li>
            <li>Click <strong>View Entries</strong> to see your submission</li>
        </ol>
    </div>

    <div class="step">
        <h3>✅ What Happens Next?</h3>
        <ul>
            <li>Every HubSpot form submission is automatically saved</li>
            <li>All form fields are captured in the entry data</li>
            <li>View submissions in <strong>AtlasPress → Content Types → HubSpot Forms</strong></li>
            <li>Export data as CSV, JSON, or XML</li>
            <li>Use the REST API to access submissions programmatically</li>
        </ul>
    </div>

    <div class="step">
        <h3>🔧 Advanced: Custom Content Type</h3>
        <p>By default, all HubSpot submissions go to "HubSpot Forms" content type. To use a custom content type:</p>
        <ol>
            <li>Create your content type in AtlasPress</li>
            <li>Note the content type ID from the URL</li>
            <li>Add this to your <code>wp-config.php</code>:</li>
        </ol>
        <code>define('ATLASPRESS_HUBSPOT_CONTENT_TYPE', 123); // Your content type ID</code>
    </div>

    <script>
        function copyWebhook() {
            const url = document.getElementById('webhookUrl').textContent.trim();
            navigator.clipboard.writeText(url).then(() => {
                document.getElementById('copied').style.display = 'inline';
                setTimeout(() => {
                    document.getElementById('copied').style.display = 'none';
                }, 2000);
            });
        }
    </script>
</body>
</html>
