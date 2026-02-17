<?php
namespace AtlasPress\Admin\Pages;

class Help
{

    public static function render()
    {
        ?>
        <div class="wrap">
            <h1>AtlasPress Help & Documentation</h1>

            <div style="max-width: 1200px;">

                <!-- Quick Start -->
                <div class="postbox" style="padding: 20px; margin-bottom: 20px;">
                    <h2>🚀 Quick Start Guide</h2>
                    <ol style="line-height: 2;">
                        <li><strong>Run Setup Wizard:</strong> Go to AtlasPress > Setup and choose your project type</li>
                        <li><strong>Create Content Types:</strong> Define your data structure with custom fields</li>
                        <li><strong>Add Entries:</strong> Submit data via REST API or admin interface</li>
                        <li><strong>Integrate Frontend:</strong> Use REST API or GraphQL to fetch data</li>
                    </ol>
                </div>

                <!-- REST API -->
                <div class="postbox" style="padding: 20px; margin-bottom: 20px;">
                    <h2>📡 REST API Endpoints</h2>
                    <table class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th>Method</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/content-types</code></td>
                                <td>GET</td>
                                <td>List all content types</td>
                            </tr>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/content-types/{id}/entries</code></td>
                                <td>GET</td>
                                <td>Get entries for a content type</td>
                            </tr>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/content-types/{id}/entries</code></td>
                                <td>POST</td>
                                <td>Create new entry</td>
                            </tr>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/entries/{id}</code></td>
                                <td>PUT</td>
                                <td>Update entry</td>
                            </tr>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/entries/{id}</code></td>
                                <td>DELETE</td>
                                <td>Delete entry</td>
                            </tr>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/graphql</code></td>
                                <td>POST</td>
                                <td>GraphQL queries</td>
                            </tr>
                            <tr>
                                <td><code>/wp-json/atlaspress/v1/upload</code></td>
                                <td>POST</td>
                                <td>Upload files</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Code Examples -->
                <div class="postbox" style="padding: 20px; margin-bottom: 20px;">
                    <h2>💻 Code Examples</h2>

                    <h3>JavaScript (Fetch API)</h3>
                    <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>// Submit form data
        const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            title: 'Contact Form',
            data: {
              name: 'John Doe',
              email: 'john@example.com',
              message: 'Hello!'
            }
          })
        });

        // Get entries
        const entries = await fetch('/wp-json/atlaspress/v1/content-types/1/entries')
          .then(r => r.json());</code></pre>

                    <h3>React/NextJS</h3>
                    <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>const [entries, setEntries] = useState([]);

        useEffect(() => {
          fetch('/wp-json/atlaspress/v1/content-types/1/entries')
            .then(r => r.json())
            .then(data => setEntries(data.data));
        }, []);</code></pre>

                    <h3>GraphQL</h3>
                    <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;"><code>const query = `{
          entries(contentTypeId: 1, limit: 10) {
            id, title, created_at
          }
        }`;

        const response = await fetch('/wp-json/atlaspress/v1/graphql', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query })
        });</code></pre>
                </div>

                <!-- Field Types -->
                <div class="postbox" style="padding: 20px; margin-bottom: 20px;">
                    <h2>📝 Available Field Types</h2>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <div>• Text</div>
                        <div>• Textarea</div>
                        <div>• Rich Text</div>
                        <div>• Number</div>
                        <div>• Email</div>
                        <div>• URL</div>
                        <div>• Phone</div>
                        <div>• Date</div>
                        <div>• Time</div>
                        <div>• DateTime</div>
                        <div>• Select Dropdown</div>
                        <div>• Radio Buttons</div>
                        <div>• Checkbox</div>
                        <div>• Multiple Checkboxes</div>
                        <div>• Range Slider</div>
                        <div>• Color Picker</div>
                        <div>• Password</div>
                        <div>• Hidden</div>
                        <div>• File Upload</div>
                        <div>• Relationship</div>
                        <div>• JSON Data</div>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="postbox" style="padding: 20px; margin-bottom: 20px;">
                    <h2>🔧 Troubleshooting</h2>

                    <h3>Common Issues:</h3>
                    <dl style="line-height: 2;">
                        <dt><strong>Q: Getting 401 Unauthorized errors?</strong></dt>
                        <dd>A: Make sure you're logged in to WordPress or configure CORS in Security settings.</dd>

                        <dt><strong>Q: File uploads not working?</strong></dt>
                        <dd>A: Check WordPress upload limits in php.ini (upload_max_filesize, post_max_size).</dd>

                        <dt><strong>Q: GraphQL queries failing?</strong></dt>
                        <dd>A: Verify your query syntax and ensure content type IDs are correct.</dd>

                        <dt><strong>Q: Entries not showing?</strong></dt>
                        <dd>A: Check if content type has entries and verify permissions.</dd>

                        <dt><strong>Q: Rate limit errors?</strong></dt>
                        <dd>A: This is a WordPress REST API limit. Clear browser cache or wait a moment.</dd>
                    </dl>
                </div>

            </div>
        </div>
        <?php
    }
}
