const { createElement, useState, useEffect } = wp.element;

const Header = ({ notificationCount = 0, newEntries = [] }) => {
    const [showNotifications, setShowNotifications] = useState(false);
    const currentPage = new URLSearchParams(window.location.search).get('page');

    const menuItems = [
        { label: 'Dashboard', page: 'atlaspress' },
        { label: 'Content Types', page: 'atlaspress-content-types' },
        { label: 'Entries', page: 'atlaspress-entries' },
        { label: 'Settings', page: 'atlaspress-webhooks' }
    ];

    return createElement(
        'div',
        { style: { background: '#fff', borderBottom: '1px solid #ddd', marginBottom: '20px', padding: '5px' } },
        createElement(
            'div',
            { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
            createElement(
                'div',
                { style: { display: 'flex', gap: '30px', alignItems: 'center' } },
                createElement('img', {
                    src: atlaspress_ajax.plugin_url + 'assets/logo.png',
                    alt: 'AtlasPress',
                    style: { height: '80px' }
                }),
                createElement(
                    'nav',
                    { style: { display: 'flex', gap: '5px' } },
                    menuItems.map(item =>
                        createElement(
                            'a',
                            {
                                key: item.page,
                                href: `?page=${item.page}`,
                                style: {
                                    textDecoration: 'none',
                                    color: currentPage === item.page ? '#667f6a' : '#666',
                                    fontWeight: currentPage === item.page ? '600' : '400',
                                    padding: '6px 12px',
                                    borderRadius: '4px',
                                    background: currentPage === item.page ? '#f0f6f0' : 'transparent',
                                    fontSize: '14px'
                                }
                            },
                            item.label
                        )
                    )
                )
            ),
        )
    );
};

const { createRoot } = wp.element;
const apiFetch = wp.apiFetch;

const SchemaBuilder = ({ contentTypeId, onClose, allContentTypes }) => {
    const [fields, setFields] = useState([]);
    const [fieldTypes, setFieldTypes] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [showAddField, setShowAddField] = useState(false);
    const [newField, setNewField] = useState({ name: '', type: 'text', label: '', required: false });

    useEffect(() => {
        Promise.all([
            apiFetch({ path: `/atlaspress/v1/content-types/${contentTypeId}` }),
            apiFetch({ path: '/atlaspress/v1/field-types' })
        ]).then(([contentType, types]) => {
            setFields(contentType.settings?.fields || []);
            setFieldTypes(types);
            setLoading(false);
        }).catch(() => setLoading(false));
    }, [contentTypeId]);

    const addField = () => {
        if (!newField.name.trim() || !newField.label.trim()) return;

        const field = {
            ...newField,
            id: Date.now(),
            name: newField.name.toLowerCase().replace(/[^a-z0-9]/g, '_')
        };

        setFields([...fields, field]);
        setNewField({ name: '', type: 'text', label: '', required: false });
        setShowAddField(false);
    };

    const removeField = (index) => {
        setFields(fields.filter((_, i) => i !== index));
    };

    const updateField = (index, updates) => {
        const updated = [...fields];
        updated[index] = { ...updated[index], ...updates };
        setFields(updated);
    };

    const saveSchema = () => {
        setSaving(true);
        apiFetch({
            path: `/atlaspress/v1/content-types/${contentTypeId}/schema`,
            method: 'PUT',
            data: { schema: fields }
        }).then(() => {
            setSaving(false);
            onClose();
        }).catch(() => setSaving(false));
    };

    if (loading) return createElement(LoadingSpinner);

    return createElement(
        'div',
        { style: { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.7)', zIndex: 9999 } },
        createElement(
            'div',
            {
                style: {
                    position: 'absolute',
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%, -50%)',
                    background: '#fff',
                    padding: '30px',
                    borderRadius: '8px',
                    width: '80%',
                    maxWidth: '800px',
                    maxHeight: '80vh',
                    overflow: 'auto'
                }
            },
            createElement(
                'div',
                { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' } },
                createElement('h2', { style: { margin: 0 } }, 'Schema Builder'),
                createElement('button', { className: 'button', onClick: onClose }, 'Close')
            ),

            createElement(
                'div',
                { style: { marginBottom: '20px' } },
                createElement(
                    'button',
                    {
                        className: 'button button-primary',
                        onClick: () => setShowAddField(!showAddField)
                    },
                    showAddField ? 'Cancel' : 'Add Field'
                )
            ),

            showAddField && createElement(
                'div',
                { className: 'postbox', style: { padding: '20px', marginBottom: '20px' } },
                createElement('h3', null, 'Add New Field'),
                createElement(
                    'div',
                    { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px', marginBottom: '15px' } },
                    createElement('input', {
                        type: 'text',
                        placeholder: 'Field Name',
                        value: newField.name,
                        onChange: (e) => setNewField({ ...newField, name: e.target.value })
                    }),
                    createElement('input', {
                        type: 'text',
                        placeholder: 'Field Label',
                        value: newField.label,
                        onChange: (e) => setNewField({ ...newField, label: e.target.value })
                    })
                ),
                createElement(
                    'div',
                    { style: { display: 'flex', gap: '15px', alignItems: 'center', marginBottom: '15px' } },
                    createElement(
                        'select',
                        {
                            value: newField.type,
                            onChange: (e) => setNewField({ ...newField, type: e.target.value }),
                            style: { flex: 1 }
                        },
                        Object.entries(fieldTypes).map(([key, type]) =>
                            createElement('option', { key, value: key }, type.label)
                        )
                    ),
                    createElement(
                        'label',
                        { style: { display: 'flex', alignItems: 'center', gap: '5px' } },
                        createElement('input', {
                            type: 'checkbox',
                            checked: newField.required,
                            onChange: (e) => setNewField({ ...newField, required: e.target.checked })
                        }),
                        'Required'
                    )
                ),
                ['select', 'radio', 'checkboxes'].includes(newField.type) && createElement(
                    'div',
                    { style: { marginBottom: '15px' } },
                    createElement('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Options (comma separated)'),
                    createElement('input', {
                        type: 'text',
                        placeholder: 'option1, option2, option3',
                        value: (newField.options || []).join(', '),
                        onChange: (e) => setNewField({ ...newField, options: e.target.value.split(',').map(o => o.trim()).filter(o => o) }),
                        style: { width: '100%', padding: '8px' }
                    })
                ),
                newField.type === 'relationship' && createElement(
                    'div',
                    { style: { marginBottom: '15px' } },
                    createElement('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Target Content Type'),
                    createElement(
                        'select',
                        {
                            value: newField.targetType || '',
                            onChange: (e) => setNewField({ ...newField, targetType: e.target.value }),
                            style: { width: '100%', padding: '8px' }
                        },
                        createElement('option', { value: '' }, 'Select content type...'),
                        (allContentTypes || []).map(type =>
                            createElement('option', { key: type.id, value: type.id }, type.name)
                        )
                    )
                ),
                createElement(
                    'button',
                    { className: 'button button-primary', onClick: addField },
                    'Add Field'
                )
            ),

            createElement(
                'div',
                { style: { marginBottom: '20px' } },
                fields.length === 0
                    ? createElement('p', { style: { textAlign: 'center', color: '#666' } }, 'No fields defined. Add your first field above.')
                    : fields.map((field, index) =>
                        createElement(
                            'div',
                            {
                                key: field.id || index,
                                className: 'postbox',
                                style: { padding: '15px', marginBottom: '10px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }
                            },
                            createElement(
                                'div',
                                null,
                                createElement('strong', null, field.label || field.name),
                                createElement('span', { style: { marginLeft: '10px', color: '#666' } }, `(${field.type})`),
                                field.required && createElement('span', { style: { marginLeft: '5px', color: '#d63638' } }, '*')
                            ),
                            createElement(
                                'button',
                                {
                                    className: 'button button-link-delete',
                                    onClick: () => removeField(index)
                                },
                                'Remove'
                            )
                        )
                    )
            ),

            createElement(
                'div',
                { style: { textAlign: 'right' } },
                createElement(
                    'button',
                    {
                        className: 'button button-primary button-large',
                        onClick: saveSchema,
                        disabled: saving
                    },
                    saving ? 'Saving...' : 'Save Schema'
                )
            )
        )
    );
};

const RelationshipField = ({ field, value, onChange, style }) => {
    const [options, setOptions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [search, setSearch] = useState('');

    const loadOptions = (searchTerm = '') => {
        if (!field.targetType) return;

        setLoading(true);
        apiFetch({
            path: `/atlaspress/v1/relationships/${field.targetType}?search=${encodeURIComponent(searchTerm)}`
        }).then(data => {
            setOptions(data || []);
            setLoading(false);
        }).catch(() => {
            setOptions([]);
            setLoading(false);
        });
    };

    useEffect(() => {
        loadOptions();
    }, [field.targetType]);

    const handleSearchChange = (e) => {
        const term = e.target.value;
        setSearch(term);
        if (term.length > 2 || term.length === 0) {
            loadOptions(term);
        }
    };

    return createElement(
        'div',
        null,
        createElement('input', {
            type: 'text',
            placeholder: 'Search related items...',
            value: search,
            onChange: handleSearchChange,
            style: { ...style, marginBottom: '5px' }
        }),
        createElement(
            'select',
            {
                value: value || '',
                onChange: (e) => onChange(e.target.value),
                style,
                disabled: loading,
                multiple: field.multiple
            },
            createElement('option', { value: '' }, loading ? 'Loading...' : 'Select related item...'),
            options.map(option =>
                createElement('option', { key: option.id, value: option.id }, option.title || `#${option.id}`)
            )
        ),
        options.length === 0 && !loading && search && createElement(
            'small',
            { style: { color: '#999', display: 'block', marginTop: '5px' } },
            'No items found'
        )
    );
};

const LiveUpdates = ({ onNotificationChange, onEntriesChange }) => {
    const [lastCheck, setLastCheck] = useState(new Date().toISOString());
    const [newEntries, setNewEntries] = useState([]);
    const [readEntries, setReadEntries] = useState(new Set());

    window.clearNotifications = () => {
        const allIds = new Set([...readEntries, ...newEntries.map(e => e.id)]);
        setReadEntries(allIds);
        setNewEntries([]);
        if (onNotificationChange) onNotificationChange(0);
        if (onEntriesChange) onEntriesChange([]);
    };

    window.markNotificationRead = (entryId) => {
        setReadEntries(prev => new Set([...prev, entryId]));
        const updated = newEntries.filter(e => e.id !== entryId);
        setNewEntries(updated);
        if (onNotificationChange) onNotificationChange(updated.length);
        if (onEntriesChange) onEntriesChange(updated);
    };

    useEffect(() => {
        const pollInterval = setInterval(async () => {
            try {
                const response = await apiFetch({
                    path: `/atlaspress/v1/entries/poll?last_check=${encodeURIComponent(lastCheck)}`
                });

                if (response.count > 0) {
                    const unreadEntries = response.new_entries.filter(e => !readEntries.has(e.id));
                    if (unreadEntries.length > 0) {
                        setNewEntries(prev => {
                            const combined = [...prev, ...unreadEntries].filter((e, i, arr) =>
                                arr.findIndex(x => x.id === e.id) === i
                            );
                            if (onNotificationChange) onNotificationChange(combined.length);
                            if (onEntriesChange) onEntriesChange(combined);
                            return combined;
                        });
                    } else if (unreadEntries.length === 0 && response.new_entries.length > 0) {
                        if (onNotificationChange) onNotificationChange(0);
                        if (onEntriesChange) onEntriesChange([]);
                    }
                    setLastCheck(response.last_check);

                    if ('Notification' in window && Notification.permission === 'granted') {
                        response.new_entries.forEach(entry => {
                            new Notification('New Submission', {
                                body: `${entry.content_type_name}: ${entry.title}`
                            });
                        });
                    }

                    window.dispatchEvent(new CustomEvent('atlaspress-refresh-entries'));
                }
            } catch (error) {
                console.error('Polling error:', error);
            }
        }, 5000);

        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        return () => clearInterval(pollInterval);
    }, [lastCheck, readEntries]);

    return null;
};

const SetupWizard = () => {
    const [step, setStep] = useState('welcome');
    const [setupData, setSetupData] = useState({});
    const [loading, setLoading] = useState(false);
    const [notification, setNotification] = useState(null);
    const [contentTypesCount, setContentTypesCount] = useState(0);
    const [contentTypesList, setContentTypesList] = useState([]);

    const showNotification = (message, type = 'error') => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), 5000);
    };

    // Check existing content types count and list
    useEffect(() => {
        apiFetch({ path: '/atlaspress/v1/content-types' })
            .then(response => {
                const types = response.data || [];
                setContentTypesCount(types.length);
                setContentTypesList(types);
            })
            .catch(() => {
                setContentTypesCount(0);
                setContentTypesList([]);
            });
    }, []);

    // Function to delete a single content type
    const deleteContentTypeFromSetup = async (id, name) => {
        if (!confirm(`Delete "${name}" and all its entries? This cannot be undone.`)) return;
        
        try {
            await apiFetch({
                path: `/atlaspress/v1/content-types/${id}`,
                method: 'DELETE'
            });
            // Update count after deletion
            const response = await apiFetch({ path: '/atlaspress/v1/content-types' });
            setContentTypesCount(response.data?.length || 0);
            showNotification(`"${name}" deleted successfully!`, 'success');
        } catch (error) {
            showNotification('Failed to delete: ' + error.message, 'error');
        }
    };

    // Free version limit
    const FREE_VERSION_LIMIT = 2;
    const isLimitReached = contentTypesCount >= FREE_VERSION_LIMIT;

    // Check if atlaspress_ajax is available
    if (typeof atlaspress_ajax === 'undefined') {
        console.error('AtlasPress: AJAX configuration not found');
        return createElement('div', { className: 'notice notice-error' },
            createElement('p', null, 'Setup configuration error. Please refresh the page.')
        );
    }

    const steps = {
        welcome: {
            title: 'Setup AtlasPress',
            content: () => createElement(
                'div',
                { className: 'setup-step' },
                createElement('h2', { style: { marginBottom: '16px' } }, 'Choose your project type'),
                createElement(
                    'span',
                    { className: `limit-badge ${isLimitReached ? 'limit-reached' : ''}`, style: { marginBottom: '20px', display: 'inline-block' } },
                    createElement('span', { className: 'count' }, contentTypesCount),
                    '/',
                    FREE_VERSION_LIMIT,
                    ' projects set up',
                    isLimitReached && ' (limit)'
                ),
                isLimitReached && createElement(
                    'div',
                    { className: 'upgrade-notice', style: { marginBottom: '20px' } },
                    createElement(
                        'div',
                        { className: 'upgrade-notice-content' },
                        createElement('span', { className: 'upgrade-notice-icon' }, '🔒'),
                        createElement(
                            'div',
                            { className: 'upgrade-notice-text' },
                            createElement('h4', null, 'Free Version Limit Reached'),
                            createElement('p', null, `You've already set up ${contentTypesCount} project types. Delete an existing project to set up a new one, or upgrade to Pro!`)
                        )
                    ),
                    createElement(
                        'div',
                        { style: { display: 'flex', gap: '10px', flexWrap: 'wrap' } },
                        createElement(
                            'button',
                            {
                                className: 'button button-link-delete',
                                onClick: async () => {
                                    if (!confirm(`This will delete all ${contentTypesCount} existing content types and their entries. Are you sure?`)) return;
                                    
                                    try {
                                        const types = await apiFetch({ path: '/atlaspress/v1/content-types' });
                                        if (types.data && types.data.length > 0) {
                                            const ids = types.data.map(t => t.id);
                                            await apiFetch({
                                                path: '/atlaspress/v1/content-types/bulk-delete',
                                                method: 'POST',
                                                data: { ids }
                                            });
                                            setContentTypesCount(0);
                                            showNotification('All content types deleted. You can now set up a new project!', 'success');
                                        }
                                    } catch (error) {
                                        showNotification('Failed to delete content types: ' + error.message, 'error');
                                    }
                                },
                                style: { fontSize: '13px', padding: '8px 16px' }
                            },
                            'Delete All'
                        ),
                        createElement(
                            'button',
                            { className: 'button-pro' },
                            'Upgrade to Pro (Coming Soon)'
                        )
                    )
                ),

                // Show existing content types when limit is reached
                isLimitReached && createElement(
                    'div',
                    { style: { marginBottom: '24px' } },
                    createElement('h3', { style: { marginBottom: '12px', fontSize: '16px' } }, 'Your Current Projects:'),
                    createElement(
                        'div',
                        { style: { display: 'grid', gap: '10px' } },
                        contentTypesList.map(type =>
                            createElement(
                                'div',
                                {
                                    key: type.id,
                                    style: {
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'center',
                                        padding: '12px 16px',
                                        background: '#fff',
                                        border: '2px solid var(--gray-200)',
                                        borderRadius: '8px'
                                    }
                                },
                                createElement('div', null,
                                    createElement('strong', null, type.name),
                                    createElement('span', { style: { marginLeft: '10px', fontSize: '12px', color: 'var(--gray-500)' } }, 
                                        `(${type.slug})`
                                    )
                                ),
                                createElement(
                                    'button',
                                    {
                                        className: 'button button-link-delete',
                                        onClick: () => deleteContentTypeFromSetup(type.id, type.name),
                                        style: { fontSize: '12px', padding: '4px 10px' }
                                    },
                                    'Delete'
                                )
                            )
                        )
                    ),
                    createElement('p', { style: { marginTop: '12px', fontSize: '13px', color: 'var(--gray-500)' } },
                        'Delete one project above to make space for a new one!'
                    )
                ),
                createElement(
                    'div',
                    { className: 'setup-options' },
                    createElement(
                        'button',
                        {
                            className: 'setup-option' + (isLimitReached ? ' disabled' : ''),
                            onClick: isLimitReached ? null : () => {
                                setSetupData({ ...setupData, setup_type: 'nextjs_forms' });
                                setStep('project_details');
                            },
                            disabled: isLimitReached,
                            style: isLimitReached ? { opacity: 0.5, cursor: 'not-allowed' } : {}
                        },
                        createElement('h3', null, 'NextJS/React Forms'),
                        createElement('p', null, 'Track form submissions from frontend apps')
                    ),
                    createElement(
                        'button',
                        {
                            className: 'setup-option' + (isLimitReached ? ' disabled' : ''),
                            onClick: isLimitReached ? null : () => {
                                setSetupData({ ...setupData, setup_type: 'headless_cms' });
                                setStep('project_details');
                            },
                            disabled: isLimitReached,
                            style: isLimitReached ? { opacity: 0.5, cursor: 'not-allowed' } : {}
                        },
                        createElement('h3', null, 'Headless CMS'),
                        createElement('p', null, 'Manage content with REST/GraphQL APIs')
                    ),
                    createElement(
                        'button',
                        {
                            className: 'setup-option' + (isLimitReached ? ' disabled' : ''),
                            onClick: isLimitReached ? null : () => {
                                setSetupData({ ...setupData, setup_type: 'api_backend' });
                                setStep('project_details');
                            },
                            disabled: isLimitReached,
                            style: isLimitReached ? { opacity: 0.5, cursor: 'not-allowed' } : {}
                        },
                        createElement('h3', null, 'API Backend'),
                        createElement('p', null, 'Store structured data and responses')
                    ),
                    createElement(
                        'button',
                        {
                            className: 'setup-option' + (isLimitReached ? ' disabled' : ''),
                            onClick: isLimitReached ? null : () => {
                                setSetupData({ ...setupData, setup_type: 'blank' });
                                setStep('project_details');
                            },
                            disabled: isLimitReached,
                            style: isLimitReached ? { opacity: 0.5, cursor: 'not-allowed' } : {}
                        },
                        createElement('h3', null, 'Blank Project'),
                        createElement('p', null, 'Start with no default content types')
                    )
                )
            )
        },
        project_details: {
            title: 'Project Details',
            content: () => createElement(
                'div',
                { className: 'setup-step' },
                createElement(
                    'div',
                    { className: 'setup-form' },
                    createElement('label', null, 'Project Name'),
                    createElement('input', {
                        type: 'text',
                        value: setupData.project_name || '',
                        onChange: (e) => setSetupData({ ...setupData, project_name: e.target.value }),
                        placeholder: 'My Project'
                    }),
                    createElement(
                        'div',
                        { className: 'setup-buttons' },
                        createElement(
                            'button',
                            {
                                className: 'button',
                                onClick: () => setStep('welcome')
                            },
                            'Back'
                        ),
                        createElement(
                            'button',
                            {
                                className: 'button button-primary',
                                onClick: () => setStep('preview'),
                                disabled: !setupData.project_name
                            },
                            'Continue'
                        )
                    )
                )
            )
        },
        preview: {
            title: 'Setup Preview',
            content: () => {
                const getPreviewContent = () => {
                    switch (setupData.setup_type) {
                        case 'nextjs_forms':
                            return {
                                description: 'Perfect for tracking form submissions from your NextJS/React applications. Start with a blank slate and create your own content types.',
                                contentTypes: ['None - you can create your own'],
                                codeExample: `// Submit form data to AtlasPress
const submitForm = async (data) => {
  await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
    method: 'POST',
    body: JSON.stringify({ title: 'Contact Form', data })
  });
};`
                            };
                        case 'headless_cms':
                            return {
                                description: 'Manage your content with a powerful headless CMS. Start with a blank slate and create your own content types.',
                                contentTypes: ['None - you can create your own'],
                                codeExample: `// Fetch blog posts
const posts = await fetch('/wp-json/atlaspress/v1/content-types/1/entries')
  .then(r => r.json());`
                            };
                        case 'api_backend':
                            return {
                                description: 'Store and manage structured data via REST/GraphQL APIs. Start with a blank slate and create your own content types.',
                                contentTypes: ['None - you can create your own'],
                                codeExample: `// Store API data
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
  method: 'POST',
  body: JSON.stringify({ data: apiResponse })
});`
                            };
                        case 'blank':
                            return {
                                description: 'Start with a clean slate - no default content types will be created',
                                contentTypes: ['None - you can create your own'],
                                codeExample: `// Create your own content types
// Visit Content Types page to get started`
                            };
                    }
                };

                const preview = getPreviewContent();

                return createElement(
                    'div',
                    { className: 'setup-preview' },
                    createElement('h3', null, `Setting up: ${setupData.project_name}`),
                    createElement('p', null, preview.description),
                    createElement('h4', null, 'Content Types to be created:'),
                    createElement(
                        'ul',
                        null,
                        preview.contentTypes.map(type =>
                            createElement('li', { key: type }, type)
                        )
                    ),
                    createElement('h4', null, 'Example Usage:'),
                    createElement('pre', null, preview.codeExample),
                    createElement(
                        'div',
                        { className: 'setup-buttons' },
                        createElement(
                            'button',
                            {
                                className: 'button button-secondary',
                                onClick: () => setStep('project_details')
                            },
                            'Back'
                        ),
                        createElement(
                            'button',
                            {
                                className: 'button button-primary button-large',
                                onClick: handleSetup,
                                disabled: loading
                            },
                            loading ? 'Setting up...' : 'Complete Setup'
                        )
                    )
                );
            }
        }
    };

    const handleSetup = async () => {
        setLoading(true);

        try {
            if (!setupData.setup_type || !setupData.project_name) {
                showNotification('Please complete all required fields.');
                setLoading(false);
                return;
            }

            const formData = new FormData();
            formData.append('action', 'atlaspress_setup');
            formData.append('setup_type', setupData.setup_type);
            formData.append('project_name', setupData.project_name);
            formData.append('nonce', atlaspress_ajax.nonce);

            const response = await fetch(atlaspress_ajax.ajaxurl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                showNotification('Setup completed successfully! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = result.data.redirect || atlaspress_ajax.admin_url + 'admin.php?page=atlaspress';
                }, 1500);
            } else {
                showNotification(result.data || 'Setup failed. Please try again.');
                setLoading(false);
            }
        } catch (error) {
            console.error('Setup error:', error);
            showNotification('Setup failed: ' + error.message);
            setLoading(false);
        }
    };

    const currentStep = steps[step];

    return createElement(
        'div',
        { className: 'atlaspress-setup-wizard' },

        notification && createElement(
            'div',
            {
                className: `notice notice-${notification.type === 'success' ? 'success' : 'error'}`,
                style: { margin: '0 0 20px 0' }
            },
            createElement('p', null, notification.message)
        ),

        createElement('h1', null, currentStep.title),
        currentStep.content()
    );
};

const SecurityApp = () => {
    const [apiKeys, setApiKeys] = useState([]);
    const [origins, setOrigins] = useState('');
    const [newKeyName, setNewKeyName] = useState('');
    const [generatedKey, setGeneratedKey] = useState('');
    const [loading, setLoading] = useState(false);
    const [notification, setNotification] = useState(null);

    useEffect(() => {
        const root = document.getElementById('atlaspress-security-app');
        if (root) {
            const keys = JSON.parse(root.dataset.keys || '[]');
            const savedOrigins = JSON.parse(root.dataset.origins || '[]');
            setApiKeys(keys);
            setOrigins(savedOrigins.join('\n'));
        }
    }, []);

    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), 3000);
    };

    const generateApiKey = async () => {
        if (!newKeyName.trim()) {
            showNotification('Please enter a key name', 'error');
            return;
        }

        setLoading(true);
        try {
            const formData = new FormData();
            formData.append('action', 'save_security_settings');
            formData.append('generate_api_key', '1');
            formData.append('api_key_name', newKeyName);

            const response = await fetch(atlaspress_ajax.ajaxurl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                setGeneratedKey(result.data.api_key);
                setApiKeys([...apiKeys, newKeyName]);
                setNewKeyName('');
                showNotification('API Key generated! Copy it now - it won\'t be shown again.');
            } else {
                showNotification('Failed to generate API key', 'error');
            }
        } catch (error) {
            showNotification('Error: ' + error.message, 'error');
        }
        setLoading(false);
    };

    const saveOrigins = async () => {
        setLoading(true);
        try {
            const formData = new FormData();
            formData.append('action', 'save_security_settings');
            formData.append('allowed_origins', origins);

            const response = await fetch(atlaspress_ajax.ajaxurl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                showNotification('CORS settings saved!');
            } else {
                showNotification('Failed to save settings', 'error');
            }
        } catch (error) {
            showNotification('Error: ' + error.message, 'error');
        }
        setLoading(false);
    };

    return createElement(
        'div',
        null,
        createElement(Header, { activePage: 'security', notificationCount: 0 }),
        createElement(LiveUpdates, { onNotificationChange: () => { } }),
        createElement(
            'div',
            { style: { maxWidth: '900px', padding: '20px' } },

            notification && createElement(
                'div',
                {
                    className: `notice notice-${notification.type === 'error' ? 'error' : 'success'}`,
                    style: { padding: '12px 15px', margin: '0 0 20px 0' }
                },
                createElement('p', null, notification.message)
            ),

            createElement('h2', null, 'API Security Settings'),

            // API Keys Section
            createElement(
                'div',
                { className: 'postbox', style: { padding: '20px', marginBottom: '20px' } },
                createElement('h3', null, '🔒 API Keys'),
                createElement('p', null, 'Generate API keys for secure access to your AtlasPress API endpoints.'),

                apiKeys.length > 0 && createElement(
                    'div',
                    { style: { marginBottom: '15px' } },
                    createElement('h4', null, 'Active Keys:'),
                    createElement(
                        'ul',
                        { style: { listStyle: 'disc', paddingLeft: '20px' } },
                        apiKeys.map(key => createElement('li', { key }, key))
                    )
                ),

                generatedKey && createElement(
                    'div',
                    { style: { padding: '15px', background: '#d4edda', borderRadius: '5px', marginBottom: '15px' } },
                    createElement('strong', null, 'Your new API Key (copy now):'),
                    createElement('br'),
                    createElement('code', { style: { fontSize: '14px', wordBreak: 'break-all' } }, generatedKey),
                    createElement('br'),
                    createElement('small', null, 'This key will not be shown again!')
                ),

                createElement(
                    'div',
                    { style: { display: 'flex', gap: '10px', alignItems: 'center' } },
                    createElement('input', {
                        type: 'text',
                        placeholder: 'Key name (e.g., Production API)',
                        value: newKeyName,
                        onChange: (e) => setNewKeyName(e.target.value),
                        style: { flex: 1, padding: '8px' }
                    }),
                    createElement(
                        'button',
                        {
                            className: 'button button-primary',
                            onClick: generateApiKey,
                            disabled: loading
                        },
                        loading ? 'Generating...' : 'Generate API Key'
                    )
                )
            ),

            // CORS Section
            createElement(
                'div',
                { className: 'postbox', style: { padding: '20px', marginBottom: '20px' } },
                createElement('h3', null, '🌐 CORS Settings'),
                createElement('p', null, 'Add allowed origins (one per line). Leave empty to allow all origins.'),
                createElement('textarea', {
                    value: origins,
                    onChange: (e) => setOrigins(e.target.value),
                    placeholder: 'https://example.com\nhttps://app.example.com',
                    rows: 5,
                    style: { width: '100%', padding: '8px', fontFamily: 'monospace' }
                }),
                createElement(
                    'button',
                    {
                        className: 'button button-primary',
                        onClick: saveOrigins,
                        disabled: loading,
                        style: { marginTop: '10px' }
                    },
                    loading ? 'Saving...' : 'Save CORS Settings'
                )
            ),

        )
    );
};

const StatCard = ({ label, value, color = '#6366f1' }) =>
    createElement(
        'div',
        {
            className: 'atlaspress-stat-card',
            style: {
                background: '#fff',
                padding: '32px',
                borderRadius: '16px',
                border: '2px solid var(--gray-200)',
                transition: 'all 0.3s ease',
                position: 'relative',
                overflow: 'hidden',
                boxShadow: 'var(--shadow-md)',
                cursor: 'pointer'
            },
            onMouseEnter: (e) => {
                e.target.style.transform = 'translateY(-4px)';
                e.target.style.boxShadow = 'var(--shadow-xl)';
                e.target.style.borderColor = 'var(--primary-light)';
            },
            onMouseLeave: (e) => {
                e.target.style.transform = 'translateY(0)';
                e.target.style.boxShadow = 'var(--shadow-md)';
                e.target.style.borderColor = 'var(--gray-200)';
            }
        },
        createElement('div', {
            style: {
                position: 'absolute',
                left: 0,
                top: 0,
                bottom: 0,
                width: '4px',
                // background: `linear-gradient(135deg, ${color}, ${color}dd)`
            }
        }),
        createElement('div', {
            style: {
                fontSize: '13px',
                fontWeight: '600',
                color: 'var(--gray-500)',
                textTransform: 'uppercase',
                letterSpacing: '0.8px',
                marginBottom: '12px'
            }
        }, label),
        createElement('div', {
            style: {
                fontSize: '32px',
                fontWeight: '800',
                color: 'var(--text)',
                background: `linear-gradient(135deg, ${color}, var(--secondary))`,
                WebkitBackgroundClip: 'text',
                WebkitTextFillColor: 'transparent',
                backgroundClip: 'text'
            }
        }, value || '0')
    );

const LoadingSpinner = () => createElement(
    'div',
    {
        className: 'atlaspress-loading',
        style: {
            padding: '48px',
            textAlign: 'center',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '12px',
            color: 'var(--text)',
            fontSize: '16px',
            fontWeight: '500'
        }
    },
    'Loading...'
);

const DashboardApp = () => {
    const [notificationCount, setNotificationCount] = useState(0);
    const [newEntries, setNewEntries] = useState([]);
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showResetModal, setShowResetModal] = useState(false);
    const [notification, setNotification] = useState(null);

    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), 3000);
    };

    const RecentEntriesWidget = ({ entries }) => createElement(
        'div',
        { className: 'dashboard-widget' },
        createElement('h3', null, 'Recent Submissions'),
        entries.length === 0
            ? createElement('p', { style: { color: 'var(--gray-500)' } }, 'No recent submissions')
            : createElement(
                'div',
                { className: 'recent-entries-list' },
                entries.map(entry => createElement(
                    'div',
                    { key: entry.id, className: 'recent-entry' },
                    createElement('div', { className: 'recent-entry-title' }, entry.title || 'Untitled'),
                    createElement('div', { className: 'recent-entry-meta' },
                        `${entry.content_type_name} • ${new Date(entry.created_at).toLocaleDateString()}`
                    )
                ))
            )
    );

    const TopTypesWidget = ({ types }) => createElement(
        'div',
        { className: 'dashboard-widget' },
        createElement('h3', null, 'Top Content Types'),
        types.length === 0
            ? createElement('p', { style: { color: 'var(--gray-500)' } }, 'No content types yet')
            : createElement(
                'div',
                { style: { display: 'flex', flexDirection: 'column', gap: '8px' } },
                types.map(type => createElement(
                    'div',
                    {
                        key: type.name,
                        style: {
                            display: 'flex',
                            justifyContent: 'space-between',
                            padding: '8px 12px',
                            background: 'var(--gray-50)',
                            borderRadius: '6px'
                        }
                    },
                    createElement('span', null, type.name),
                    createElement('span', { style: { fontWeight: '600', color: 'var(--primary)' } }, type.entry_count)
                ))
            )
    );

    const handleReset = async () => {
        try {
            if (!atlaspress_ajax || !atlaspress_ajax.nonce) {
                showNotification('Configuration error. Please refresh the page.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'atlaspress_reset_setup');
            formData.append('nonce', atlaspress_ajax.nonce);

            const response = await fetch(atlaspress_ajax.ajaxurl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                showNotification('Setup reset successfully! Redirecting to setup wizard...', 'success');
                setTimeout(() => {
                    window.location.href = result.data.redirect || atlaspress_ajax.admin_url + 'admin.php?page=atlaspress-setup';
                }, 1500);
            } else {
                showNotification('Reset failed: ' + (result.data || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Reset error:', error);
            showNotification('Reset failed: ' + error.message, 'error');
        }
        setShowResetModal(false);
    };

    useEffect(() => {
        apiFetch({ path: '/atlaspress/v1/dashboard' })
            .then(data => {
                setStats(data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    if (loading) return createElement(LoadingSpinner);
    if (!stats) return createElement('div', null, 'Failed to load dashboard data.');

    return createElement('div', null, createElement(Header, { notificationCount, newEntries }),
        createElement('div',
            { className: 'atlaspress-dashboard' },

            notification && createElement(
                'div',
                {
                    className: `notice notice-${notification.type === 'error' ? 'error' : 'success'}`,
                    style: {
                        padding: '12px 15px',
                        margin: '0 0 20px 0',
                        borderRadius: '6px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between'
                    }
                },
                createElement('span', null, notification.message),
                createElement(
                    'button',
                    {
                        onClick: () => setNotification(null),
                        style: {
                            background: 'none',
                            border: 'none',
                            fontSize: '16px',
                            cursor: 'pointer',
                            padding: '0 5px'
                        }
                    },
                    '×'
                )
            ),

            showResetModal && createElement(
                'div',
                { className: 'atlaspress-modal-overlay' },
                createElement(
                    'div',
                    { className: 'atlaspress-modal', style: { maxWidth: '500px' } },
                    createElement('h3', { style: { margin: '0 0 15px 0' } }, 'Reset AtlasPress Setup'),
                    createElement('p', { style: { marginBottom: '20px', color: 'var(--gray-600)' } }, 'This will delete all content types and entries, then redirect you to the setup wizard. This action cannot be undone.'),
                    createElement(
                        'div',
                        { style: { display: 'flex', gap: '12px', justifyContent: 'flex-end' } },
                        createElement(
                            'button',
                            {
                                className: 'button button-secondary',
                                onClick: () => setShowResetModal(false)
                            },
                            'Cancel'
                        ),
                        createElement(
                            'button',
                            {
                                className: 'button button-primary',
                                onClick: handleReset,
                                style: { background: 'var(--error)', borderColor: 'var(--error)' }
                            },
                            'Reset Setup'
                        )
                    )
                )
            ),

            createElement(
                'div',
                { className: 'dashboard-header' },
                createElement('h1', { style: { margin: 0, fontSize: '28px', fontWeight: '700' } }, 'AtlasPress Dashboard'),
                createElement(
                    'div',
                    { style: { display: 'flex', gap: '12px' } },
                    createElement(
                        'button',
                        {
                            className: 'button button-secondary',
                            onClick: () => {
                                if (atlaspress_ajax && atlaspress_ajax.admin_url) {
                                    window.location.href = atlaspress_ajax.admin_url + 'admin.php?page=atlaspress-setup&reconfigure=1';
                                } else {
                                    showNotification('Configuration error. Please refresh the page.', 'error');
                                }
                            },
                            style: {
                                background: 'var(--gray-100)',
                                borderColor: 'var(--gray-300)',
                                color: 'var(--gray-700)',
                                fontWeight: '500'
                            }
                        },
                        'Reconfigure'
                    ),
                    createElement(
                        'button',
                        {
                            className: 'button',
                            onClick: () => setShowResetModal(true),
                            style: {
                                background: '#fef2f2',
                                borderColor: '#fecaca',
                                color: 'var(--error)',
                                fontWeight: '500'
                            }
                        },
                        'Reset Setup'
                    )
                )
            ),

            createElement(
                'div',
                { className: 'dashboard-content' },
                createElement(
                    'div',
                    { className: 'dashboard-stats' },
                    createElement(StatCard, { label: 'Content Types', value: stats.contentTypes, color: '#667f6a' }),
                    createElement(StatCard, { label: 'Fields', value: stats.fields, color: '#9CA3AF' }),
                    createElement(StatCard, { label: 'Entries', value: stats.entries, color: '#667f6a' }),
                    createElement(StatCard, { label: 'API Status', value: stats.apiStatus, color: '#9CA3AF' })
                ),

                createElement(
                    'div',
                    { className: 'dashboard-widgets' },
                    createElement(RecentEntriesWidget, { entries: stats.recentEntries || [] }),
                    createElement(TopTypesWidget, { types: stats.topContentTypes || [] })
                )
            )
        ), createElement(LiveUpdates, { onNotificationChange: setNotificationCount, onEntriesChange: setNewEntries }))
};

const ContentTypesApp = () => {
    const [contentTypes, setContentTypes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [newTypeName, setNewTypeName] = useState('');
    const [error, setError] = useState('');
    const [editingSchema, setEditingSchema] = useState(null);
    const [notificationCount, setNotificationCount] = useState(0);
    const [newEntries, setNewEntries] = useState([]);
    const [notification, setNotification] = useState(null);

    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), 4000);
    };

    // Free version limit
    const FREE_VERSION_LIMIT = 2;
    const isLimitReached = contentTypes.length >= FREE_VERSION_LIMIT;

    const loadContentTypes = () => {
        setLoading(true);
        setError('');
        apiFetch({ path: '/atlaspress/v1/content-types' })
            .then(response => {
                setContentTypes(response.data || []);
                setLoading(false);
            })
            .catch(err => {
                console.error('Content types error:', err);
                if (err.code === 'table_missing') {
                    setError('Database tables not found. Please run the setup wizard first.');
                } else {
                    setError('Failed to load content types: ' + (err.message || 'Unknown error'));
                }
                setLoading(false);
            });
    };

    const createContentType = () => {
        if (!newTypeName.trim()) {
            setError('Name is required');
            return;
        }

        // Check free version limit
        if (isLimitReached) {
            setError(`Free version limited to ${FREE_VERSION_LIMIT} content types. Upgrade to Pro for unlimited content types.`);
            return;
        }

        apiFetch({
            path: '/atlaspress/v1/content-types',
            method: 'POST',
            data: { name: newTypeName }
        }).then(() => {
            setNewTypeName('');
            setShowForm(false);
            setError('');
            loadContentTypes();
        }).catch(err => {
            setError(err.message || 'Failed to create content type');
        });
    };

    const deleteContentType = (id) => {
        if (!confirm('Are you sure you want to delete this content type?')) return;

        apiFetch({
            path: `/atlaspress/v1/content-types/${id}`,
            method: 'DELETE'
        }).then(() => {
            loadContentTypes();
        }).catch(err => {
            setError(err.message || 'Failed to delete content type');
        });
    };

    useEffect(() => {
        loadContentTypes();
    }, []);

    if (loading) return createElement(LoadingSpinner);

    return createElement(
        'div',
        null,
        createElement(Header, { activePage: 'content-types', notificationCount, newEntries }),
        createElement(LiveUpdates, { onNotificationChange: setNotificationCount, onEntriesChange: setNewEntries }),
        createElement(
            'div',
            { style: { display: 'grid', gap: '20px', padding: '20px' } },

            notification && createElement(
                'div',
                {
                    className: `notice notice-${notification.type}`,
                    style: { padding: '12px 16px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }
                },
                createElement('span', null, notification.message),
                createElement('button', {
                    onClick: () => setNotification(null),
                    style: { background: 'none', border: 'none', cursor: 'pointer', fontSize: '18px' }
                }, '×')
            ),

            editingSchema && createElement(SchemaBuilder, {
                contentTypeId: editingSchema,
                allContentTypes: contentTypes,
                onClose: () => {
                    setEditingSchema(null);
                    loadContentTypes();
                }
            }),

            error && createElement(
                'div',
                { className: 'notice notice-error', style: { padding: '10px' } },
                createElement('p', null, error)
            ),

            createElement(
                'div',
                { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' } },
                createElement('h2', { style: { margin: 0 } }, 'Content Types'),
                createElement(
                    'div',
                    { style: { display: 'flex', alignItems: 'center', gap: '12px' } },
                    createElement(
                        'span',
                        { className: `limit-badge ${isLimitReached ? 'limit-reached' : ''}` },
                        createElement('span', { className: 'count' }, contentTypes.length),
                        '/',
                        FREE_VERSION_LIMIT,
                        ' types',
                        isLimitReached && ' (limit)'
                    ),
                    createElement(
                        'button',
                        {
                            className: 'button button-primary',
                            onClick: () => isLimitReached ? null : setShowForm(!showForm),
                            disabled: isLimitReached,
                            style: isLimitReached ? { opacity: 0.6, cursor: 'not-allowed' } : {}
                        },
                        showForm ? 'Cancel' : 'Add New'
                    )
                )
            ),

            isLimitReached && createElement(
                'div',
                { className: 'upgrade-notice' },
                createElement(
                    'div',
                    { className: 'upgrade-notice-content' },
                    createElement('span', { className: 'upgrade-notice-icon' }, '🔒'),
                    createElement(
                        'div',
                        { className: 'upgrade-notice-text' },
                        createElement('h4', null, 'Free Version Limit Reached'),
                        createElement('p', null, `You've reached the limit of ${FREE_VERSION_LIMIT} content types. Delete existing content types to create new ones, or upgrade to Pro!`)
                    )
                ),
                createElement(
                    'div',
                    { style: { display: 'flex', gap: '10px' } },
                    createElement(
                        'button',
                        {
                            className: 'button button-link-delete',
                            onClick: async () => {
                                if (!confirm(`This will delete all ${contentTypes.length} content types and their entries. Are you sure?`)) return;
                                
                                try {
                                    const ids = contentTypes.map(t => t.id);
                                    await apiFetch({
                                        path: '/atlaspress/v1/content-types/bulk-delete',
                                        method: 'POST',
                                        data: { ids }
                                    });
                                    loadContentTypes();
                                    showNotification('All content types deleted. You can now create new ones!', 'success');
                                } catch (error) {
                                    showNotification('Failed to delete content types: ' + error.message, 'error');
                                }
                            },
                            style: { fontSize: '13px', padding: '8px 16px' }
                        },
                        'Delete All & Start Fresh'
                    ),
                    createElement(
                        'button',
                        { className: 'button-pro' },
                        'Upgrade to Pro (Coming Soon)'
                    )
                )
            ),

            showForm && !isLimitReached && createElement(
                'div',
                {
                    className: 'postbox',
                    style: { padding: '20px' }
                },
                createElement('h3', null, 'Create New Content Type'),
                createElement('input', {
                    type: 'text',
                    placeholder: 'Content Type Name',
                    value: newTypeName,
                    onChange: (e) => setNewTypeName(e.target.value),
                    style: { width: '300px', marginRight: '10px' },
                    onKeyPress: (e) => e.key === 'Enter' && createContentType()
                }),
                createElement(
                    'button',
                    {
                        className: 'button button-primary',
                        onClick: createContentType
                    },
                    'Create'
                )
            ),

            createElement(
                'div',
                { style: { display: 'grid', gap: '15px' } },
                contentTypes.length === 0
                    ? createElement(
                        'div',
                        { className: 'postbox', style: { padding: '40px', textAlign: 'center' } },
                        createElement('p', { style: { fontSize: '16px', color: '#666' } }, 'No content types found.'),
                        createElement('p', null, 'Create your first content type to get started!')
                    )
                    : contentTypes.map(type =>
                        createElement(
                            'div',
                            {
                                key: type.id,
                                className: 'postbox',
                                style: {
                                    padding: '20px',
                                    display: 'flex',
                                    justifyContent: 'space-between',
                                    alignItems: 'center'
                                }
                            },
                            createElement(
                                'div',
                                null,
                                createElement('h3', { style: { margin: '0 0 5px 0' } }, type.name),
                                createElement('p', { style: { margin: 0, color: '#666' } }, `Slug: ${type.slug}`),
                                createElement('small', { style: { color: '#999' } }, `Fields: ${type.settings?.fields?.length || 0}`)
                            ),
                            createElement(
                                'div',
                                { style: { display: 'flex', gap: '10px' } },
                                createElement(
                                    'button',
                                    {
                                        className: 'button',
                                        onClick: () => setEditingSchema(type.id)
                                    },
                                    'Edit Schema'
                                ),
                                createElement(
                                    'button',
                                    {
                                        className: 'button button-link-delete',
                                        onClick: () => deleteContentType(type.id)
                                    },
                                    'Delete'
                                )
                            )
                        )
                    )
            )
        )
    );
};

const EntryForm = ({ contentTypeId, entryId = null, onClose }) => {
    const [contentType, setContentType] = useState(null);
    const [formData, setFormData] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        const loadData = async () => {
            try {
                const type = await apiFetch({ path: `/atlaspress/v1/content-types/${contentTypeId}` });
                setContentType(type);

                if (entryId) {
                    const entry = await apiFetch({ path: `/atlaspress/v1/entries/${entryId}` });
                    setFormData({ title: entry.title, ...entry.data });
                } else {
                    // Initialize with default values
                    const defaults = { title: '' };
                    (type.settings?.fields || []).forEach(field => {
                        defaults[field.name] = field.defaultValue || '';
                    });
                    setFormData(defaults);
                }
                setLoading(false);
            } catch (error) {
                setLoading(false);
            }
        };
        loadData();
    }, [contentTypeId, entryId]);

    const handleSubmit = async () => {
        const newErrors = {};

        // Validate required fields
        if (!formData.title?.trim()) {
            newErrors.title = 'Title is required';
        }

        (contentType.settings?.fields || []).forEach(field => {
            if (field.required && !formData[field.name]) {
                newErrors[field.name] = `${field.label} is required`;
            }
        });

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setSaving(true);
        try {
            const { title, ...data } = formData;
            const payload = { title, data };

            if (entryId) {
                await apiFetch({
                    path: `/atlaspress/v1/entries/${entryId}`,
                    method: 'PUT',
                    data: payload
                });
            } else {
                await apiFetch({
                    path: `/atlaspress/v1/content-types/${contentTypeId}/entries`,
                    method: 'POST',
                    data: payload
                });
            }
            onClose();
        } catch (error) {
            setSaving(false);
        }
    };

    const renderField = (field) => {
        const value = formData[field.name] || '';
        const hasError = errors[field.name];

        const fieldStyle = {
            width: '100%',
            padding: '8px',
            border: hasError ? '1px solid #d63638' : '1px solid #ddd',
            borderRadius: '4px'
        };

        switch (field.type) {
            case 'textarea':
                return createElement('textarea', {
                    value,
                    onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                    placeholder: field.placeholder,
                    rows: field.rows || 4,
                    style: fieldStyle
                });

            case 'richtext':
                return createElement('textarea', {
                    value,
                    onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                    rows: field.height || 6,
                    style: { ...fieldStyle, fontFamily: 'monospace' },
                    placeholder: 'Rich text content (HTML supported)'
                });

            case 'number':
            case 'range':
                return createElement('input', {
                    type: field.type,
                    value,
                    onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                    placeholder: field.placeholder,
                    min: field.min,
                    max: field.max,
                    step: field.step,
                    style: fieldStyle
                });

            case 'email':
            case 'url':
            case 'tel':
            case 'password':
            case 'color':
            case 'hidden':
                return createElement('input', {
                    type: field.type,
                    value,
                    onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                    placeholder: field.placeholder,
                    style: field.type === 'hidden' ? { display: 'none' } : fieldStyle
                });

            case 'date':
            case 'time':
            case 'datetime-local':
                return createElement('input', {
                    type: field.type === 'datetime' ? 'datetime-local' : field.type,
                    value,
                    onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                    style: fieldStyle
                });

            case 'select':
                return createElement(
                    'select',
                    {
                        value,
                        onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                        multiple: field.multiple,
                        style: fieldStyle
                    },
                    createElement('option', { value: '' }, 'Select...'),
                    (field.options || []).map(option =>
                        createElement('option', { key: option, value: option }, option)
                    )
                );

            case 'radio':
                return createElement(
                    'div',
                    { style: { display: 'flex', flexDirection: 'column', gap: '8px' } },
                    (field.options || []).map(option =>
                        createElement(
                            'label',
                            {
                                key: option,
                                style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }
                            },
                            createElement('input', {
                                type: 'radio',
                                name: field.name,
                                value: option,
                                checked: value === option,
                                onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value })
                            }),
                            createElement('span', null, option)
                        )
                    )
                );

            case 'checkbox':
                return createElement(
                    'label',
                    { style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' } },
                    createElement('input', {
                        type: 'checkbox',
                        checked: !!value,
                        onChange: (e) => setFormData({ ...formData, [field.name]: e.target.checked })
                    }),
                    createElement('span', null, field.label || 'Check this option')
                );

            case 'checkboxes':
                const selectedValues = Array.isArray(value) ? value : [];
                return createElement(
                    'div',
                    { style: { display: 'flex', flexDirection: 'column', gap: '8px' } },
                    (field.options || []).map(option =>
                        createElement(
                            'label',
                            {
                                key: option,
                                style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }
                            },
                            createElement('input', {
                                type: 'checkbox',
                                value: option,
                                checked: selectedValues.includes(option),
                                onChange: (e) => {
                                    const newValues = e.target.checked
                                        ? [...selectedValues, option]
                                        : selectedValues.filter(v => v !== option);
                                    setFormData({ ...formData, [field.name]: newValues });
                                }
                            }),
                            createElement('span', null, option)
                        )
                    )
                );

            case 'file':
                return createElement(
                    'div',
                    null,
                    createElement('input', {
                        type: 'file',
                        multiple: field.multiple,
                        accept: field.allowedTypes ? field.allowedTypes.map(t => `.${t}`).join(',') : undefined,
                        onChange: async (e) => {
                            const files = Array.from(e.target.files);
                            if (files.length === 0) return;

                            const uploadedFiles = [];
                            for (const file of files) {
                                const formData = new FormData();
                                formData.append('file', file);

                                try {
                                    const response = await apiFetch({
                                        path: '/atlaspress/v1/upload',
                                        method: 'POST',
                                        body: formData,
                                        headers: {}
                                    });
                                    uploadedFiles.push(response);
                                } catch (error) {
                                    console.error('Upload failed:', error);
                                }
                            }

                            setFormData({
                                ...formData,
                                [field.name]: field.multiple ? uploadedFiles : uploadedFiles[0]
                            });
                        },
                        style: fieldStyle
                    }),
                    value && createElement('small', { style: { display: 'block', marginTop: '5px', color: '#666' } },
                        Array.isArray(value)
                            ? `${value.length} file(s) uploaded`
                            : (value.filename || 'File uploaded')
                    )
                );

            case 'relationship':
                return createElement(RelationshipField, {
                    field,
                    value,
                    onChange: (newValue) => setFormData({ ...formData, [field.name]: newValue }),
                    style: fieldStyle
                });

            case 'json':
                return createElement('textarea', {
                    value: typeof value === 'object' ? JSON.stringify(value, null, 2) : value,
                    onChange: (e) => {
                        try {
                            const parsed = JSON.parse(e.target.value);
                            setFormData({ ...formData, [field.name]: parsed });
                        } catch {
                            setFormData({ ...formData, [field.name]: e.target.value });
                        }
                    },
                    rows: 6,
                    style: { ...fieldStyle, fontFamily: 'monospace' },
                    placeholder: '{"key": "value"}'
                });

            default:
                return createElement('input', {
                    type: 'text',
                    value,
                    onChange: (e) => setFormData({ ...formData, [field.name]: e.target.value }),
                    placeholder: field.placeholder,
                    style: fieldStyle
                });
        }
    };

    if (loading) return createElement(LoadingSpinner);
    if (!contentType) return createElement('div', null, 'Content type not found');

    return createElement(
        'div',
        { style: { position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, background: 'rgba(0,0,0,0.7)', zIndex: 9999 } },
        createElement(
            'div',
            {
                style: {
                    position: 'absolute',
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%, -50%)',
                    background: '#fff',
                    padding: '30px',
                    borderRadius: '8px',
                    width: '80%',
                    maxWidth: '600px',
                    maxHeight: '80vh',
                    overflow: 'auto'
                }
            },
            createElement(
                'div',
                { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' } },
                createElement('h2', { style: { margin: 0 } }, `${entryId ? 'Edit' : 'Create'} ${contentType.name}`),
                createElement('button', { className: 'button', onClick: onClose }, 'Close')
            ),

            createElement(
                'div',
                { style: { marginBottom: '20px' } },
                createElement(
                    'div',
                    { style: { marginBottom: '15px' } },
                    createElement('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Title *'),
                    createElement('input', {
                        type: 'text',
                        value: formData.title || '',
                        onChange: (e) => setFormData({ ...formData, title: e.target.value }),
                        style: {
                            width: '100%',
                            padding: '8px',
                            border: errors.title ? '1px solid #d63638' : '1px solid #ddd',
                            borderRadius: '4px'
                        }
                    }),
                    errors.title && createElement('div', { style: { color: '#d63638', fontSize: '12px', marginTop: '5px' } }, errors.title)
                ),

                (contentType.settings?.fields || []).map(field =>
                    createElement(
                        'div',
                        { key: field.name, style: { marginBottom: '15px' } },
                        createElement(
                            'label',
                            { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } },
                            field.label,
                            field.required && createElement('span', { style: { color: '#d63638' } }, ' *')
                        ),
                        renderField(field),
                        errors[field.name] && createElement('div', { style: { color: '#d63638', fontSize: '12px', marginTop: '5px' } }, errors[field.name])
                    )
                )
            ),

            createElement(
                'div',
                { style: { textAlign: 'right' } },
                createElement(
                    'button',
                    {
                        className: 'button button-primary button-large',
                        onClick: handleSubmit,
                        disabled: saving
                    },
                    saving ? 'Saving...' : (entryId ? 'Update' : 'Create')
                )
            )
        )
    );
};

const EntriesApp = () => {
    const [entries, setEntries] = useState([]);
    const [contentTypes, setContentTypes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedType, setSelectedType] = useState('');
    const [selectedEntries, setSelectedEntries] = useState([]);
    const [pagination, setPagination] = useState({ page: 1, per_page: 20, total: 0, total_pages: 0 });
    const [notification, setNotification] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [notificationCount, setNotificationCount] = useState(0);
    const [newEntries, setNewEntries] = useState([]);
    const [editingEntry, setEditingEntry] = useState(null);

    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
        setTimeout(() => setNotification(null), 3000);
    };

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const typeParam = params.get('type');
        const highlightParam = params.get('highlight');
        const editParam = params.get('edit');

        if (typeParam) {
            setSelectedType(typeParam);
        }

        if (editParam) {
            setEditingEntry(editParam);
        }

        apiFetch({ path: '/atlaspress/v1/content-types' })
            .then(response => {
                setContentTypes(response.data || []);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    const loadEntries = (typeId, page = 1) => {
        if (!typeId) return;
        const params = new URLSearchParams({
            page,
            per_page: 20
        });
        if (searchTerm) params.append('search', searchTerm);
        if (statusFilter) params.append('status', statusFilter);

        apiFetch({ path: `/atlaspress/v1/content-types/${typeId}/entries?${params}` })
            .then(response => {
                setEntries(response.data || []);
                setPagination(response.pagination || { page: 1, per_page: 20, total: 0, total_pages: 0 });
            });
    };

    const bulkDeleteEntries = () => {
        if (selectedEntries.length === 0) return;
        if (!confirm(`Delete ${selectedEntries.length} entries?`)) return;

        apiFetch({
            path: '/atlaspress/v1/entries/bulk-delete',
            method: 'POST',
            data: { ids: selectedEntries }
        }).then(() => {
            loadEntries(selectedType, pagination.page);
            setSelectedEntries([]);
            showNotification(`Deleted ${selectedEntries.length} entries`);
        }).catch(() => {
            showNotification('Failed to delete entries', 'error');
        });
    };

    const bulkUpdateStatus = (status) => {
        if (selectedEntries.length === 0) return;
        if (!confirm(`Update ${selectedEntries.length} entries to ${status}?`)) return;

        apiFetch({
            path: '/atlaspress/v1/entries/bulk-update',
            method: 'POST',
            data: { ids: selectedEntries, status }
        }).then(() => {
            loadEntries(selectedType, pagination.page);
            setSelectedEntries([]);
            showNotification(`Updated ${selectedEntries.length} entries`);
        }).catch(() => {
            showNotification('Failed to update entries', 'error');
        });
    };

    const deleteEntry = (id) => {
        if (!confirm('Are you sure you want to delete this entry?')) return;

        apiFetch({
            path: `/atlaspress/v1/entries/${id}`,
            method: 'DELETE'
        }).then(() => {
            loadEntries(selectedType);
            showNotification('Entry deleted successfully');
        }).catch(() => {
            showNotification('Failed to delete entry', 'error');
        });
    };

    const exportEntries = () => {
        if (!selectedType) return;

        const selectedTypeName = contentTypes.find(t => t.id == selectedType)?.name || 'entries';
        const csvContent = 'data:text/csv;charset=utf-8,' +
            'Title,Created Date,Data\n' +
            entries.map(entry =>
                `"${entry.title}","${new Date(entry.created_at).toLocaleDateString()}","${JSON.stringify(entry.data).replace(/"/g, '""')}"`
            ).join('\n');

        const link = document.createElement('a');
        link.setAttribute('href', encodeURI(csvContent));
        link.setAttribute('download', `${selectedTypeName.toLowerCase().replace(/\s+/g, '-')}-entries.csv`);
        link.click();

        showNotification('Entries exported successfully');
    };

    useEffect(() => {
        if (selectedType) loadEntries(selectedType);

        const params = new URLSearchParams(window.location.search);
        const highlightParam = params.get('highlight');
        if (highlightParam) {
            setTimeout(() => {
                const row = document.querySelector(`tr[data-entry-id="${highlightParam}"]`);
                if (row) {
                    row.style.background = '#fff3cd';
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => { row.style.background = ''; }, 3000);
                }
            }, 500);
        }
    }, [selectedType, searchTerm, statusFilter]);

    if (loading) return createElement(LoadingSpinner);

    return createElement(
        'div',
        null,
        createElement(Header, { activePage: 'entries', notificationCount, newEntries }),
        createElement(LiveUpdates, { onNotificationChange: setNotificationCount, onEntriesChange: setNewEntries }),
        createElement(
            'div',
            { style: { display: 'grid', gap: '20px', padding: '20px' } },

            editingEntry && createElement(EntryForm, {
                contentTypeId: selectedType,
                entryId: editingEntry,
                onClose: () => {
                    setEditingEntry(null);
                    window.history.pushState({}, '', '?page=atlaspress-entries&type=' + selectedType);
                    loadEntries(selectedType);
                }
            }),

            notification && createElement(
                'div',
                {
                    className: `notice notice-${notification.type === 'error' ? 'error' : 'success'}`,
                    style: {
                        padding: '12px 15px',
                        margin: '0',
                        borderRadius: '6px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between'
                    }
                },
                createElement('span', null, notification.message),
                createElement(
                    'button',
                    {
                        onClick: () => setNotification(null),
                        style: {
                            background: 'none',
                            border: 'none',
                            fontSize: '16px',
                            cursor: 'pointer',
                            padding: '0 5px'
                        }
                    },
                    '×'
                )
            ),

            createElement(
                'div',
                { className: 'entries-header' },
                createElement(
                    'div',
                    { className: 'entries-header-left' },
                    createElement('h2', null, 'Entries'),
                    selectedType && createElement(
                        'span',
                        { style: { fontSize: '13px', color: 'var(--gray-500)', padding: '4px 10px', background: 'var(--gray-100)', borderRadius: '20px' } },
                        contentTypes.find(t => t.id == selectedType)?.name || ''
                    )
                ),
                createElement(
                    'div',
                    { className: 'entries-header-right' },
                    createElement(
                        'select',
                        {
                            value: selectedType,
                            onChange: (e) => setSelectedType(e.target.value),
                            className: 'entries-type-select'
                        },
                        createElement('option', { value: '' }, 'Select Content Type'),
                        contentTypes.map(type =>
                            createElement('option', { key: type.id, value: type.id }, type.name)
                        )
                    ),
                    selectedType && createElement(
                        'div',
                        { className: 'entries-actions' },
                        createElement('input', {
                            type: 'file',
                            id: 'import-file',
                            accept: '.csv,.json',
                            style: { display: 'none' },
                            onChange: async (e) => {
                                const file = e.target.files[0];
                                if (!file) return;

                                const formData = new FormData();
                                formData.append('file', file);

                                try {
                                    const response = await fetch(`/wp-json/atlaspress/v1/import/entries/${selectedType}`, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-WP-Nonce': wpApiSettings.nonce }
                                    });
                                    const result = await response.json();
                                    if (response.ok) {
                                        showNotification(result.message);
                                        loadEntries(selectedType);
                                    } else {
                                        showNotification(result.message || 'Import failed', 'error');
                                    }
                                } catch (error) {
                                    showNotification('Import failed: ' + error.message, 'error');
                                }
                                e.target.value = '';
                            }
                        }),
                        createElement(
                            'button',
                            {
                                className: 'button button-import',
                                onClick: () => document.getElementById('import-file').click()
                            },
                            'Import'
                        ),
                        entries.length > 0 && createElement(
                            'button',
                            {
                                className: 'button button-export',
                                onClick: () => window.open(`/wp-json/atlaspress/v1/export/csv/${selectedType}`, '_blank')
                            },
                            'CSV'
                        ),
                        entries.length > 0 && createElement(
                            'button',
                            {
                                className: 'button button-export',
                                onClick: () => window.open(`/wp-json/atlaspress/v1/export/json/${selectedType}`, '_blank')
                            },
                            'JSON'
                        ),
                        entries.length > 0 && createElement(
                            'button',
                            {
                                className: 'button button-export',
                                onClick: () => window.open(`/wp-json/atlaspress/v1/export/xml/${selectedType}`, '_blank')
                            },
                            'XML'
                        )
                    )
                )
            ),

            selectedType && createElement(
                'div',
                { className: 'entries-search' },
                createElement('input', {
                    type: 'text',
                    placeholder: 'Search entries...',
                    value: searchTerm,
                    onChange: (e) => setSearchTerm(e.target.value)
                }),
                createElement(
                    'select',
                    {
                        value: statusFilter,
                        onChange: (e) => setStatusFilter(e.target.value),
                        style: { padding: '8px' }
                    },
                    createElement('option', { value: '' }, 'All Status'),
                    createElement('option', { value: 'published' }, 'Published'),
                    createElement('option', { value: 'draft' }, 'Draft'),
                    createElement('option', { value: 'pending' }, 'Pending')
                ),
                (searchTerm || statusFilter) && createElement(
                    'button',
                    {
                        className: 'button',
                        onClick: () => {
                            setSearchTerm('');
                            setStatusFilter('');
                        }
                    },
                    'Clear Filters'
                )
            ),

            selectedType ? createElement(
                'div',
                null,
                entries.length === 0
                    ? createElement(
                        'div',
                        { className: 'entries-empty-state' },
                        createElement('h3', null, 'No submissions yet'),
                        createElement('p', null, 'Form submissions will appear here when users submit forms from your frontend.'),
                        createElement(
                            'div',
                            { style: { marginTop: '20px', padding: '15px', background: 'var(--gray-50)', borderRadius: '8px', textAlign: 'left' } },
                            createElement('h4', { style: { margin: '0 0 10px 0', fontSize: '14px', color: 'var(--gray-700)' } }, 'Integration Code:'),
                            createElement('pre', {
                                style: {
                                    background: '#fff',
                                    padding: '12px',
                                    borderRadius: '6px',
                                    fontSize: '12px',
                                    overflow: 'auto',
                                    border: '1px solid var(--gray-200)'
                                }
                            }, `// Submit to this form type
fetch('/wp-json/atlaspress/v1/content-types/${selectedType}/entries', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    title: 'Form Submission',
    data: { name: 'John', email: 'john@example.com' }
  })
});`)
                        )
                    )
                    : createElement(
                        'div',
                        null,
                        createElement(
                            'div',
                            { className: 'entries-info-bar' },
                            createElement(
                                'div',
                                { className: 'entries-count' },
                                createElement('span', null, `${entries.length}`),
                                ' submissions',
                                createElement('span', { className: 'entries-latest', style: { marginLeft: '15px' } },
                                    `Latest: ${entries[0] ? new Date(entries[0].created_at).toLocaleDateString() : 'N/A'}`
                                )
                            ),
                            selectedEntries.length > 0 && createElement(
                                'div',
                                { className: 'entries-bulk-actions' },
                                createElement(
                                    'button',
                                    {
                                        className: 'button button-export',
                                        onClick: () => bulkUpdateStatus('published')
                                    },
                                    'Publish'
                                ),
                                createElement(
                                    'button',
                                    {
                                        className: 'button button-import',
                                        onClick: () => bulkUpdateStatus('draft')
                                    },
                                    'Draft'
                                ),
                                createElement(
                                    'button',
                                    {
                                        className: 'button button-link-delete',
                                        onClick: bulkDeleteEntries
                                    },
                                    `Delete (${selectedEntries.length})`
                                )
                            )
                        ),
                        createElement(
                            'table',
                            { className: 'wp-list-table widefat fixed striped' },
                            createElement(
                                'thead',
                                null,
                                createElement(
                                    'tr',
                                    null,
                                    createElement(
                                        'td',
                                        { className: 'check-column' },
                                        createElement('input', {
                                            type: 'checkbox',
                                            onChange: (e) => setSelectedEntries(e.target.checked ? entries.map(entry => entry.id) : [])
                                        })
                                    ),
                                    createElement('th', null, 'ID'),
                                    createElement('th', null, 'Title'),
                                    createElement('th', null, 'Data'),
                                    createElement('th', null, 'Date'),
                                    createElement('th', null, 'Actions')
                                )
                            ),
                            createElement(
                                'tbody',
                                null,
                                entries.map(entry =>
                                    createElement(
                                        'tr',
                                        { key: entry.id, 'data-entry-id': entry.id },
                                        createElement(
                                            'th',
                                            { className: 'check-column' },
                                            createElement('input', {
                                                type: 'checkbox',
                                                checked: selectedEntries.includes(entry.id),
                                                onChange: (e) => {
                                                    setSelectedEntries(e.target.checked
                                                        ? [...selectedEntries, entry.id]
                                                        : selectedEntries.filter(id => id !== entry.id)
                                                    );
                                                }
                                            })
                                        ),
                                        createElement('td', null, entry.id),
                                        createElement('td', { style: { fontWeight: '600' } }, entry.title || 'Untitled'),
                                        createElement(
                                            'td',
                                            { style: { fontSize: '12px', maxWidth: '300px' } },
                                            Object.entries(entry.data || {}).slice(0, 3).map(([key, value]) =>
                                                createElement('div', { key },
                                                    createElement('strong', null, key + ': '),
                                                    String(value).substring(0, 40) + (String(value).length > 40 ? '...' : '')
                                                )
                                            )
                                        ),
                                        createElement('td', null, new Date(entry.created_at).toLocaleString()),
                                        createElement(
                                            'td',
                                            null,
                                            createElement(
                                                'div',
                                                { style: { display: 'flex', gap: '5px' } },
                                                createElement(
                                                    'button',
                                                    {
                                                        className: 'button button-small',
                                                        onClick: () => {
                                                            window.location.href = `?page=atlaspress-entries&type=${selectedType}&edit=${entry.id}`;
                                                        },
                                                        title: 'Edit'
                                                    },
                                                    'Edit'
                                                ),
                                                createElement(
                                                    'button',
                                                    {
                                                        className: 'button button-small button-link-delete',
                                                        onClick: () => deleteEntry(entry.id)
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
            ) : createElement(
                'div',
                { className: 'postbox', style: { padding: '40px', textAlign: 'center' } },
                createElement('h3', { style: { color: '#666' } }, 'Select a form type to view submissions'),
                createElement('p', null, 'Choose a content type from the dropdown above to see form submissions from your frontend applications.')
            )
        )
    );
};

document.addEventListener('DOMContentLoaded', () => {
    const securityRoot = document.getElementById('atlaspress-security-app');
    if (securityRoot) {
        const root = createRoot(securityRoot);
        root.render(createElement(SecurityApp));
        return;
    }

    const setupRoot = document.getElementById('atlaspress-setup-wizard');
    if (setupRoot) {
        const root = createRoot(setupRoot);
        root.render(createElement(SetupWizard));
        return;
    }

    const dashboardRoot = document.getElementById('atlaspress-admin-app');
    if (dashboardRoot) {
        const root = createRoot(dashboardRoot);
        root.render(createElement(
            'div',
            null,
            createElement(DashboardApp)
        ));
    }

    const contentTypesRoot = document.getElementById('atlaspress-content-types-app');
    if (contentTypesRoot) {
        const root = createRoot(contentTypesRoot);
        root.render(createElement(ContentTypesApp));
    }

    const entriesRoot = document.getElementById('atlaspress-entries-app');
    if (entriesRoot) {
        const root = createRoot(entriesRoot);
        root.render(createElement(EntriesApp));
    }
});












window.atlaspressHeader = Header;
