# AtlasPress User Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [Creating Content Types](#creating-content-types)
3. [Managing Entries](#managing-entries)
4. [API Integration](#api-integration)
5. [Security Settings](#security-settings)
6. [Tips & Tricks](#tips-tricks)

---

## Getting Started

### Installation
1. Install and activate AtlasPress plugin
2. Navigate to **AtlasPress** in WordPress admin
3. Complete the Setup Wizard

### Setup Wizard
Choose your project type:
- **NextJS/React Forms**: Pre-configured for form tracking
- **Headless CMS**: Blog posts and content management
- **API Backend**: Structured data storage
- **Blank Project**: Start from scratch

---

## Creating Content Types

### Step 1: Add Content Type
1. Go to **AtlasPress > Content Types**
2. Click **"Add New"**
3. Enter name (e.g., "Contact Form")
4. Click **"Create"**

### Step 2: Build Schema
1. Click **"Edit Schema"** on your content type
2. Click **"Add Field"**
3. Configure field:
   - **Field Name**: Internal name (e.g., `email`)
   - **Field Label**: Display name (e.g., "Email Address")
   - **Field Type**: Choose from 20+ types
   - **Required**: Check if mandatory

### Step 3: Add Options (for Select/Radio/Checkboxes)
- Enter options separated by commas
- Example: `Small, Medium, Large, Extra Large`

### Step 4: Save Schema
Click **"Save Schema"** when done

---

## Managing Entries

### View Entries
1. Go to **AtlasPress > Entries**
2. Select content type from dropdown
3. View submissions in table format

### Search & Filter
- **Search Box**: Search by title or content
- **Status Filter**: Filter by Published/Draft/Pending
- **Clear Filters**: Reset all filters

### Bulk Operations
1. Select entries using checkboxes
2. Choose action:
   - **Mark as Published**: Publish selected entries
   - **Mark as Draft**: Set to draft status
   - **Delete**: Remove selected entries

### Individual Actions
- **📋 Duplicate**: Clone an entry
- **Delete**: Remove single entry

### Export Data
Click export buttons to download:
- **Export CSV**: Spreadsheet format
- **Export JSON**: Structured data
- **Export XML**: Universal format

---

## API Integration

### Basic Form Submission

```javascript
// Submit form data
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    title: 'Form Submission',
    data: {
      name: 'John Doe',
      email: 'john@example.com',
      message: 'Hello!'
    }
  })
});

const result = await response.json();
console.log(result); // { message: "Created", id: 123 }
```

### Fetch Entries

```javascript
// Get all entries
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries');
const { data: entries } = await response.json();

// With pagination
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries?page=1&per_page=20');

// With search
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries?search=john');

// With status filter
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries?status=published');
```

### GraphQL Queries

```javascript
const query = `{
  entries(contentTypeId: 1, limit: 10) {
    id
    title
    created_at
    data
  }
}`;

const response = await fetch('/wp-json/atlaspress/v1/graphql', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ query })
});

const result = await response.json();
console.log(result.data.entries);
```

### File Upload

```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);

const response = await fetch('/wp-json/atlaspress/v1/upload', {
  method: 'POST',
  body: formData
});

const file = await response.json();
console.log(file.url); // File URL
```

---

## Security Settings

### Generate API Key
1. Go to **AtlasPress > Security**
2. Enter key name (e.g., "Production API")
3. Click **"Generate API Key"**
4. Copy the key (shown once only!)
5. Use in requests:
   ```javascript
   headers: {
     'X-API-Key': 'your-api-key-here'
   }
   ```

### Configure CORS
1. Go to **AtlasPress > Security**
2. Scroll to **CORS Settings**
3. Add allowed origins (one per line):
   ```
   https://example.com
   https://app.example.com
   ```
4. Click **"Save CORS Settings"**
5. Leave empty to allow all origins (dev only)

---

## Tips & Tricks

### Best Practices
✅ Use descriptive field names (e.g., `customer_email` not `email1`)
✅ Set required fields for critical data
✅ Use relationships to link related content
✅ Export data regularly as backup
✅ Test API endpoints before production

### Performance Tips
⚡ Use pagination for large datasets
⚡ Add indexes to frequently searched fields
⚡ Cache API responses in your frontend
⚡ Use status filters to reduce data transfer

### Common Patterns

**Contact Form**
```
Fields: name (text), email (email), message (textarea), source (hidden)
```

**Blog Post**
```
Fields: title (text), content (richtext), featured_image (file), category (select), tags (text)
```

**Product**
```
Fields: name (text), price (number), description (richtext), images (file, multiple), in_stock (checkbox)
```

**User Profile**
```
Fields: username (text), bio (textarea), avatar (file), social_links (json)
```

### Keyboard Shortcuts
- **Ctrl/Cmd + S**: Save (in schema builder)
- **Esc**: Close modal
- **Tab**: Navigate fields

### Testing
Use the built-in test page:
1. Visit: `http://your-site.com/test-atlaspress.html`
2. Test all features:
   - Form submission
   - File upload
   - GraphQL queries
   - Relationships

---

## Need Help?

📚 **Full Documentation**: AtlasPress > Help
🧪 **Test Page**: `/test-atlaspress.html`
📖 **README**: Check plugin folder
🐛 **Troubleshooting**: See Help section

---

**AtlasPress v1.0.0** | Schema-Driven Backend for Modern WordPress
