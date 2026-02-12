<?php
namespace AtlasPress\Admin\Pages;

class Webhooks {
    
    public static function render() {
        $webhooks = get_option('atlaspress_webhooks', []);
        ?>
        <div class="wrap">
            <h1>Webhooks</h1>
            <p>Trigger HTTP requests when entries are created, updated, or deleted.</p>
            
            <div id="webhooks-app"></div>
        </div>
        
        <script>
        const WebhooksApp = () => {
            const [webhooks, setWebhooks] = React.useState(<?php echo wp_json_encode($webhooks); ?>);
            const [newWebhook, setNewWebhook] = React.useState({ event: 'atlaspress_entry_created', url: '', secret: '' });
            
            const events = [
                { value: 'atlaspress_entry_created', label: 'Entry Created' },
                { value: 'atlaspress_entry_updated', label: 'Entry Updated' },
                { value: 'atlaspress_entry_deleted', label: 'Entry Deleted' }
            ];
            
            const addWebhook = () => {
                if (!newWebhook.url) return alert('URL is required');
                
                const updated = { ...webhooks };
                if (!updated[newWebhook.event]) updated[newWebhook.event] = [];
                updated[newWebhook.event].push({ url: newWebhook.url, secret: newWebhook.secret });
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'atlaspress_save_webhooks',
                        nonce: '<?php echo wp_create_nonce('atlaspress_webhooks'); ?>',
                        webhooks: JSON.stringify(updated)
                    })
                }).then(() => {
                    setWebhooks(updated);
                    setNewWebhook({ event: 'atlaspress_entry_created', url: '', secret: '' });
                });
            };
            
            const deleteWebhook = (event, index) => {
                const updated = { ...webhooks };
                updated[event].splice(index, 1);
                if (updated[event].length === 0) delete updated[event];
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'atlaspress_save_webhooks',
                        nonce: '<?php echo wp_create_nonce('atlaspress_webhooks'); ?>',
                        webhooks: JSON.stringify(updated)
                    })
                }).then(() => setWebhooks(updated));
            };
            
            return React.createElement('div', null,
                React.createElement('div', { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    React.createElement('h2', null, 'Add Webhook'),
                    React.createElement('table', { className: 'form-table' },
                        React.createElement('tbody', null,
                            React.createElement('tr', null,
                                React.createElement('th', null, 'Event'),
                                React.createElement('td', null,
                                    React.createElement('select', {
                                        value: newWebhook.event,
                                        onChange: (e) => setNewWebhook({ ...newWebhook, event: e.target.value }),
                                        style: { width: '100%' }
                                    }, events.map(e => React.createElement('option', { key: e.value, value: e.value }, e.label)))
                                )
                            ),
                            React.createElement('tr', null,
                                React.createElement('th', null, 'URL *'),
                                React.createElement('td', null,
                                    React.createElement('input', {
                                        type: 'url',
                                        value: newWebhook.url,
                                        onChange: (e) => setNewWebhook({ ...newWebhook, url: e.target.value }),
                                        placeholder: 'https://example.com/webhook',
                                        style: { width: '100%' }
                                    })
                                )
                            ),
                            React.createElement('tr', null,
                                React.createElement('th', null, 'Secret (Optional)'),
                                React.createElement('td', null,
                                    React.createElement('input', {
                                        type: 'text',
                                        value: newWebhook.secret,
                                        onChange: (e) => setNewWebhook({ ...newWebhook, secret: e.target.value }),
                                        placeholder: 'Used for HMAC signature verification',
                                        style: { width: '100%' }
                                    })
                                )
                            )
                        )
                    ),
                    React.createElement('button', { className: 'button button-primary', onClick: addWebhook }, 'Add Webhook')
                ),
                
                React.createElement('div', { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    React.createElement('h2', null, 'Active Webhooks'),
                    Object.keys(webhooks).length === 0 
                        ? React.createElement('p', null, 'No webhooks configured.')
                        : Object.entries(webhooks).map(([event, hooks]) =>
                            React.createElement('div', { key: event, style: { marginBottom: '20px' } },
                                React.createElement('h3', null, events.find(e => e.value === event)?.label || event),
                                React.createElement('table', { className: 'wp-list-table widefat fixed striped' },
                                    React.createElement('thead', null,
                                        React.createElement('tr', null,
                                            React.createElement('th', null, 'URL'),
                                            React.createElement('th', { style: { width: '100px' } }, 'Secret'),
                                            React.createElement('th', { style: { width: '80px' } }, 'Actions')
                                        )
                                    ),
                                    React.createElement('tbody', null,
                                        hooks.map((hook, index) =>
                                            React.createElement('tr', { key: index },
                                                React.createElement('td', null, React.createElement('code', null, hook.url)),
                                                React.createElement('td', null, hook.secret ? '✓ Set' : '—'),
                                                React.createElement('td', null,
                                                    React.createElement('button', {
                                                        className: 'button button-small',
                                                        onClick: () => deleteWebhook(event, index)
                                                    }, 'Delete')
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                )
            );
        };
        
        ReactDOM.render(React.createElement(WebhooksApp), document.getElementById('webhooks-app'));
        </script>
        <?php
    }
}
