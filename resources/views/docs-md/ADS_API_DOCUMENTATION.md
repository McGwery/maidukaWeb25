# Advertising Platform API - Complete Documentation

## 🎯 Overview

A professional advertising platform that allows:
- **Shops** to create ads (minimum 1 per month with active subscription)
- **System Administrators** to create premium ads for large companies
- **Users** to see personalized ads in a dedicated "Deals" tab
- **Complete Analytics** - Views, clicks, conversions, CTR, and more

---

## ✨ Key Features

### For Shop Owners
- ✅ Create ads (images or videos)
- ✅ Target by shop categories, types, or location
- ✅ Set budget and cost-per-click
- ✅ Schedule ads (start/end dates)
- ✅ Real-time analytics dashboard
- ✅ Monthly ad limits based on subscription plan

### For Administrators
- ✅ Create unlimited ads for any company
- ✅ Auto-approved ads
- ✅ Approve/reject shop ads
- ✅ Monitor all ad performance
- ✅ Handle inappropriate content reports

### For Users
- ✅ Personalized ad feed in "Deals" tab
- ✅ Swipeable image/video cards
- ✅ Targeted based on their shop's category
- ✅ Smooth infinite scroll experience

---

## 📊 Ad Limits by Subscription Plan

| Plan | Ads Per Month | Priority | Features |
|------|---------------|----------|----------|
| **Free** | 1 | Low | Basic targeting |
| **Basic** | 2 | Medium | Category targeting |
| **Premium** | 5 | High | Full targeting + analytics |
| **Enterprise** | 10 | Highest | Everything + priority placement |

---

## 📡 API Endpoints

### Base Path: `/api`

---

## 1. Ads Feed (Deals Tab) 📱

**Purpose:** Get personalized ad feed for mobile app

**Endpoint:** `GET /api/ads/feed`

**Query Parameters:**
- `shopId` (optional) - User's current shop UUID
- `perPage` (optional) - Items per page (default: 20)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "ads": [
      {
        "id": "uuid",
        "shopId": "uuid",
        "shop": {
          "id": "uuid",
          "name": "MegaMart Suppliers",
          "imageUrl": "https://..."
        },
        "title": "50% Off All Electronics!",
        "description": "Limited time offer on laptops, phones, and accessories",
        "imageUrl": "https://...",
        "videoUrl": null,
        "mediaType": "image",
        "ctaText": "Shop Now",
        "ctaUrl": "https://shop.com/deals",
        "adType": {
          "value": "card",
          "label": "Card Ad",
          "description": "Card format integrated in content feed"
        },
        "placement": {
          "value": "home",
          "label": "Home Screen"
        },
        "priority": 10,
        "isLive": true,
        "analytics": {
          "viewCount": 1250,
          "clickCount": 85,
          "ctr": 6.8
        },
        "startsAt": "2025-11-01T00:00:00Z",
        "expiresAt": "2025-11-30T23:59:59Z"
      }
    ],
    "pagination": {
      "total": 45,
      "currentPage": 1,
      "lastPage": 3,
      "perPage": 20
    },
    "shopInfo": {
      "id": "uuid",
      "name": "My Shop",
      "businessType": "retail"
    }
  }
}
```

---

## 2. Get All Ads

**Endpoint:** `GET /api/shops/{shopId}/ads`

**Query Parameters:**
- `status` - Filter by status (draft, pending, approved, rejected, paused, completed)
- `placement` - Filter by placement (home, products, sales, reports, all)
- `isActive` - Filter active/inactive
- `search` - Search in title/description
- `sortBy` - Sort field (default: created_at)
- `sortDirection` - asc/desc (default: desc)
- `perPage` - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "ads": [...],
    "pagination": {...}
  }
}
```

---

## 3. Create Ad

**Endpoint:** `POST /api/shops/{shopId}/ads`

**Request Body:**
```json
{
  "title": "Black Friday Sale - 70% Off!",
  "description": "Biggest sale of the year on all products",
  "imageUrl": "https://cdn.example.com/ad-image.jpg",
  "videoUrl": null,
  "mediaType": "image",
  "ctaText": "Shop Now",
  "ctaUrl": "https://myshop.com/sale",
  
  "targetCategories": ["category-uuid-1", "category-uuid-2"],
  "targetShopTypes": ["retail", "wholesale"],
  "targetLocation": "Dar es Salaam",
  "targetAll": false,
  
  "adType": "card",
  "placement": "home",
  "priority": 5,
  
  "startsAt": "2025-11-10T00:00:00Z",
  "expiresAt": "2025-11-30T23:59:59Z",
  
  "budget": 50000,
  "costPerClick": 100,
  
  "notes": "Black Friday campaign"
}
```

**Validation:**
- `title` - required, max 255
- `description` - required, max 1000
- `imageUrl` or `videoUrl` - at least one required
- `mediaType` - required, "image" or "video"
- `adType` - required, "banner", "card", "popup", or "native"
- `placement` - required, "home", "products", "sales", "reports", or "all"
- `startsAt` - required, date, today or future
- `expiresAt` - required, date, after startsAt

**Response:**
```json
{
  "success": true,
  "code": 201,
  "message": "Ad created successfully.",
  "data": {
    "id": "uuid",
    "title": "Black Friday Sale - 70% Off!",
    "status": {
      "value": "pending",
      "label": "Pending Approval",
      "color": "yellow"
    },
    ...
  }
}
```

**Error - Monthly Limit Reached:**
```json
{
  "success": false,
  "code": 403,
  "message": "Monthly ad limit reached. Your plan allows 2 ad(s) per month.",
  "data": {
    "currentAds": 2,
    "limit": 2
  }
}
```

---

## 4. Track Ad View

**Endpoint:** `POST /api/shops/{shopId}/ads/{adId}/view`

**Purpose:** Track when user views an ad

**Request Body:**
```json
{
  "shopId": "viewer-shop-uuid",
  "deviceType": "mobile",
  "platform": "android",
  "viewDuration": 5
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "View tracked successfully."
}
```

**Tracking Logic:**
- Unique view: Counted if user hasn't viewed same ad in last hour
- Creates view record with device/platform info
- Updates ad view counters
- Updates daily performance stats

---

## 5. Track Ad Click

**Endpoint:** `POST /api/shops/{shopId}/ads/{adId}/click`

**Purpose:** Track when user clicks on ad

**Request Body:**
```json
{
  "shopId": "viewer-shop-uuid",
  "deviceType": "mobile",
  "platform": "android",
  "clickLocation": "home"
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Click tracked successfully.",
  "data": {
    "ctaUrl": "https://shop.com/deals"
  }
}
```

**Tracking Logic:**
- Unique click: Counted if user hasn't clicked same ad in last hour
- Creates click record
- Updates ad click counters
- Deducts cost-per-click from budget
- Updates CTR (Click-Through Rate)
- Updates daily performance stats

---

## 6. Get Ad Analytics

**Endpoint:** `GET /api/shops/{shopId}/ads/{adId}/analytics`

**Query Parameters:**
- `period` - week, month, or all (default: week)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "overview": {
      "totalViews": 5230,
      "uniqueViews": 3450,
      "totalClicks": 387,
      "uniqueClicks": 298,
      "ctr": 7.4,
      "totalSpent": 38700,
      "remainingBudget": 11300
    },
    "dailyPerformance": [
      {
        "date": "2025-11-07",
        "views": 450,
        "uniqueViews": 320,
        "clicks": 35,
        "uniqueClicks": 28,
        "conversions": 3,
        "ctr": 7.78,
        "cost": 3500
      },
      ...
    ],
    "conversions": [
      {
        "type": "visit",
        "count": 45,
        "totalValue": null
      },
      {
        "type": "purchase",
        "count": 12,
        "totalValue": 450000
      }
    ],
    "demographics": {
      "devices": [
        {"type": "mobile", "count": 3890},
        {"type": "tablet", "count": 890},
        {"type": "desktop", "count": 450}
      ],
      "platforms": [
        {"platform": "android", "count": 3200},
        {"platform": "ios", "count": 1800},
        {"platform": "web", "count": 230}
      ]
    }
  }
}
```

---

## 7. Update Ad

**Endpoint:** `PUT /api/shops/{shopId}/ads/{adId}`

**Request Body:** (all fields optional)
```json
{
  "title": "Updated Title",
  "isActive": false,
  "priority": 8
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Ad updated successfully.",
  "data": {...}
}
```

---

## 8. Delete Ad

**Endpoint:** `DELETE /api/shops/{shopId}/ads/{adId}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Ad deleted successfully."
}
```

---

## 9. Approve Ad (Admin Only)

**Endpoint:** `POST /api/shops/{shopId}/ads/{adId}/approve`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Ad approved successfully.",
  "data": {
    "status": {
      "value": "approved",
      "label": "Approved",
      "color": "green"
    },
    "approvedBy": {
      "id": "admin-uuid",
      "name": "Admin User"
    },
    "approvedAt": "2025-11-07T10:30:00Z"
  }
}
```

---

## 10. Reject Ad (Admin Only)

**Endpoint:** `POST /api/shops/{shopId}/ads/{adId}/reject`

**Request Body:**
```json
{
  "reason": "Inappropriate content"
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Ad rejected.",
  "data": {
    "status": {
      "value": "rejected",
      "label": "Rejected",
      "color": "red"
    },
    "rejectionReason": "Inappropriate content"
  }
}
```

---

## 11. Pause/Unpause Ad

**Endpoint:** `POST /api/shops/{shopId}/ads/{adId}/toggle-pause`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Ad paused.",
  "data": {...}
}
```

---

## 📱 Mobile App Integration (Deals Tab)

### UI Design

```
┌─────────────────────────────────────┐
│  📱 Deals Tab                       │
├─────────────────────────────────────┤
│                                     │
│  ┌───────────────────────────────┐ │
│  │  🖼️ [AD IMAGE/VIDEO]          │ │
│  │                               │ │
│  │  Black Friday Sale!           │ │
│  │  70% off all electronics      │ │
│  │                               │ │
│  │  [Shop Now →]                 │ │
│  │                               │ │
│  │  👁️ 1.2K views · 💳 85 clicks│ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌───────────────────────────────┐ │
│  │  🎥 [VIDEO AD]                │ │
│  │                               │ │
│  │  New Product Launch           │ │
│  │  Get 50% off today only       │ │
│  │                               │ │
│  │  [Learn More →]               │ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌───────────────────────────────┐ │
│  │  🖼️ [AD IMAGE]                │ │
│  │  ...                          │ │
│  └───────────────────────────────┘ │
│                                     │
│  [Loading more...]                  │
└─────────────────────────────────────┘
```

### Kotlin Example

```kotlin
// Data models
data class AdsResponse(
    val success: Boolean,
    val code: Int,
    val data: AdsData
)

data class AdsData(
    val ads: List<Ad>,
    val pagination: Pagination
)

data class Ad(
    val id: String,
    val title: String,
    val description: String,
    val imageUrl: String?,
    val videoUrl: String?,
    val mediaType: String, // "image" or "video"
    val ctaText: String,
    val ctaUrl: String?,
    val analytics: AdAnalytics
)

data class AdAnalytics(
    val viewCount: Int,
    val clickCount: Int,
    val ctr: Double
)

// Get ads feed
suspend fun getAdsFeed(shopId: String): List<Ad> {
    val response = api.get<AdsResponse>("/api/ads/feed?shopId=$shopId")
    return response.data.ads
}

// Track view
suspend fun trackAdView(adId: String, shopId: String, viewDuration: Int) {
    api.post("/api/shops/$shopId/ads/$adId/view") {
        body = mapOf(
            "shopId" to shopId,
            "deviceType" to "mobile",
            "platform" to "android",
            "viewDuration" to viewDuration
        )
    }
}

// Track click
suspend fun trackAdClick(adId: String, shopId: String) {
    val response = api.post<TrackClickResponse>(
        "/api/shops/$shopId/ads/$adId/click"
    ) {
        body = mapOf(
            "shopId" to shopId,
            "deviceType" to "mobile",
            "platform" to "android",
            "clickLocation" to "deals_tab"
        )
    }
    
    // Open CTA URL
    response.data.ctaUrl?.let { url ->
        openUrl(url)
    }
}

// UI Implementation
@Composable
fun DealsTabScreen() {
    val ads by viewModel.ads.collectAsState()
    
    LazyColumn {
        items(ads) { ad ->
            AdCard(
                ad = ad,
                onView = { viewModel.trackView(ad.id) },
                onClick = { viewModel.trackClick(ad.id) }
            )
        }
    }
}

@Composable
fun AdCard(ad: Ad, onView: () -> Unit, onClick: () -> Unit) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(16.dp)
            .clickable { onClick() }
    ) {
        Column {
            // Image or Video
            if (ad.mediaType == "image") {
                AsyncImage(
                    model = ad.imageUrl,
                    contentDescription = ad.title
                )
            } else {
                VideoPlayer(url = ad.videoUrl)
            }
            
            // Content
            Column(modifier = Modifier.padding(16.dp)) {
                Text(ad.title, style = MaterialTheme.typography.h6)
                Text(ad.description)
                
                Button(onClick = onClick) {
                    Text(ad.ctaText)
                }
                
                // Stats
                Row {
                    Text("👁️ ${ad.analytics.viewCount} views")
                    Spacer(modifier = Modifier.width(16.dp))
                    Text("💳 ${ad.analytics.clickCount} clicks")
                }
            }
        }
    }
    
    // Track view when visible
    LaunchedEffect(ad.id) {
        onView()
    }
}
```

---

## 📊 Analytics Tracking

### Metrics Collected

1. **Views**
   - Total views
   - Unique views (per user per hour)
   - View duration
   - Device type (mobile/tablet/desktop)
   - Platform (Android/iOS/Web)

2. **Clicks**
   - Total clicks
   - Unique clicks (per user per hour)
   - Click location (which screen)
   - Click-through rate (CTR)

3. **Conversions**
   - Visit conversions
   - Call conversions
   - Message conversions
   - Purchase conversions
   - Conversion value

4. **Performance**
   - Daily aggregates
   - Cost tracking
   - Budget monitoring
   - Engagement rate

---

## 🎯 Ad Targeting

### Targeting Options

1. **By Category**
   ```json
   {
     "targetCategories": ["electronics", "phones"]
   }
   ```
   Shows to shops selling these categories

2. **By Shop Type**
   ```json
   {
     "targetShopTypes": ["retail", "wholesale"]
   }
   ```
   Shows to specific business types

3. **By Location**
   ```json
   {
     "targetLocation": "Dar es Salaam"
   }
   ```
   Geographic targeting

4. **All Users**
   ```json
   {
     "targetAll": true
   }
   ```
   Shows to everyone

---

## 🔐 Permissions & Access

### Shop Owners
- Create ads (within subscription limits)
- View own ads
- Edit own ads
- Delete own ads
- View own analytics

### Administrators
- Create unlimited ads
- View all ads
- Approve/reject ads
- Edit any ad
- Delete any ad
- View all analytics

---

## 💰 Budget & Billing

### Cost Per Click (CPC)
```json
{
  "budget": 50000,
  "costPerClick": 100
}
```

- Each click deducts from budget
- Ad pauses when budget exhausted
- Real-time budget tracking

### Budget Monitoring
```json
{
  "budget": 50000,
  "totalSpent": 38700,
  "remainingBudget": 11300
}
```

---

## ✅ Ad Approval Workflow

```
Shop Creates Ad
      ↓
Status: PENDING
      ↓
Admin Reviews
      ↓
   ┌──────┴──────┐
   ↓             ↓
APPROVED      REJECTED
   ↓             ↓
Goes Live    Notification
```

---

## 📁 Files Created

### Models (6)
1. `app/Models/Ad.php`
2. `app/Models/AdView.php`
3. `app/Models/AdClick.php`
4. `app/Models/AdConversion.php`
5. `app/Models/AdReport.php`
6. `app/Models/AdPerformanceDaily.php`

### Enums (3)
1. `app/Enums/AdStatus.php`
2. `app/Enums/AdType.php`
3. `app/Enums/AdPlacement.php`

### Controllers (1)
1. `app/Http/Controllers/Api/AdController.php`

### Requests (4)
1. `app/Http/Requests/StoreAdRequest.php`
2. `app/Http/Requests/UpdateAdRequest.php`
3. `app/Http/Requests/TrackAdViewRequest.php`
4. `app/Http/Requests/TrackAdClickRequest.php`

### Resources (1)
1. `app/Http/Resources/AdResource.php`

### Migrations (1)
1. `database/migrations/*_create_ads_tables.php`

---

## 🚀 Status

✅ **COMPLETE & PRODUCTION READY**

All features implemented:
- Ad creation with subscription limits
- Admin ad management
- Personalized feed
- Complete analytics
- Image & video support
- Real-time tracking
- Budget monitoring
- Approval workflow

---

**Implementation Date:** November 7, 2025  
**Status:** ✅ Production Ready  
**API Standard:** CamelCase (Kotlin Compatible)

