(function() {
  if (!atlaspressProxy.enabled) return;

  // Intercept HubSpot forms
  window.addEventListener('message', function(event) {
    if (event.data.type === 'hsFormCallback' && event.data.eventName === 'onFormSubmit') {
      captureFormData('hubspot', event.data.id, event.data.data);
    }
  });

  // Intercept Typeform
  if (window.typeformEmbed) {
    const original = window.typeformEmbed.makeWidget;
    window.typeformEmbed.makeWidget = function(...args) {
      const widget = original.apply(this, args);
      widget.onSubmit = function(data) {
        captureFormData('typeform', args[1]?.formId || 'unknown', data);
      };
      return widget;
    };
  }

  // Intercept all form submissions
  document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Skip if already processed or is WordPress form
    if (form.dataset.atlaspressProcessed || form.action.includes('wp-admin')) return;
    
    // Detect form type
    let formType = 'generic';
    let formId = 'unknown';
    
    if (form.classList.contains('hs-form') || form.querySelector('[name*="hubspot"]')) {
      formType = 'hubspot';
      formId = form.querySelector('[name="formGuid"]')?.value || 'unknown';
    } else if (form.action.includes('typeform.com')) {
      formType = 'typeform';
    } else if (form.action.includes('formstack.com')) {
      formType = 'formstack';
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    captureFormData(formType, formId, data);
    form.dataset.atlaspressProcessed = 'true';
  }, true);

  function captureFormData(formType, formId, data) {
    fetch(atlaspressProxy.apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...data,
        _formType: formType,
        _formId: formId,
        _pageUrl: window.location.href
      })
    }).catch(err => console.error('AtlasPress capture failed:', err));
  }
})();
