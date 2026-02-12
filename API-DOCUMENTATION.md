# AtlasPress API Documentation

Complete API reference for AtlasPress REST API and GraphQL endpoints.

## Base URL

```
http://your-site.com/wp-json/atlaspress/v1
```

## Authentication

### Free Version
- **Admin Users**: Automatically authenticated when logged in
- **Public Access**: Open by default for GET requests
- **API Keys**: Optional, configure in Security settings

### Using API Keys
```javascript
headers: {
  'X-API-Key': 'your-api-key-here'
}
```

---

## REST API Endpoints

### Content Types

#### List All Content Types
```
GET /content-types
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Contact Form",
      "slug": "contact-form",
      "description": "",
      "settings": {
        "fields": [...]
      },
      "status": "active",
      "created_at": "2024-01-01 12:00:00"
    }
  ]
}
```

#### Get Single Content Type
```
GET /content-types/{id}
```

#### Create Content Type
```
POST /content-types
```

**Body:**
```json
{
  "name": "Contact Form"
}
```

#### Delete Content Type
```
DELETE /content-types/{id}
```

#### Update Schema
```
PUT /content-types/{id}/schema
```

**Body:**
```json
{
  "schema": [
    {
      "name": "email",
      "type": "email",
      "label": "Email Address",
      "required": true
    }
  ]
}
```

---

### Entries

#### List Entries
```
GET /content-types/{type_id}/entries
```

**Query Parameters:**
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 20, max: 100)
- `search` (string): Search term
- `status` (string): Filter by status (published, draft, pending)

**Example:**
```
GET /content-types/1/entries?page=1&per_page=20&search=john&status=published
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "content_type_id": 1,
      "title": "Contact Form Submission",
      "slug": "contact-form-submission",
      "data": {
        "name": "John Doe",
        "email": "john@example.com",
        "message": "Hello!"
      },
      "status": "published",
      "author_id": 1,
      "created_at": "2024-01-01 12:00:00",
      "updated_at": "2024-01-01 12:00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

#### Get Single Entry
```
GET /entries/{id}
```

#### Create Entry
```
POST /content-types/{type_id}/entries
```

**Body:**
```json
{
  "title": "Contact Form Submission",
  "data": {
    "name": "John Doe",
    "email": "john@example.com",
    "message": "Hello world!"
  }
}
```

**Response:**
```json
{
  "message": "Created",
  "id": 123
}
```

#### Update Entry
```
PUT /entries/{id}
```

**Body:**
```json
{
  "title": "Updated Title",
  "data": {
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

#### Delete Entry
```
DELETE /entries/{id}
```

#### Duplicate Entry
```
POST /entries/{id}/duplicate
```

**Response:**
```json
{
  "message": "Entry duplicated",
  "id": 124
}
```

---

### Bulk Operations

#### Bulk Delete
```
POST /entries/bulk-delete
```

**Body:**
```json
{
  "ids": [1, 2, 3, 4, 5]
}
```

#### Bulk Update Status
```
POST /entries/bulk-update
```

**Body:**
```json
{
  "ids": [1, 2, 3],
  "status": "published"
}
```

---

### File Upload

#### Upload File
```
POST /upload
```

**Content-Type:** `multipart/form-data`

**Form Data:**
- `file`: File to upload
- `field_type` (optional): Field type
- `allowed_types` (optional): Array of allowed extensions
- `max_size` (optional): Max file size in bytes

**Response:**
```json
{
  "id": 456,
  "url": "http://site.com/wp-content/uploads/2024/01/file.pdf",
  "filename": "file.pdf",
  "type": "application/pdf",
  "size": 102400
}
```

**Example:**
```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);

const response = await fetch('/wp-json/atlaspress/v1/upload', {
  method: 'POST',
  body: formData
});
```

#### Delete File
```
DELETE /files/{id}
```

---

### Export

#### Export as CSV
```
GET /export/csv/{type_id}
```

Downloads CSV file.

#### Export as JSON
```
GET /export/json/{type_id}
```

Downloads JSON file.

#### Export as XML
```
GET /export/xml/{type_id}
```

Downloads XML file.

---

### Relationships

#### Search Related Items
```
GET /relationships/{type_id}?search={term}
```

**Response:**
```json
[
  {
    "id": 1,
    "title": "Related Item",
    "slug": "related-item"
  }
]
```

---

## GraphQL API

### Endpoint
```
POST /graphql
```

### Query Structure
```json
{
  "query": "{ contentTypes { id, name } }"
}
```

### Available Queries

#### Get Content Types
```graphql
{
  contentTypes {
    id
    name
    slug
    status
    created_at
  }
}
```

**With Arguments:**
```graphql
{
  contentTypes(id: 1) {
    id
    name
  }
}

{
  contentTypes(limit: 5) {
    id
    name
  }
}
```

#### Get Entries
```graphql
{
  entries(contentTypeId: 1) {
    id
    title
    slug
    data
    status
    created_at
  }
}
```

**With Arguments:**
```graphql
{
  entries(contentTypeId: 1, limit: 10) {
    id
    title
  }
}

{
  entries(contentTypeId: 1, status: "published") {
    id
    title
  }
}
```

#### Get Single Entry
```graphql
{
  entry(id: 5) {
    id
    title
    slug
    data
    created_at
    updated_at
  }
}
```

#### Multiple Queries
```graphql
{
  contentTypes(limit: 3) {
    id
    name
  }
  entries(contentTypeId: 1, limit: 5) {
    id
    title
  }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "code": "invalid_data",
  "message": "Invalid request data",
  "data": {
    "status": 400
  }
}
```

### 401 Unauthorized
```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to do that.",
  "data": {
    "status": 401
  }
}
```

### 404 Not Found
```json
{
  "code": "not_found",
  "message": "Entry not found",
  "data": {
    "status": 404
  }
}
```

### 422 Validation Error
```json
{
  "code": "validation_failed",
  "message": "Validation failed for field: email",
  "data": {
    "status": 422
  }
}
```

---

## Rate Limiting

Free version uses WordPress default rate limiting:
- **Logged-in users**: No limit
- **Public requests**: WordPress default (varies by host)

---

## CORS Configuration

Configure allowed origins in **AtlasPress > Security**:

```
https://example.com
https://app.example.com
```

Headers sent:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, X-API-Key
```

---

## Webhooks

Webhooks trigger on these events:
- `atlaspress_entry_created`
- `atlaspress_entry_updated`
- `atlaspress_entry_deleted`
- `atlaspress_content_type_created`

**Payload:**
```json
{
  "event": "atlaspress_entry_created",
  "data": {
    "id": 123,
    "title": "Entry Title",
    "content_type_id": 1
  },
  "timestamp": 1704110400
}
```

---

## Code Examples

### JavaScript/Fetch

```javascript
// Create entry
const createEntry = async (typeId, data) => {
  const response = await fetch(`/wp-json/atlaspress/v1/content-types/${typeId}/entries`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      title: 'Entry Title',
      data: data
    })
  });
  return response.json();
};

// Get entries with search
const searchEntries = async (typeId, searchTerm) => {
  const response = await fetch(
    `/wp-json/atlaspress/v1/content-types/${typeId}/entries?search=${encodeURIComponent(searchTerm)}`
  );
  return response.json();
};

// Upload file
const uploadFile = async (file) => {
  const formData = new FormData();
  formData.append('file', file);
  
  const response = await fetch('/wp-json/atlaspress/v1/upload', {
    method: 'POST',
    body: formData
  });
  return response.json();
};
```

### React Hooks

```jsx
import { useState, useEffect } from 'react';

function useEntries(typeId) {
  const [entries, setEntries] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`/wp-json/atlaspress/v1/content-types/${typeId}/entries`)
      .then(r => r.json())
      .then(data => {
        setEntries(data.data);
        setLoading(false);
      });
  }, [typeId]);

  return { entries, loading };
}

// Usage
function MyComponent() {
  const { entries, loading } = useEntries(1);
  
  if (loading) return <div>Loading...</div>;
  
  return (
    <div>
      {entries.map(entry => (
        <div key={entry.id}>{entry.title}</div>
      ))}
    </div>
  );
}
```

### PHP/WordPress

```php
// Get entries
$response = wp_remote_get(
  home_url('/wp-json/atlaspress/v1/content-types/1/entries')
);
$entries = json_decode(wp_remote_retrieve_body($response), true);

// Create entry
$response = wp_remote_post(
  home_url('/wp-json/atlaspress/v1/content-types/1/entries'),
  [
    'headers' => ['Content-Type' => 'application/json'],
    'body' => json_encode([
      'title' => 'Entry Title',
      'data' => ['name' => 'John', 'email' => 'john@example.com']
    ])
  ]
);
```

---

## Best Practices

1. **Always validate data** before sending to API
2. **Use pagination** for large datasets
3. **Cache responses** when appropriate
4. **Handle errors** gracefully
5. **Use HTTPS** in production
6. **Implement rate limiting** on your frontend
7. **Test with test page** before production

---

**AtlasPress API v1.0.0**
