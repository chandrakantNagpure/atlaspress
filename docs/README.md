# AtlasPress Documentation

AtlasPress is a powerful WordPress plugin that transforms WordPress into a structured data backend with schema-driven content types, REST API, GraphQL, and form capture capabilities.

## Features

### Content Types
- Schema-driven content type builder with 20+ field types
- Custom field types: Text, Number, Email, URL, Textarea, Rich Text, Select, Checkbox, Radio, Date, DateTime, File, Image, Color, JSON, and more
- Field validation rules
- Custom slugs and descriptions

### Entries Management
- Full CRUD operations for entries
- Bulk actions (delete, update status)
- Search and filtering
- Import/Export (CSV, JSON, XML)

### APIs
- **REST API** - Full RESTful endpoints for content types and entries
- **GraphQL** - Query data using GraphQL
- **Form Proxy** - Capture form submissions from any frontend

### Integrations
- Webhook triggers for create/update/delete events
- WordPress media library integration for file uploads
- HubSpot integration

### Admin Features
- Setup Wizard for quick project initialization
- Dashboard with statistics
- Security settings with API key management
- CORS configuration
- Multisite support

## Installation

1. Upload the `atlaspress` folder to the `/wp-content/plugins/` directory
2. Activate AtlasPress from the Plugins screen in WordPress
3. Open **AtlasPress > Setup** in the admin area
4. Complete the setup wizard
5. Create your first content type and start submitting entries

## Quick Start

### Create a Content Type

1. Go to **AtlasPress > Content Types**
2. Click "Add New"
3. Enter a name (e.g., "Blog Post")
4. Click "Create"
5. Edit the schema to add fields (title, content, author, etc.)

### Submit Entries via REST API

```javascript
// Submit a new entry
const response = await fetch('/wp-json/atlaspress/v1/content-types/1/entries', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_API_KEY'
  },
  body: JSON.stringify({
    title: 'My Blog Post',
    data: {
      content: 'Post content here...',
      author: 'John Doe'
    }
  })
});

const entry = await response.json();
```

### Submit Entries via Form

```html
<form action="/api/atlaspress/submit" method="POST">
  <input type="hidden" name="form_type" value="contact">
  <input type="hidden" name="form_id" value="contact-form-1">
  <input type="text" name="name" placeholder="Your Name">
  <input type="email" name="email" placeholder="Your Email">
  <textarea name="message" placeholder="Message"></textarea>
  <button type="submit">Submit</button>
</form>
```

### Query via GraphQL

```graphql
{
  entries(contentTypeId: 1, limit: 10) {
    id
    title
    data
    created_at
  }
}
```

## REST API Endpoints

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/wp-json/atlaspress/v1/content-types` | GET, POST | List/Create content types |
| `/wp-json/atlaspress/v1/content-types/{id}` | GET, DELETE | Get/Delete content type |
| `/wp-json/atlaspress/v1/content-types/{id}/entries` | GET, POST | List/Create entries |
| `/wp-json/atlaspress/v1/entries/{id}` | GET, PUT, DELETE | Entry operations |
| `/wp-json/atlaspress/v1/graphql` | POST | GraphQL endpoint |
| `/wp-json/atlaspress/v1/upload` | POST | File upload |

## Field Types

| Type | Description |
|------|-------------|
| text | Single line text |
| textarea | Multi-line text |
| rich_text | Rich text editor |
| number | Number input |
| email | Email input |
| url | URL input |
| date | Date picker |
| datetime | Date and time picker |
| select | Dropdown select |
| radio | Radio buttons |
| checkbox | Checkbox |
| file | File upload |
| image | Image upload |
| color | Color picker |
| json | JSON editor |
| relationship | Link to other entries |

## Security

### API Keys
Generate API keys from **AtlasPress > Settings > Security**

### CORS Configuration
Configure allowed origins from **AtlasPress > Settings > Security**

### Capabilities
- `atlaspress_manage_types` - Create/edit/delete content types
- `atlaspress_edit_entries` - Edit entries
- `atlaspress_delete_entries` - Delete entries
- `atlaspress_view_dashboard` - View dashboard

## Free Version Limitations

The free version is limited to **2 content types**. To create unlimited content types, upgrade to Pro (coming soon).

## Support

- GitHub: https://github.com/chandrakantNagpure/atlaspress
- WordPress: https://wordpress.org/plugins/atlaspress/

## Changelog

### 1.0.0
- Initial release
- Schema-driven content type and entry management
- REST API and GraphQL support
- Form capture and webhook integration
- Import/export and file upload support
