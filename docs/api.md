# Storender API Documentation

## Authentication
Storender uses session-based authentication for the dashboard and API Key authentication for programmatic access to assets.

### Register
`POST /api/auth/register`
- **Body**: `{ "email": "user@example.com", "password": "password" }`
- **Response**: `201 Created`

### Login
`POST /api/auth/login`
- **Body**: `{ "email": "user@example.com", "password": "password" }`
- **Response**: `200 OK` (Set-Cookie: PHPSESSID)

---

## Projects

### List Projects
`GET /api/projects`
- **Headers**: `Cookie: PHPSESSID=...`
- **Response**: `[ { "id": 1, "name": "My Project", "slug": "my-project" } ]`

### Create Project
`POST /api/projects`
- **Body**: `{ "name": "New Project" }`
- **Response**: `201 Created`

### Delete Project
`DELETE /api/projects/{id}`
- **Response**: `200 OK`
- **Warning**: Recursively deletes all files and keys associated with the project.

### Generate API Key
`POST /api/projects/{id}/keys`
- **Body**: `{ "label": "Production" }`
- **Response**: `{ "key": "generated-key-string" }`

---

## Files

### Upload File
`POST /api/files`
- **Headers**:
    - `X-Project-ID`: `{project_id}`
    - `X-File-Name`: `image.png`
    - `Content-Type`: `image/png`
    - `Cookie`: `PHPSESSID=...` (Dashboard) OR `X-API-Key: ...` (External)
- **Body**: Raw binary file content
- **Response**: `201 Created`

### Download/View File
`GET /api/files/{id}`
- **Headers**: `X-API-Key: ...` (Required only if file is Private)
- **Response**: Binary file stream

### Update Visibility
`PATCH /api/files/{id}`
- **Headers**: `Content-Type: application/json`
- **Body**: `{ "is_public": 1 }` (1 for Public, 0 for Private)
- **Response**: `200 OK`

### Delete File
`DELETE /api/files/{id}`
- **Headers**: `X-Project-ID: ...`
- **Response**: `200 OK`

## Backender Integration
To use Storender as the backend storage for your Backender projects, create a proxy endpoint in Backender:

```php
// In Backender: POST /upload
return function ($request) {
    // 1. Authenticate in Backender
    // if (!$request->user()) return Response::error('Unauthorized', 401);

    // 2. Proxy to Storender
    $storenderUrl = 'http://storender:8080/api/files';
    $apiKey = 'YOUR_STORENDER_API_KEY';
    $projectId = '1'; // Your Target Project ID

    $ch = curl_init($storenderUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-API-Key: $apiKey",
        "X-Project-ID: $projectId"
    ]);

    // Forward the file
    $cFile = new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cFile]);

    $response = curl_exec($ch);
    curl_close($ch);

    // 3. Save URL to Database & Return
    $result = json_decode($response, true);
    // DB::insert('images', ['url' => '/api/files/' . $result['id']]);
    
    return $result;
};
```

### Displaying Private Images
If your file is **Private**, standard `<img>` tags won't work because they can't send headers. You can pass the key in the URL:
```html
<img src="http://storender:8080/api/files/{id}?api_key=YOUR_KEY" />
```
*Note: For better security, keep portfolio assets Public.*
