<?php
namespace AtlasPress\Admin\Pages;

class Webhooks {
    
    public static function render() {
        $webhooks = get_option('atlaspress_webhooks', []);
        if(!is_array($webhooks)) {
            $webhooks = [];
        }

        $retry_log = \AtlasPress\Core\Webhooks::get_retry_log();
        if(!is_array($retry_log)) {
            $retry_log = [];
        }

        $is_pro_active = \AtlasPress\Core\ProVersion::is_pro_active();
        ?>
        <div class="wrap">
            <div id="atlaspress-webhooks-app"></div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const { createElement, useState } = wp.element;
            const { createRoot } = wp.element;
            
            const Header = window.atlaspressHeader || (() => null);
            
            const WebhooksApp = () => {
            const [webhooks, setWebhooks] = useState(<?php echo wp_json_encode($webhooks); ?>);
            const [retryLog] = useState(<?php echo wp_json_encode(array_slice($retry_log, -50)); ?>);
            const [newWebhook, setNewWebhook] = useState({ event: 'atlaspress_entry_created', url: '', secret: '' });
            const isProActive = <?php echo $is_pro_active ? 'true' : 'false'; ?>;
            
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
            
            return createElement('div', null,
                createElement(Header, { activePage: 'webhooks', notificationCount: 0 }),
                createElement('div', { style: { padding: '20px' } },
                    createElement('h1', { style: { marginBottom: '20px' } }, 'Webhooks'),
                    createElement('p', { style: { marginBottom: '20px' } }, 'Trigger HTTP requests when entries are created, updated, or deleted.'),
                createElement('div', { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    createElement('h2', null, 'Add Webhook'),
                    createElement('table', { className: 'form-table' },
                        createElement('tbody', null,
                            createElement('tr', null,
                                createElement('th', null, 'Event'),
                                createElement('td', null,
                                    createElement('select', {
                                        value: newWebhook.event,
                                        onChange: (e) => setNewWebhook({ ...newWebhook, event: e.target.value }),
                                        style: { width: '100%' }
                                    }, events.map(e => createElement('option', { key: e.value, value: e.value }, e.label)))
                                )
                            ),
                            createElement('tr', null,
                                createElement('th', null, 'URL *'),
                                createElement('td', null,
                                    createElement('input', {
                                        type: 'url',
                                        value: newWebhook.url,
                                        onChange: (e) => setNewWebhook({ ...newWebhook, url: e.target.value }),
                                        placeholder: 'https://example.com/webhook',
                                        style: { width: '100%' }
                                    })
                                )
                            ),
                            createElement('tr', null,
                                createElement('th', null, 'Secret (Optional)'),
                                createElement('td', null,
                                    createElement('input', {
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
                    createElement('button', { className: 'button button-primary', onClick: addWebhook }, 'Add Webhook')
                ),
                
                createElement('div', { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    createElement('h2', null, 'Active Webhooks'),
                    Object.keys(webhooks).length === 0 
                        ? createElement('p', null, 'No webhooks configured.')
                        : Object.entries(webhooks).map(([event, hooks]) =>
                            createElement('div', { key: event, style: { marginBottom: '20px' } },
                                createElement('h3', null, events.find(e => e.value === event)?.label || event),
                                createElement('table', { className: 'wp-list-table widefat fixed striped' },
                                    createElement('thead', null,
                                        createElement('tr', null,
                                            createElement('th', null, 'URL'),
                                            createElement('th', { style: { width: '100px' } }, 'Secret'),
                                            createElement('th', { style: { width: '80px' } }, 'Actions')
                                        )
                                    ),
                                    createElement('tbody', null,
                                        hooks.map((hook, index) =>
                                            createElement('tr', { key: index },
                                                createElement('td', null, createElement('code', null, hook.url)),
                                                createElement('td', null, hook.secret ? '✓ Set' : '—'),
                                                createElement('td', null,
                                                    createElement('button', {
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
                ),
                createElement('div', { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    createElement('h2', null, 'Delivery Retry Log'),
                    !isProActive
                        ? createElement('p', { style: { margin: 0, color: '#6b7280' } }, 'Advanced webhook retries are available with an active Pro license.')
                        : retryLog.length === 0
                            ? createElement('p', { style: { margin: 0 } }, 'No retry events recorded yet.')
                            : createElement('table', { className: 'wp-list-table widefat fixed striped' },
                                createElement('thead', null,
                                    createElement('tr', null,
                                        createElement('th', { style: { width: '170px' } }, 'Recorded'),
                                        createElement('th', { style: { width: '180px' } }, 'Event'),
                                        createElement('th', null, 'URL'),
                                        createElement('th', { style: { width: '70px' } }, 'Try'),
                                        createElement('th', { style: { width: '90px' } }, 'Status'),
                                        createElement('th', { style: { width: '220px' } }, 'Message')
                                    )
                                ),
                                createElement('tbody', null,
                                    retryLog
                                        .slice()
                                        .reverse()
                                        .map((item, index) =>
                                            createElement('tr', { key: index },
                                                createElement('td', null, item.recorded_at || 'n/a'),
                                                createElement('td', null, item.event || 'unknown'),
                                                createElement('td', null, createElement('code', null, item.url || '')),
                                                createElement('td', null, item.attempt || 1),
                                                createElement('td', null, item.status || 'unknown'),
                                                createElement('td', null, item.message || '')
                                            )
                                        )
                                )
                            )
                )
                )
            );
        };
        
        const root = createRoot(document.getElementById('atlaspress-webhooks-app'));
        root.render(createElement(WebhooksApp));
        });
        </script>
        <?php
    }
}
