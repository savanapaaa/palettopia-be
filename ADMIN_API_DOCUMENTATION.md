# Admin API Endpoints - Palettopia

Base URL: `http://127.0.0.1:8000/api/admin`

**Authentication Required:** All admin endpoints require:
1. Valid Bearer token (login as admin)
2. User role must be `admin`

## Admin Login Credentials

```
Email: admin@palettopia.com
Password: admin123
```

---

## 🔐 Admin Endpoints

### 1. Get Dashboard Statistics
```http
GET /api/admin/statistics
Authorization: Bearer {admin_token}

Response 200:
{
  "success": true,
  "data": {
    "total_users": 5,
    "total_admins": 1,
    "total_products": 12,
    "total_analyses": 24,
    "products_by_palette": {
      "winter_clear": 3,
      "summer_cool": 3,
      "spring_bright": 3,
      "autumn_warm": 3
    },
    "recent_analyses": [
      {
        "id": 52,
        "user": {
          "id": 2,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "result_palette": "spring bright",
        "created_at": "2025-12-16T10:00:00.000000Z"
      }
    ],
    "analyses_by_palette": {
      "winter_clear": 6,
      "summer_cool": 5,
      "spring_bright": 8,
      "autumn_warm": 5
    }
  }
}
```

---

### 2. Get All Products (Admin View)
```http
GET /api/admin/products
GET /api/admin/products?palette_category=spring+bright
GET /api/admin/products?search=lipstick
GET /api/admin/products?per_page=20
Authorization: Bearer {admin_token}

Response 200:
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000",
        "image_url": null,
        "palette_category": "spring bright",
        "description": "Light pink lipstick...",
        "user": {
          "id": 1,
          "name": "Test User",
          "email": "test@palettopia.com"
        },
        "created_at": "2025-12-16T10:00:00.000000Z",
        "updated_at": "2025-12-16T10:00:00.000000Z"
      }
    ],
    "per_page": 15,
    "total": 12
  }
}
```

**Query Parameters:**
- `palette_category` - Filter by palette (winter clear, summer cool, spring bright, autumn warm)
- `search` - Search by product name
- `per_page` - Items per page (default: 15)

---

### 3. Create Product
```http
POST /api/admin/products
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

FormData:
  name: "Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000"
  description: "Light pink lipstick for spring bright palette"
  palette_category: "spring bright"
  image: (file) // optional, jpg/png max 10MB

Or JSON (without image):
Content-Type: application/json
{
  "name": "Product Name",
  "description": "Product description",
  "palette_category": "spring bright",
  "image_url": "/storage/products/image.jpg" // optional
}

Response 201:
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 13,
    "name": "Spring Bright Lipstick #FFCCF9...",
    "image_url": "/storage/products/abc123.jpg",
    "palette_category": "spring bright",
    "description": "Light pink lipstick...",
    "user_id": 1,
    "created_at": "2025-12-16T10:00:00.000000Z",
    "updated_at": "2025-12-16T10:00:00.000000Z"
  }
}

Error 422 (Validation Failed):
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "palette_category": ["The selected palette category is invalid."]
  }
}
```

**Validation Rules:**
- `name` - required, string, max 255 characters
- `description` - optional, string
- `palette_category` - required, must be one of: winter clear, summer cool, spring bright, autumn warm
- `image` - optional, image file (jpeg/png/jpg/gif), max 10MB
- `image_url` - optional, string

---

### 4. Update Product
```http
PUT /api/admin/products/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Updated Product Name",
  "description": "Updated description",
  "palette_category": "autumn warm"
}

// For image upload, use multipart/form-data:
Content-Type: multipart/form-data
FormData:
  name: "Updated Name"
  image: (file)

Response 200:
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Product Name",
    "image_url": "/storage/products/new-image.jpg",
    "palette_category": "autumn warm",
    "description": "Updated description",
    "created_at": "2025-12-16T10:00:00.000000Z",
    "updated_at": "2025-12-16T11:00:00.000000Z"
  }
}

Error 404 (Not Found):
{
  "success": false,
  "message": "Product not found"
}
```

**Notes:**
- All fields are optional for update
- If new image uploaded, old image will be deleted automatically
- Only provide fields you want to update

---

### 5. Delete Product
```http
DELETE /api/admin/products/{id}
Authorization: Bearer {admin_token}

Response 200:
{
  "success": true,
  "message": "Product deleted successfully"
}

Error 404 (Not Found):
{
  "success": false,
  "message": "Product not found"
}
```

**Notes:**
- Product image will be deleted from storage automatically
- This action cannot be undone

---

### 6. View All User Analyses
```http
GET /api/admin/analyses
GET /api/admin/analyses?palette=spring+bright
GET /api/admin/analyses?user_id=2
GET /api/admin/analyses?search=john
GET /api/admin/analyses?date_from=2025-12-01&date_to=2025-12-31
GET /api/admin/analyses?per_page=20
Authorization: Bearer {admin_token}

Response 200:
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 52,
        "user_id": 2,
        "result_palette": "spring bright",
        "colors": [
          "#FFB5E8", "#FF9CEE", "#FFCCF9", "#FCC2FF",
          "#F6A6FF", "#82BDFF", "#C5A3FF", "#D5AAFF"
        ],
        "image_url": "/storage/analyses/abc123.jpg",
        "notes": "Palette determined based on your skin tone colors...",
        "user": {
          "id": 2,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "created_at": "2025-12-16T10:00:00.000000Z",
        "updated_at": "2025-12-16T10:00:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 24
  }
}
```

**Query Parameters:**
- `palette` - Filter by palette type
- `user_id` - Filter by specific user
- `search` - Search by user name or email
- `date_from` - Filter from date (YYYY-MM-DD)
- `date_to` - Filter to date (YYYY-MM-DD)
- `per_page` - Items per page (default: 20)

---

## 🔒 Authorization & Errors

### 403 Forbidden (Not Admin)
```json
{
  "success": false,
  "message": "Unauthorized. Admin access only."
}
```

### 401 Unauthorized (No Token)
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to fetch statistics"
}
```

---

## 🚀 Frontend Integration Example

```typescript
class AdminAPI {
  baseURL = 'http://127.0.0.1:8000/api/admin';
  token: string;

  constructor(token: string) {
    this.token = token;
  }

  // Statistics
  async getStatistics() {
    const res = await fetch(`${this.baseURL}/statistics`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }

  // Products
  async getProducts(filters?: { palette_category?: string; search?: string; per_page?: number }) {
    const params = new URLSearchParams(filters as any);
    const res = await fetch(`${this.baseURL}/products?${params}`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }

  async createProduct(data: { name: string; description?: string; palette_category: string }, image?: File) {
    if (image) {
      const formData = new FormData();
      formData.append('name', data.name);
      formData.append('palette_category', data.palette_category);
      if (data.description) formData.append('description', data.description);
      formData.append('image', image);

      const res = await fetch(`${this.baseURL}/products`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${this.token}` },
        body: formData
      });
      return res.json();
    } else {
      const res = await fetch(`${this.baseURL}/products`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
      return res.json();
    }
  }

  async updateProduct(id: number, data: any, image?: File) {
    if (image) {
      const formData = new FormData();
      Object.keys(data).forEach(key => formData.append(key, data[key]));
      formData.append('image', image);

      const res = await fetch(`${this.baseURL}/products/${id}`, {
        method: 'PUT',
        headers: { 'Authorization': `Bearer ${this.token}` },
        body: formData
      });
      return res.json();
    } else {
      const res = await fetch(`${this.baseURL}/products/${id}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
      return res.json();
    }
  }

  async deleteProduct(id: number) {
    const res = await fetch(`${this.baseURL}/products/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }

  // Analyses
  async getAnalyses(filters?: {
    palette?: string;
    user_id?: number;
    search?: string;
    date_from?: string;
    date_to?: string;
    per_page?: number;
  }) {
    const params = new URLSearchParams(filters as any);
    const res = await fetch(`${this.baseURL}/analyses?${params}`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }
}

// Usage
const adminAPI = new AdminAPI(adminToken);

// Get dashboard stats
const stats = await adminAPI.getStatistics();

// Get products
const products = await adminAPI.getProducts({ palette_category: 'spring bright' });

// Create product
const newProduct = await adminAPI.createProduct({
  name: 'New Product',
  palette_category: 'autumn warm',
  description: 'Product description'
}, imageFile);

// Update product
await adminAPI.updateProduct(1, { name: 'Updated Name' });

// Delete product
await adminAPI.deleteProduct(1);

// Get all analyses
const analyses = await adminAPI.getAnalyses({ palette: 'spring bright' });
```

---

## ✅ Complete Admin API Summary

| Endpoint | Method | Description | Auth |
|----------|--------|-------------|------|
| `/api/admin/statistics` | GET | Dashboard statistics | Admin |
| `/api/admin/products` | GET | List all products with filters | Admin |
| `/api/admin/products` | POST | Create new product | Admin |
| `/api/admin/products/{id}` | PUT | Update product | Admin |
| `/api/admin/products/{id}` | DELETE | Delete product | Admin |
| `/api/admin/analyses` | GET | View all user analyses | Admin |

All endpoints are now **READY** for frontend integration! 🎉
