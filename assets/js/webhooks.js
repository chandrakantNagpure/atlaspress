const { createElement, useState } = wp.element;
const { createRoot } = wp.element;

document.addEventListener('DOMContentLoaded', () => {
    const webhooksRoot = document.getElementById('atlaspress-webhooks-app');
    if (!webhooksRoot) {
        return;
    }

    const Header = window.atlaspressHeader || (() => null);
    const config = window.atlaspress_webhooks_data || {};

    const WebhooksApp = () => {
        const [webhooks, setWebhooks] = useState(config.webhooks || {});
        const [newWebhook, setNewWebhook] = useState({
            event: 'atlaspress_entry_created',
            url: '',
            secret: ''
        });

        const events = [
            { value: 'atlaspress_entry_created', label: 'Entry Created' },
            { value: 'atlaspress_entry_updated', label: 'Entry Updated' },
            { value: 'atlaspress_entry_deleted', label: 'Entry Deleted' }
        ];

        const saveWebhooks = (updated) => {
            fetch(config.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'atlaspress_save_webhooks',
                    nonce: config.nonce,
                    webhooks: JSON.stringify(updated)
                })
            })
                .then(() => {
                    setWebhooks(updated);
                })
                .catch(() => {});
        };

        const addWebhook = () => {
            if (!newWebhook.url) {
                alert('URL is required');
                return;
            }

            const updated = { ...webhooks };
            if (!updated[newWebhook.event]) {
                updated[newWebhook.event] = [];
            }
            updated[newWebhook.event].push({
                url: newWebhook.url,
                secret: newWebhook.secret
            });

            saveWebhooks(updated);
            setNewWebhook({ event: 'atlaspress_entry_created', url: '', secret: '' });
        };

        const deleteWebhook = (event, index) => {
            const updated = { ...webhooks };
            updated[event].splice(index, 1);
            if (updated[event].length === 0) {
                delete updated[event];
            }
            saveWebhooks(updated);
        };

        return createElement(
            'div',
            null,
            createElement(Header, { activePage: 'webhooks', notificationCount: 0 }),
            createElement(
                'div',
                { style: { padding: '20px' } },
                createElement('h2', { style: { marginBottom: '20px' } }, 'Webhooks'),
                createElement('p', { style: { marginBottom: '20px' } }, 'Trigger HTTP requests when entries are created, updated, or deleted.'),
                createElement(
                    'div',
                    { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    createElement('h2', null, 'Add Webhook'),
                    createElement(
                        'table',
                        { className: 'form-table' },
                        createElement(
                            'tbody',
                            null,
                            createElement(
                                'tr',
                                null,
                                createElement('th', null, 'Event'),
                                createElement(
                                    'td',
                                    null,
                                    createElement(
                                        'select',
                                        {
                                            value: newWebhook.event,
                                            onChange: (e) => setNewWebhook({ ...newWebhook, event: e.target.value }),
                                            style: { width: '100%' }
                                        },
                                        events.map((event) => createElement('option', { key: event.value, value: event.value }, event.label))
                                    )
                                )
                            ),
                            createElement(
                                'tr',
                                null,
                                createElement('th', null, 'URL *'),
                                createElement(
                                    'td',
                                    null,
                                    createElement('input', {
                                        type: 'url',
                                        value: newWebhook.url,
                                        onChange: (e) => setNewWebhook({ ...newWebhook, url: e.target.value }),
                                        placeholder: 'https://example.com/webhook',
                                        style: { width: '100%' }
                                    })
                                )
                            ),
                            createElement(
                                'tr',
                                null,
                                createElement('th', null, 'Secret (Optional)'),
                                createElement(
                                    'td',
                                    null,
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
                createElement(
                    'div',
                    { className: 'postbox', style: { padding: '20px', marginTop: '20px' } },
                    createElement('h2', null, 'Active Webhooks'),
                    Object.keys(webhooks).length === 0
                        ? createElement('p', null, 'No webhooks configured.')
                        : Object.entries(webhooks).map(([event, hooks]) =>
                            createElement(
                                'div',
                                { key: event, style: { marginBottom: '20px' } },
                                createElement('h3', null, (events.find((item) => item.value === event) || {}).label || event),
                                createElement(
                                    'table',
                                    { className: 'wp-list-table widefat fixed striped' },
                                    createElement(
                                        'thead',
                                        null,
                                        createElement(
                                            'tr',
                                            null,
                                            createElement('th', null, 'URL'),
                                            createElement('th', { style: { width: '100px' } }, 'Secret'),
                                            createElement('th', { style: { width: '80px' } }, 'Actions')
                                        )
                                    ),
                                    createElement(
                                        'tbody',
                                        null,
                                        hooks.map((hook, index) =>
                                            createElement(
                                                'tr',
                                                { key: `${event}-${index}` },
                                                createElement('td', null, createElement('code', null, hook.url)),
                                                createElement('td', null, hook.secret ? 'Set' : '-'),
                                                createElement(
                                                    'td',
                                                    null,
                                                    createElement(
                                                        'button',
                                                        {
                                                            className: 'button button-small',
                                                            onClick: () => deleteWebhook(event, index)
                                                        },
                                                        'Delete'
                                                    )
                                                )
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

    createRoot(webhooksRoot).render(createElement(WebhooksApp));
});
