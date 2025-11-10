# Ads API - Quick Reference Guide

## 🚀 Quick Start

### Authentication
All endpoints require authentication token:
```
Authorization: Bearer {token}
```

---

## 📱 Mobile App - Deals Tab

### Get Ads Feed
```http
GET /api/ads/feed?shopId={shopId}&perPage=20
```

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

### Track View
```http
POST /api/shops/{shopId}/ads/{adId}/view
```
```json
{
  "shopId": "uuid",
  "deviceType": "mobile",
  "platform": "android",
  "viewDuration": 5
}
```

### Track Click
```http
POST /api/shops/{shopId}/ads/{adId}/click
```
```json
{
  "shopId": "uuid",
  "deviceType": "mobile",
  "platform": "android"
}
```

---

## 🏪 Shop Owner - Ad Management

### Create Ad
```http
POST /api/shops/{shopId}/ads
```
```json
{
  "title": "Sale Title",
  "description": "Sale description",
  "imageUrl": "https://...",
  "mediaType": "image",
  "ctaText": "Shop Now",
  "ctaUrl": "https://...",
  "targetCategories": ["uuid1", "uuid2"],
  "adType": "card",
  "placement": "home",
  "startsAt": "2025-11-10T00:00:00Z",
  "expiresAt": "2025-11-30T23:59:59Z",
  "budget": 50000,
  "costPerClick": 100
}
```

### Get My Ads
```http
GET /api/shops/{shopId}/ads?status=approved&perPage=15
```

### Get Analytics
```http
GET /api/shops/{shopId}/ads/{adId}/analytics?period=week
```

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
      "ctr": 7.4,
      "totalSpent": 38700,
      "remainingBudget": 11300
    },
    "dailyPerformance": [...],
    "conversions": [...],
    "demographics": {...}
  }
}
```

### Update Ad
```http
PUT /api/shops/{shopId}/ads/{adId}
```
```json
{
  "title": "Updated Title",
  "isActive": false,
  "priority": 8
}
```

### Delete Ad
```http
DELETE /api/shops/{shopId}/ads/{adId}
```

### Pause/Unpause Ad
```http
POST /api/shops/{shopId}/ads/{adId}/toggle-pause
```

---

## 👨‍💼 Admin - Ad Management

### Approve Ad
```http
POST /api/shops/{shopId}/ads/{adId}/approve
```

### Reject Ad
```http
POST /api/shops/{shopId}/ads/{adId}/reject
```
```json
{
  "reason": "Inappropriate content"
}
```

---

## 📊 Ad Status Flow

```
draft → pending → approved → (paused) → completed
                 ↓
              rejected
```

---

## 🎯 Targeting Options

### By Category
```json
{
  "targetCategories": ["electronics-uuid", "phones-uuid"]
}
```

### By Shop Type
```json
{
  "targetShopTypes": ["retail", "wholesale"]
}
```

### By Location
```json
{
  "targetLocation": "Dar es Salaam"
}
```

### All Users
```json
{
  "targetAll": true
}
```

---

## 💰 Monthly Ad Limits

| Plan | Ads/Month | Priority |
|------|-----------|----------|
| Free | 1 | Low |
| Basic | 2 | Medium |
| Premium | 5 | High |
| Enterprise | 10 | Highest |

---

## 🎨 Ad Types

- **banner** - Top banner ad
- **card** - Card in feed (recommended)
- **popup** - Modal popup
- **native** - Native content ad

---

## 📍 Placement Options

- **home** - Home screen
- **products** - Products screen
- **sales** - Sales/POS screen
- **reports** - Reports screen
- **all** - All screens

---

## 📈 Analytics Metrics

### Overview
- Total views
- Unique views
- Total clicks
- Unique clicks
- CTR (Click-Through Rate)
- Total spent
- Remaining budget

### Daily Performance
- Views per day
- Clicks per day
- Conversions per day
- Cost per day

### Demographics
- Device breakdown (mobile/tablet/desktop)
- Platform breakdown (Android/iOS/Web)

---

## 🔧 Query Parameters

### Get Ads
- `status` - Filter by status
- `placement` - Filter by placement
- `isActive` - Filter active/inactive
- `search` - Search title/description
- `sortBy` - Sort field
- `sortDirection` - asc/desc
- `perPage` - Items per page

### Analytics
- `period` - week/month/all

---

## ✅ Standard Response Format

### Success
```json
{
  "success": true,
  "code": 200,
  "message": "Optional message",
  "data": {...}
}
```

### Error
```json
{
  "success": false,
  "code": 400,
  "message": "Error message",
  "errors": {...}
}
```

---

## 🛡️ Permissions

### Shop Owners
- Create ads (within limits)
- View own ads
- Edit own ads
- Delete own ads
- View own analytics

### Admins
- Create unlimited ads
- View all ads
- Approve/reject ads
- Edit any ad
- Delete any ad
- View all analytics

---

## 🐛 Common Errors

### Monthly Limit Reached
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

### Budget Exhausted
```json
{
  "success": false,
  "code": 400,
  "message": "Ad budget exhausted"
}
```

### Ad Not Found
```json
{
  "success": false,
  "code": 404,
  "message": "Ad not found"
}
```

---

## 📝 Validation Rules

### Create Ad
- `title` - required, max:255
- `description` - required, max:1000
- `imageUrl` or `videoUrl` - at least one required
- `mediaType` - required, in:image,video
- `adType` - required, in:banner,card,popup,native
- `placement` - required, in:home,products,sales,reports,all
- `startsAt` - required, date, today or future
- `expiresAt` - required, date, after startsAt
- `budget` - nullable, numeric, min:0
- `costPerClick` - nullable, numeric, min:0

---

## 🎯 Best Practices

1. **Images**: Use high-quality images (1200x628px recommended)
2. **Videos**: Keep under 30 seconds
3. **CTA**: Use clear, action-oriented text
4. **Targeting**: Be specific for better results
5. **Budget**: Start small and scale based on performance
6. **Timing**: Schedule during peak hours
7. **Analytics**: Review daily performance regularly

---

## 📞 Support

For issues or questions, contact your system administrator.

---

**Last Updated:** November 7, 2025  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

