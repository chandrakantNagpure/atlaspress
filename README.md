# AtlasPress - Schema-Driven Backend for Modern WordPress

A powerful, schema-driven backend and middleware layer for modern WordPress applications. Perfect for headless CMS, form tracking, and API-first development.

## 🚀 Features

### Core Features
- **20+ Field Types** - Text, email, file uploads, relationships, JSON, and more
- **REST API** - Full CRUD operations with search and filtering
- **GraphQL** - Query your data with GraphQL syntax
- **File Uploads** - Integrated with WordPress media library
- **Relationships** - Link entries between content types
- **Multi-Format Export** - CSV, JSON, and XML export
- **Webhooks** - Trigger actions on create/update/delete
- **Multi-Site Support** - Works with WordPress multisite

### Admin Interface
- **Setup Wizard** - Quick project setup (NextJS Forms, Headless CMS, API Backend)
- **Schema Builder** - Visual field editor with validation
- **Advanced Search** - Search entries by title or content
- **Bulk Operations** - Delete or update multiple entries
- **Entry Duplication** - Clone entries with one click
- **Table View** - Clean, sortable data tables

### Security
- **API Key Management** - Generate and manage API keys
- **CORS Configuration** - Whitelist allowed origins
- **Permission System** - WordPress capability-based access control

## 📦 Installation

1. Upload the plugin files to `/wp-content/plugins/atlaspress/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Run the setup wizard at **AtlasPress > Setup**
4. Choose your project type and start building!

## 🎯 Quick Start

### 1. Run Setup Wizard
Navigate to **AtlasPress > Setup** and choose:
- **NextJS/React Forms** - Track form submissions
- **Headless CMS** - Manage content via APIs
- **API Backend** - Store structured data
- **Blank Project** - Start from scratch

### 2. Create Content Types
Go to **AtlasPress > Content Types** and create your first content type:
```
Name: Contact Form
Fields:
  - name (text, required)
  - email (email, required)
  - message (textarea, required)
```

### 3. Submit Data via API
```javascript
await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    title: 'Contact Form Submission',
    data: {
      name: 'John Doe',
      email: 'john@example.com',
      message: 'Hello world!'
    }
  })
});
```

### 4. Fetch Data
```javascript
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries');
const { data: entries } = await response.json();
```

## 📡 API Reference

### REST API Endpoints

#### Content Types
- `GET /wp-json/atlaspress/v1/content-types` - List all content types
- `POST /wp-json/atlaspress/v1/content-types` - Create content type
- `GET /wp-json/atlaspress/v1/content-types/{id}` - Get single content type
- `DELETE /wp-json/atlaspress/v1/content-types/{id}` - Delete content type

#### Entries
- `GET /wp-json/atlaspress/v1/content-types/{id}/entries` - List entries
  - Query params: `?page=1&per_page=20&search=term&status=published`
- `POST /wp-json/atlaspress/v1/content-types/{id}/entries` - Create entry
- `GET /wp-json/atlaspress/v1/entries/{id}` - Get single entry
- `PUT /wp-json/atlaspress/v1/entries/{id}` - Update entry
- `DELETE /wp-json/atlaspress/v1/entries/{id}` - Delete entry
- `POST /wp-json/atlaspress/v1/entries/{id}/duplicate` - Duplicate entry

#### Bulk Operations
- `POST /wp-json/atlaspress/v1/entries/bulk-delete` - Delete multiple entries
- `POST /wp-json/atlaspress/v1/entries/bulk-update` - Update multiple entries

#### File Upload
- `POST /wp-json/atlaspress/v1/upload` - Upload file

#### Export
- `GET /wp-json/atlaspress/v1/export/csv/{type_id}` - Export as CSV
- `GET /wp-json/atlaspress/v1/export/json/{type_id}` - Export as JSON
- `GET /wp-json/atlaspress/v1/export/xml/{type_id}` - Export as XML

#### GraphQL
- `POST /wp-json/atlaspress/v1/graphql` - Execute GraphQL query

### GraphQL Examples

**Get Content Types:**
```graphql
{
  contentTypes {
    id, name, slug
  }
}
```

**Get Entries:**
```graphql
{
  entries(contentTypeId: 1, limit: 10) {
    id, title, created_at, data
  }
}
```

**Get Single Entry:**
```graphql
{
  entry(id: 5) {
    id, title, slug, data
  }
}
```

## 🔧 Integration Examples

### React/NextJS

```jsx
'use client';
import { useState, useEffect } from 'react';

export default function ContactForm() {
  const [form, setForm] = useState({ name: '', email: '', message: '' });
  const [status, setStatus] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        title: `Contact from ${form.name}`,
        data: form
      })
    });

    setStatus(response.ok ? 'Sent!' : 'Failed');
  };

  return (
    <form onSubmit={handleSubmit}>
      <input 
        placeholder="Name" 
        onChange={(e) => setForm({...form, name: e.target.value})} 
      />
      <input 
        placeholder="Email" 
        onChange={(e) => setForm({...form, email: e.target.value})} 
      />
      <textarea 
        placeholder="Message" 
        onChange={(e) => setForm({...form, message: e.target.value})} 
      />
      <button type="submit">Send</button>
      {status && <p>{status}</p>}
    </form>
  );
}
```

### Fetch Blog Posts (Server Component)

```jsx
export default async function BlogPage() {
  const res = await fetch('http://your-site.com/wp-json/atlaspress/v1/content-types/1/entries', {
    cache: 'no-store'
  });
  const { data: posts } = await res.json();

  return (
    <div>
      {posts.map(post => (
        <article key={post.id}>
          <h2>{post.data.title}</h2>
          <div dangerouslySetInnerHTML={{ __html: post.data.content }} />
        </article>
      ))}
    </div>
  );
}
```

## 🎨 Field Types

| Type | Description | Validation Options |
|------|-------------|-------------------|
| Text | Single line text | maxLength, minLength, pattern |
| Textarea | Multi-line text | maxLength, minLength |
| Rich Text | WYSIWYG editor | maxLength, minLength |
| Number | Numeric input | min, max, step |
| Email | Email validation | pattern |
| URL | URL validation | pattern |
| Phone | Phone number | pattern |
| Date | Date picker | min, max |
| Time | Time picker | min, max |
| DateTime | Date & time | min, max |
| Select | Dropdown | options, multiple |
| Radio | Radio buttons | options |
| Checkbox | Single checkbox | - |
| Checkboxes | Multiple checkboxes | options, minSelected, maxSelected |
| Range | Slider | min, max, step |
| Color | Color picker | - |
| Password | Password field | minLength, pattern |
| Hidden | Hidden field | - |
| File | File upload | fileTypes, maxSize, maxFiles |
| Relationship | Link to other entries | targetType, multiple |
| JSON | JSON data | schema |

## 🔐 Security

### API Keys (Free Version)
Generate API keys in **AtlasPress > Security**:
1. Enter key name
2. Click "Generate API Key"
3. Copy the key (shown once)
4. Use in requests: `X-API-Key: your-key-here`

### CORS Configuration
Add allowed origins in **AtlasPress > Security**:
```
https://example.com
https://app.example.com
```

Leave empty to allow all origins (development only).

## 🐛 Troubleshooting

### Common Issues

**401 Unauthorized Errors**
- Ensure you're logged in to WordPress admin
- Configure CORS for your frontend domain
- Check API key if using authentication

**File Upload Fails**
- Check PHP upload limits: `upload_max_filesize`, `post_max_size`
- Verify WordPress media upload permissions
- Check file type restrictions

**GraphQL Errors**
- Verify query syntax
- Ensure content type IDs exist
- Check field names match schema

**Rate Limit Exceeded**
- Clear browser cache
- Wait a moment before retrying
- This is a WordPress REST API limit

## 📊 System Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- Modern browser (for admin interface)

## 🔄 Changelog

### Version 1.0.0
- Initial release
- 20+ field types
- REST API & GraphQL
- File uploads
- Relationships
- Multi-format export
- Bulk operations
- Advanced search
- API key management
- CORS configuration

## 📝 License

GPL v2 or later

## 🤝 Support

- **Documentation**: AtlasPress > Help in WordPress admin
- **Test Page**: `/test-atlaspress.html` on your site
- **Issues**: Check troubleshooting guide in Help section

## 🚀 Pro Version (Coming Soon)

Upgrade to AtlasPress Pro for:
- Rate limiting with custom rules
- Advanced analytics dashboard
- Email notifications
- Webhook retry logic
- Cloud file storage (S3, Cloudinary)
- Priority support
- Early access to new features

---

Made with ❤️ for modern WordPress development
