/**
 * AtlasPress Universal Form Tracker
 * Automatically captures all form submissions on any website
 */
class AtlasPressClient {
    constructor(config) {
        this.baseUrl = config.baseUrl;
        this.contentTypeId = config.contentTypeId || 1;
        this.debug = config.debug || false;
        this.excludeSelectors = config.exclude || [];
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.attachFormListeners());
        } else {
            this.attachFormListeners();
        }
        
        // Watch for dynamically added forms
        this.observeNewForms();
    }
    
    attachFormListeners() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => this.attachToForm(form));
    }
    
    attachToForm(form) {
        // Skip if already attached or excluded
        if (form.hasAttribute('data-atlaspress-attached') || this.isExcluded(form)) {
            return;
        }
        
        form.setAttribute('data-atlaspress-attached', 'true');
        
        form.addEventListener('submit', async (e) => {
            await this.handleFormSubmit(e, form);
        });
        
        if (this.debug) {
            console.log('AtlasPress: Attached to form', form);
        }
    }
    
    isExcluded(form) {
        return this.excludeSelectors.some(selector => {
            return form.matches(selector) || form.closest(selector);
        });
    }
    
    async handleFormSubmit(event, form) {
        try {
            // Get form data
            const formData = new FormData(form);
            const data = {};
            
            // Convert FormData to object, handling multiple values
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    // Handle multiple values (checkboxes, multi-select)
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            // Generate title from form data
            const title = this.generateTitle(data, form);
            
            // Add metadata
            const payload = {
                title: title,
                data: {
                    ...data,
                    _meta: {
                        url: window.location.href,
                        referrer: document.referrer,
                        userAgent: navigator.userAgent,
                        timestamp: new Date().toISOString(),
                        formId: form.id || null,
                        formClass: form.className || null,
                        formAction: form.action || null,
                        formMethod: form.method || 'GET'
                    }
                }
            };
            
            // Submit to AtlasPress
            const response = await fetch(`${this.baseUrl}/wp-json/atlaspress/v1/content-types/${this.contentTypeId}/entries`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            
            if (this.debug) {
                if (response.ok) {
                    console.log('AtlasPress: Form submitted successfully', payload);
                } else {
                    console.error('AtlasPress: Submission failed', response.status);
                }
            }
            
        } catch (error) {
            if (this.debug) {
                console.error('AtlasPress: Error submitting form', error);
            }
        }
    }
    
    generateTitle(data, form) {
        // Try to generate meaningful title from form data
        const nameFields = ['name', 'full_name', 'fullname', 'first_name', 'firstname'];
        const emailFields = ['email', 'email_address', 'e_mail'];
        const subjectFields = ['subject', 'topic', 'inquiry_type'];
        
        let title = 'Form Submission';
        
        // Try to find name
        const name = nameFields.find(field => data[field]) ? data[nameFields.find(field => data[field])] : null;
        
        // Try to find subject
        const subject = subjectFields.find(field => data[field]) ? data[subjectFields.find(field => data[field])] : null;
        
        // Try to find email
        const email = emailFields.find(field => data[field]) ? data[emailFields.find(field => data[field])] : null;
        
        if (name && subject) {
            title = `${subject} - ${name}`;
        } else if (name) {
            title = `Contact Form - ${name}`;
        } else if (email) {
            title = `Form Submission - ${email}`;
        } else if (subject) {
            title = subject;
        } else if (form.id) {
            title = `Form: ${form.id}`;
        } else if (form.className) {
            title = `Form: ${form.className.split(' ')[0]}`;
        }
        
        return title;
    }
    
    observeNewForms() {
        // Watch for dynamically added forms (React, Vue, etc.)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is a form
                        if (node.tagName === 'FORM') {
                            this.attachToForm(node);
                        }
                        // Check for forms within the added node
                        const forms = node.querySelectorAll ? node.querySelectorAll('form') : [];
                        forms.forEach(form => this.attachToForm(form));
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Auto-initialize if config is provided
if (typeof window.atlasPressConfig !== 'undefined') {
    window.atlasPress = new AtlasPressClient(window.atlasPressConfig);
}