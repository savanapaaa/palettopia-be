# Palettopia API Documentation - Ready Endpoints

Base URL: `http://127.0.0.1:8000/api`

## 🔓 Public Endpoints (Tidak Perlu Login)

### 1. Register User
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890"
}

Response 201:
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|abc123..."
  }
}
```

### 2. Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

Response 200:
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|abc123..."
  }
}
```

### 3. Get All Products (Public)
```http
GET /api/products
GET /api/products?palette_category=spring+bright

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000",
      "image_url": null,
      "palette_category": "spring bright",
      "description": "Light pink lipstick for spring bright palette...",
      "created_at": "2025-12-16T10:00:00.000000Z"
    }
  ]
}
```

### 4. Get Single Product
```http
GET /api/products/{id}

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000",
    "image_url": null,
    "palette_category": "spring bright",
    "description": "Light pink lipstick..."
  }
}
```

### 5. Get Palettes
```http
GET /api/palettes

Response 200:
{
  "success": true,
  "data": [
    "winter clear",
    "summer cool",
    "spring bright",
    "autumn warm"
  ]
}
```

### 6. Get Palette Colors
```http
GET /api/palettes/spring+bright

Response 200:
{
  "success": true,
  "data": {
    "palette": "spring bright",
    "colors": [
      "#FFB5E8", "#FF9CEE", "#FFCCF9", "#FCC2FF",
      "#F6A6FF", "#82BDFF", "#C5A3FF", "#D5AAFF"
    ],
    "undertone": "warm"
  }
}
```

### 7. Get Recommendations by Palette (Public)
```http
GET /api/recommendations?palette=spring+bright

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 7,
      "name": "Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000",
      "palette_category": "spring bright"
    }
  ]
}
```

---

## 🔐 Protected Endpoints (Perlu Login)

**Header Required:**
```
Authorization: Bearer {token}
```

### 8. Get Current User
```http
GET /api/me
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "phone": "081234567890"
  }
}
```

### 9. Logout
```http
POST /api/logout
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 10. Upload Image
```http
POST /api/uploads/image
Authorization: Bearer {token}
Content-Type: multipart/form-data

FormData:
  image: (binary file - jpg/png, max 10MB)

Response 200:
{
  "success": true,
  "data": {
    "url": "/storage/analyses/UxfarXPXgrXnRbxYyQBQcQBEizHqMJG5.jpg",
    "filename": "UxfarXPXgrXnRbxYyQBQcQBEizHqMJG5.jpg"
  }
}
```

### 11. Analyze Image (Skin Tone Analysis)
```http
POST /api/analysis
Authorization: Bearer {token}
Content-Type: application/json

{
  "image_url": "/storage/analyses/UxfarXPXgrXnRbxYyQBQcQBEizHqMJG5.jpg"
}

Response 200:
{
  "success": true,
  "data": {
    "id": 52,
    "palette_name": "spring bright",
    "colors": [
      "#FFB5E8", "#FF9CEE", "#FFCCF9", "#FCC2FF",
      "#F6A6FF", "#82BDFF", "#C5A3FF", "#D5AAFF"
    ],
    "undertone": "neutral",
    "explanation": "Palette determined based on your skin tone colors...",
    "created_at": "2025-12-16T10:00:00.000000Z",
    "recommendations": [
      {
        "id": 7,
        "name": "Spring Bright Lipstick #FFCCF9 by ByNeer - Rp 85.000",
        "image_url": null,
        "palette_category": "spring bright",
        "description": "Light pink lipstick..."
      }
    ]
  }
}
```

### 12. Get Analysis History
```http
GET /api/history
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 52,
      "user_id": 1,
      "result_palette": "spring bright",
      "colors": ["#FFB5E8", "#FF9CEE", "#FFCCF9", "#FCC2FF", "#F6A6FF", "#82BDFF", "#C5A3FF", "#D5AAFF"],
      "image_url": "/storage/analyses/xxx.jpg",
      "notes": "Palette determined based on your skin tone colors...",
      "created_at": "2025-12-16T10:00:00.000000Z"
    }
  ]
}
```

### 13. Delete Analysis History
```http
DELETE /api/history/{id}
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "message": "Analysis history deleted successfully"
}
```

### 14. Get User Profile
```http
GET /api/profile
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "081234567890",
    "role": "user",
    "created_at": "2025-12-15T10:00:00.000000Z"
  }
}
```

### 15. Update Profile
```http
PUT /api/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Updated",
  "phone": "081234567890",
  "password": "newpassword123"
}

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Updated",
    "email": "john@example.com",
    "phone": "081234567890",
    "role": "user"
  }
}
```

### 16. Get Product Recommendations (Based on Latest Analysis)
```http
GET /api/recommendation
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "palette": "spring bright",
    "colors": ["#FFB5E8", "#FF9CEE", "#FFCCF9", "#FCC2FF", "#F6A6FF", "#82BDFF", "#C5A3FF", "#D5AAFF"],
    "message": "Use this palette to search for products"
  }
}
```

---

## 📋 Integration Notes

### Authentication Flow
```typescript
// 1. Login
const response = await fetch('http://127.0.0.1:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});
const data = await response.json();
const token = data.data.token;

// 2. Use token for protected routes
const userResponse = await fetch('http://127.0.0.1:8000/api/me', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

### CORS Configuration
Backend sudah configured untuk accept requests dari:
- `http://localhost:5173`
- `http://127.0.0.1:5173`

### Error Response Format
```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created (register success)
- `400` - Bad Request (validation error)
- `401` - Unauthorized (invalid credentials)
- `404` - Not Found
- `500` - Server Error

---

## 🎨 Palette Types & Colors

### Available Palette Types
1. **winter clear** - Cool undertones, high contrast, icy colors
2. **summer cool** - Cool undertones, soft/muted, dusty colors
3. **spring bright** - Warm undertones, bright/clear, fresh colors
4. **autumn warm** - Warm undertones, rich/earthy, muted colors

### Fixed Color Palettes (Match Google Drive Products)

**Winter Clear:**
```
#E8F1F5, #B4D4E1, #7FB3D5, #5499C7, 
#2980B9, #1F618D, #1A5276, #154360
```

**Summer Cool:**
```
#85E3FF, #ACE7FF, #A7C7E7, #B4E7CE, 
#95E1D3, #7FCDCD, #82CAFF, #A0CFEC
```

**Spring Bright:**
```
#FFB5E8, #FF9CEE, #FFCCF9, #FCC2FF, 
#F6A6FF, #82BDFF, #C5A3FF, #D5AAFF
```

**Autumn Warm:**
```
#E07A5F, #F2CC8F, #81B29A, #C1666B, 
#D4A373, #3D5A80, #774936, #F4F1DE
```

---

## 🚀 Complete Frontend Integration Example

```typescript
// API Service
class PalettopiaAPI {
  baseURL = 'http://127.0.0.1:8000/api';
  token: string | null = null;

  constructor() {
    this.token = localStorage.getItem('token');
  }

  // Auth
  async login(email: string, password: string) {
    const res = await fetch(`${this.baseURL}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const data = await res.json();
    if (data.success) {
      this.token = data.data.token;
      localStorage.setItem('token', this.token);
    }
    return data;
  }

  async register(name: string, email: string, password: string, password_confirmation: string) {
    const res = await fetch(`${this.baseURL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password, password_confirmation })
    });
    const data = await res.json();
    if (data.success) {
      this.token = data.data.token;
      localStorage.setItem('token', this.token);
    }
    return data;
  }

  async logout() {
    const res = await fetch(`${this.baseURL}/logout`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    localStorage.removeItem('token');
    this.token = null;
    return res.json();
  }

  // Analysis
  async uploadImage(file: File) {
    const formData = new FormData();
    formData.append('image', file);
    
    const res = await fetch(`${this.baseURL}/uploads/image`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${this.token}` },
      body: formData
    });
    return res.json();
  }

  async analyzeImage(imageUrl: string) {
    const res = await fetch(`${this.baseURL}/analysis`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ image_url: imageUrl })
    });
    return res.json();
  }

  // History
  async getHistory() {
    const res = await fetch(`${this.baseURL}/history`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }

  async deleteHistory(id: number) {
    const res = await fetch(`${this.baseURL}/history/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }

  // Products
  async getProducts(paletteCategory?: string) {
    const url = paletteCategory 
      ? `${this.baseURL}/products?palette_category=${encodeURIComponent(paletteCategory)}`
      : `${this.baseURL}/products`;
    const res = await fetch(url);
    return res.json();
  }

  // Profile
  async getProfile() {
    const res = await fetch(`${this.baseURL}/profile`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    });
    return res.json();
  }

  async updateProfile(data: { name?: string; phone?: string; password?: string }) {
    const res = await fetch(`${this.baseURL}/profile`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });
    return res.json();
  }

  // Palettes
  async getPalettes() {
    const res = await fetch(`${this.baseURL}/palettes`);
    return res.json();
  }

  async getPaletteColors(palette: string) {
    const res = await fetch(`${this.baseURL}/palettes/${encodeURIComponent(palette)}`);
    return res.json();
  }
}

// Usage
const api = new PalettopiaAPI();

// Login example
const loginResult = await api.login('test@palettopia.com', 'password123');

// Upload & Analyze
const uploadResult = await api.uploadImage(imageFile);
const analysisResult = await api.analyzeImage(uploadResult.data.url);

// Get history
const history = await api.getHistory();

// Get products by palette
const products = await api.getProducts('spring bright');
```

---

## ✅ Ready for Frontend Integration

### User Features (100% Complete)
- ✅ Authentication (Login/Register/Logout)
- ✅ Image Upload & Analysis
- ✅ Analysis History (List & Delete)
- ✅ Product Catalog with Filters
- ✅ User Profile (View & Update)
- ✅ Product Recommendations

### Admin Features (Not Available Yet)
- ❌ `GET /api/admin/statistics` - Dashboard stats
- ❌ `GET /api/admin/products` - Admin product management
- ❌ `POST /api/admin/products` - Create product
- ❌ `PUT /api/admin/products/{id}` - Update product
- ❌ `DELETE /api/admin/products/{id}` - Delete product
- ❌ `GET /api/admin/analyses` - View all user analyses

Contact backend team if admin endpoints are needed.
