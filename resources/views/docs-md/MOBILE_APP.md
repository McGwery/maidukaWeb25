# MAIDUKA MULTI-SHOP MANAGEMENT ANDROID APP
## Professional Development Specification (Kotlin + Jetpack Compose)

---

## PROJECT OVERVIEW
Create a **production-grade Android mobile application** for the Maiduka Multi-Shop Management System using:
- **Language**: Kotlin
- **UI Framework**: Jetpack Compose
- **Architecture**: MVVM + Clean Architecture
- **Min API Level**: 24 (Android 7.0)
- **Target API Level**: 34 (Android 14)

---

## 📱 APPLICATION CORE FEATURES

### 1. **AUTHENTICATION MODULE**
**Endpoints Integration**:
- `POST /auth/register` - User registration with phone number
- `POST /auth/verify-phone` - Phone number verification (OTP)
- `POST /auth/login` - Password-based login
- `POST /auth/login/otp/request` - Request login OTP
- `POST /auth/login/otp/verify` - OTP-based login
- `POST /auth/password/reset/request` - Request password reset OTP
- `POST /auth/password/reset` - Reset password with OTP
- `POST /auth/logout` - Logout with token revocation

**Features**:
- Phone number + OTP verification system
- Biometric authentication (Fingerprint/Face ID)
- Secure token management (Sanctum)
- Session persistence with encrypted SharedPreferences
- Auto-logout on token expiration
- Deep linking support for verification links

**UI Components**:
- Phone input with country code selector
- OTP input fields (auto-fill capability)
- Password strength indicator
- Biometric toggle option
- Remember me functionality

---

### 2. **SHOP MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops` - List all user shops
- `POST /shops` - Create new shop
- `GET /shops/{shop}` - Get shop details
- `PUT /shops/{shop}` - Update shop information
- `DELETE /shops/{shop}` - Delete shop
- `POST /shops/{shop}/switch` - Switch active shop
- `POST /shops/{shop}/active` - Set shop as active

**Features**:
- Multi-shop support with quick switcher
- Shop profile management (name, location, contact info)
- Shop logo/image upload with compression
- Shop status indicators (active, inactive)
- Bottom sheet shop selector
- Shop analytics overview

**UI Components**:
- Shop card with quick actions
- FAB for creating new shop
- Shop switcher bottom sheet
- Shop profile editor screen
- Shop list with search and filters

---

### 3. **PRODUCT MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/products` - List products
- `POST /shops/{shop}/products` - Create product
- `GET /shops/{shop}/products/{product}` - Get product details
- `PUT /shops/{shop}/products/{product}` - Update product
- `DELETE /shops/{shop}/products/{product}` - Delete product
- `PATCH /shops/{shop}/products/{product}/stock` - Update stock
- `GET /shops/{shop}/products/{product}/adjustments` - Stock history
- `GET /shops/{shop}/inventory/analysis` - Inventory analytics
- `GET /shops/{shop}/inventory/adjustments` - Adjustment summary

**Features**:
- Product CRUD operations with image upload
- Barcode scanning for products
- Stock management with auto-alerts (low stock warnings)
- Stock adjustment history with timestamps
- Inventory analytics dashboard (stock value, turnover rate)
- Product categorization
- Price management (cost, selling, wholesale)
- Bulk operations (import/export CSV)

**UI Components**:
- Product list with infinite scroll
- Product card with quick actions
- Product detail editor
- Barcode scanner integration (ML Kit)
- Stock adjustment dialog
- Inventory dashboard with charts
- Low stock alert notifications

---

### 4. **CATEGORY MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops/categories/ctx` - Get categories for current shop context

**Features**:
- Product categorization
- Category filtering in product lists
- Custom category creation (admin)
- Category icons/colors

---

### 5. **POINT OF SALE (POS) MODULE**
**Endpoints Integration**:
- `POST /shops/{shop}/pos/sales` - Complete sale transaction
- `GET /shops/{shop}/pos/sales` - Get sales history
- `GET /shops/{shop}/pos/sales/{sale}` - Get sale details
- `GET /shops/{shop}/pos/analytics` - Sales analytics
- `POST /shops/{shop}/pos/sales/{sale}/refund` - Process refund
- `POST /shops/{shop}/pos/sales/{sale}/payments` - Add payment
- `GET /shops/{shop}/customers` - List customers
- `POST /shops/{shop}/customers` - Create customer
- `GET /shops/{shop}/customers/{customer}` - Get customer details
- `PUT /shops/{shop}/customers/{customer}` - Update customer
- `DELETE /shops/{shop}/customers/{customer}` - Delete customer

**Features**:
- Real-time sales transaction processing
- Shopping cart with product search
- Multiple payment methods (Cash, Card, Mobile Money)
- Customer management and loyalty tracking
- Receipt generation (PDF/print)
- Sales refund/return management
- Payment splitting
- Discount application (percentage/fixed)
- Sales analytics with charts (Daily, Weekly, Monthly)
- Offline mode with sync capabilities

**UI Components**:
- POS checkout screen (shopping cart)
- Product quick search
- Payment method selector
- Receipt preview/share
- Sales history list
- Customer quick select
- Analytics dashboard with graphs
- Refund dialog

---

### 6. **PURCHASE ORDER MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/purchase-orders/buyer` - Orders created by user
- `GET /shops/{shop}/purchase-orders/seller` - Orders from suppliers
- `POST /shops/{shop}/purchase-orders` - Create PO
- `GET /shops/{shop}/purchase-orders/{purchaseOrder}` - Get PO details
- `PUT /shops/{shop}/purchase-orders/{purchaseOrder}` - Update PO
- `DELETE /shops/{shop}/purchase-orders/{purchaseOrder}` - Cancel PO
- `PATCH /shops/{shop}/purchase-orders/{purchaseOrder}/status` - Update status
- `POST /shops/{shop}/purchase-orders/{purchaseOrder}/payments` - Record payment
- `POST /shops/{shop}/purchase-orders/{purchaseOrder}/transfer-stock` - Transfer stock

**Features**:
- Create and track purchase orders
- Buyer and Seller dual view
- PO status tracking (Pending, Confirmed, Delivered, Completed)
- Payment tracking for POs
- Stock transfer on PO completion
- PO history and analytics
- Supplier management

**UI Components**:
- PO list (buyer/seller tabs)
- PO creation form
- PO detail view with timeline
- Payment record dialog
- Status update interface

---

### 7. **EXPENSE MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/expenses` - List expenses
- `POST /shops/{shop}/expenses` - Create expense
- `GET /shops/{shop}/expenses/summary` - Expense summary
- `GET /shops/{shop}/expenses/categories` - Expense categories
- `GET /shops/{shop}/expenses/{expense}` - Get expense details
- `PUT /shops/{shop}/expenses/{expense}` - Update expense
- `DELETE /shops/{shop}/expenses/{expense}` - Delete expense

**Features**:
- Expense logging with categorization
- Receipt attachment (photo/document)
- Expense summary dashboard
- Category-wise breakdown
- Period-based filtering
- Budget tracking against limits

**UI Components**:
- Expense list with filters
- Expense entry form with receipt camera
- Category selector
- Summary dashboard
- Expense detail view

---

### 8. **TEAM MEMBER MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/members` - List shop members
- `POST /shops/{shop}/members` - Add member
- `GET /shops/{shop}/members/{member}` - Get member details
- `PUT /shops/{shop}/members/{member}` - Update member
- `DELETE /shops/{shop}/members/{member}` - Remove member

**Features**:
- Team member management
- Role-based permissions
- Activity logging per member
- Commission/salary tracking
- Member performance metrics

**UI Components**:
- Members list
- Member profile editor
- Role assignment dialog
- Member activity timeline

---

### 9. **REPORTING & ANALYTICS MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/reports/overview` - Business overview
- `GET /shops/{shop}/reports/sales` - Sales reports
- `GET /shops/{shop}/reports/products` - Product performance
- `GET /shops/{shop}/reports/financial` - Financial reports
- `GET /shops/{shop}/reports/employees` - Employee reports

**Features**:
- Comprehensive business dashboards
- Revenue analytics with graphs
- Product performance reports
- Financial health indicators
- Employee productivity metrics
- Custom date range filtering
- Export reports (PDF/CSV)

**UI Components**:
- Dashboard with KPI cards
- Line/Bar/Pie charts
- Report screens per category
- Date range picker
- Export button

---

### 10. **SUBSCRIPTION MANAGEMENT MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/subscriptions` - List subscriptions
- `GET /shops/{shop}/subscriptions/current` - Active subscription
- `GET /shops/{shop}/subscriptions/statistics` - Stats
- `POST /shops/{shop}/subscriptions` - Create subscription
- `GET /shops/{shop}/subscriptions/{subscription}` - Get details
- `PUT /shops/{shop}/subscriptions/{subscription}` - Update subscription
- `POST /shops/{shop}/subscriptions/{subscription}/cancel` - Cancel
- `POST /shops/{shop}/subscriptions/{subscription}/renew` - Renew
- `POST /shops/{shop}/subscriptions/{subscription}/suspend` - Suspend
- `POST /shops/{shop}/subscriptions/{subscription}/activate` - Activate
- `GET /subscription/plans` - Get available plans

**Features**:
- Subscription plan browsing
- Active subscription management
- Plan upgrade/downgrade
- Payment history
- Renewal reminders
- Cancellation workflows

**UI Components**:
- Plans showcase screen
- Subscription status card
- Plan comparison dialog
- Payment history list

---

### 11. **SAVINGS & GOALS MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/savings/settings` - Get settings
- `PUT /shops/{shop}/savings/settings` - Update settings
- `POST /shops/{shop}/savings/deposit` - Deposit money
- `POST /shops/{shop}/savings/withdraw` - Withdraw money
- `GET /shops/{shop}/savings/transactions` - Transaction history
- `GET /shops/{shop}/savings/summary` - Savings summary
- `GET /shops/{shop}/savings/goals` - List goals
- `POST /shops/{shop}/savings/goals` - Create goal
- `PUT /shops/{shop}/savings/goals/{goal}` - Update goal
- `DELETE /shops/{shop}/savings/goals/{goal}` - Delete goal

**Features**:
- Savings account management
- Deposit/withdraw tracking
- Savings goals with progress tracking
- Automatic savings rules
- Transaction history

**UI Components**:
- Savings dashboard
- Deposit/Withdraw forms
- Transaction list
- Goals overview with progress bars
- Goal detail screen

---

### 12. **SHOP SETTINGS MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/settings` - Get settings
- `PUT /shops/{shop}/settings` - Update settings
- `POST /shops/{shop}/settings/reset` - Reset settings
- `GET /settings/categories` - Settings categories

**Features**:
- Shop configuration
- Business hours setup
- Currency settings
- Tax configuration
- Notification preferences

**UI Components**:
- Settings screens by category
- Toggle switches for preferences
- Time pickers for business hours
- Currency selector

---

### 13. **CHAT & MESSAGING MODULE**
**Endpoints Integration**:
- `GET /shops/{shop}/chat/conversations` - List conversations
- `GET /shops/{shop}/chat/conversations/{conversation}` - Get conversation
- `POST /shops/{shop}/chat/conversations/{conversation}/archive` - Archive
- `GET /shops/{shop}/chat/conversations/{conversation}/messages` - Get messages
- `POST /shops/{shop}/chat/messages` - Send message
- `DELETE /shops/{shop}/chat/conversations/{conversation}/messages/{message}` - Delete message
- `POST /shops/{shop}/chat/conversations/{conversation}/mark-read` - Mark read
- `POST /shops/{shop}/chat/conversations/{conversation}/typing/start` - Start typing
- `POST /shops/{shop}/chat/conversations/{conversation}/typing/stop` - Stop typing
- `GET /shops/{shop}/chat/conversations/{conversation}/typing` - Get typing status
- `POST /shops/{shop}/chat/conversations/{conversation}/messages/{message}/react` - React
- `DELETE /shops/{shop}/chat/conversations/{conversation}/messages/{message}/react` - Remove reaction
- `POST /shops/{shop}/chat/block` - Block shop
- `POST /shops/{shop}/chat/unblock` - Unblock shop
- `GET /shops/{shop}/chat/blocked` - Get blocked shops
- `GET /shops/{shop}/chat/unread-count` - Unread count
- `GET /shops/{shop}/chat/statistics` - Chat statistics
- `GET /shops/{shop}/chat/search-shops` - Search shops

**Features**:
- Real-time messaging with WebSocket
- Typing indicators
- Message reactions (emoji)
- Conversation archiving
- Block/unblock functionality
- Unread message counters
- Message search
- Chat notifications with sound/vibration

**UI Components**:
- Conversations list
- Chat screen with bubble UI
- Typing indicator animation
- Message options menu
- Block confirmation dialog
- Emoji picker for reactions

---

### 14. **ADVERTISEMENTS & PROMOTIONS MODULE**
**Endpoints Integration**:
- `GET /manage/ads` - List ads
- `POST /manage/ads` - Create ad
- `GET /manage/ads/{ad}` - Get ad details
- `PUT /manage/ads/{ad}` - Update ad
- `DELETE /manage/ads/{ad}` - Delete ad
- `POST /manage/ads/{ad}/view` - Track view
- `POST /manage/ads/{ad}/click` - Track click
- `GET /manage/ads/{ad}/analytics` - Ad analytics
- `POST /manage/ads/{ad}/approve` - Approve (admin)
- `POST /manage/ads/{ad}/reject` - Reject (admin)
- `POST /manage/ads/{ad}/toggle-pause` - Pause/Resume
- `GET /ads/feed` - Get ads feed

**Features**:
- Create and manage promotional ads
- Ad performance analytics (views, clicks, CTR)
- Ads feed for browsing deals
- Ad pause/resume
- Admin approval workflow

**UI Components**:
- Ads management list
- Ad creation form with image upload
- Analytics dashboard
- Ads feed screen
- Ad detail view

---

## 🏗️ TECHNICAL ARCHITECTURE

### **Project Structure**
