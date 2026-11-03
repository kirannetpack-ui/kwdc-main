# KWDC Database Quick Reference Guide

## 📋 Table Index

### Authentication & Users (5 tables)
- `users` - User profiles with roles and status
- `user_contacts` - Emergency and reference contacts
- `password_reset_tokens` - Password reset functionality
- `sessions` - Active user sessions
- `personal_access_tokens` - API authentication tokens

### Dispatch & Logistics (5 tables)
- `dispatch_orders` - Main dispatch orders
- `dispatch_items` - Items within dispatch orders
- `pickup_requests` - Pickup service requests
- `pickup_stops` - Multiple stops per pickup request
- `delivery_stops` - Delivery progress tracking

### Warehouse & Inventory (5 tables)
- `warehouses` - Warehouse facility records
- `warehouse_requests` - Warehouse storage requests
- `warehouse_photos` - Warehouse documentation photos
- `stocks` - Inventory items in warehouses
- `boxes` - Storage containers/boxes

### Equipment Rental (3 tables)
- `equipment` - Equipment catalog
- `equipment_requests` - Rental requests
- `equipment_jobs` - Active rental assignments

### Financial Management (6 tables)
- `invoices` - Billing documents
- `transactions` - Payment records
- `driver_rates` - Driver pricing tiers
- `agency_rates` - Security agency rates
- `margin_tiers` - Admin commission structure
- `partner_earnings` - Driver/partner income tracking

### Security Services (5 tables)
- `security_agencies` - Security agency profiles
- `security_personnel` - Security staff records
- `security_assignments` - Service assignments
- `security_incidents` - Incident logging
- `security_goods` - Items under security protection

### Loader Management (4 tables)
- `loaders` - Loader profiles
- `loader_managers` - Manager assignments
- `loader_assignments` - Work assignments
- `loader_job_logs` - Time tracking and hours

### Location Services (1 table)
- `kataho_locations` - Kataho code and location mapping

### Communication (1 table)
- `conversation_sessions` - Chat/conversation sessions

### Notifications (1 table)
- `notifications` - System notifications

### Business Entities (2 tables)
- `proposals` - Business proposals
- `auctions` - Auction records
- `insurance` - Insurance records
- `vehicles` - Vehicle records
- `partner_proposals` - Partner proposals/bids
- `partner_job_offers` - Job offers to partners
- `order_items` - Items in orders

---

## 🔑 Key Relationships

### User-Centric Relationships
```
users
├── user_contacts (one-to-many)
├── dispatch_orders (as creator)
├── pickup_requests (as client or driver)
├── warehouse_requests (as client)
├── equipment (as owner)
├── equipment_requests (as client)
├── driver_rates (as driver)
├── invoices (as user or client)
├── transactions (as user)
├── security_agencies (as user)
├── security_personnel (as user)
├── loaders (implicit)
├── notifications (polymorphic)
└── conversation_sessions
```

### Order-Centric Relationships
```
pickup_requests
├── pickup_stops (one-to-many)
├── user (client)
├── user (driver)
├── warehouse
└── invoices
```

### Financial Relationships
```
invoices
├── transactions (one-to-many)
├── user
├── client
├── warehouse_request
└── items (JSON)

transactions
└── invoice
```

### Equipment Relationships
```
equipment
├── equipment_requests (one-to-many)
├── equipment_jobs (one-to-many)
├── user (owner)
└── dispatch_items (indirect)
```

### Security Relationships
```
security_agencies
├── security_personnel (one-to-many)
├── security_assignments (one-to-many)
├── agency_rates (one-to-many)
└── user
```

---

## 📊 Field Reference by Table

### users
```sql
id, user_code, name, email, password, phone, role, is_active, 
avg_rating, address, profile_photo, is_admin, is_driver, 
is_equipment_owner, user_type, preferred_location, 
remember_token, created_at, updated_at
```
**Indexes:** role, email, phone

### pickup_requests
```sql
id, client_id, driver_id, warehouse_id, tracking_id, invoice_no,
total_distance, total_price, tax_amount, grand_total, bill_type,
pan_number, payment_status, payment_due_date, status, assigned_at,
picked_up_at, delivered_at, started_at, completed_at, cancelled_at,
admin_margin, notes, created_at, updated_at
```
**Indexes:** client_id, driver_id, status, tracking_id

### dispatch_orders
```sql
id, warehouse_id, client_id, driver_id, order_number, status,
total_distance, total_price, admin_margin, tax_amount, grand_total,
payment_status, assigned_at, started_at, completed_at, cancelled_at,
created_at, updated_at
```
**Indexes:** warehouse_id, client_id, driver_id, status

### invoices
```sql
id, user_id, client_id, warehouse_request_id, invoice_number,
order_type, order_id, amount, subtotal, discount, tax_rate,
tax_amount, grand_total, billing_type, pan_number, billing_address,
items (JSON), status, payment_status, due_date, payment_due_date,
paid_at, payment_method, qr_code, description, notes,
created_at, updated_at
```
**Indexes:** invoice_number, status, payment_status

### equipment
```sql
id, user_id, owner_id, name, type, model, year, description,
weight, engine_power, bucket_capacity, max_reach, daily_rate,
weekly_rate, monthly_rate, security_deposit, location, status,
front_photo, side_photo, working_photo, registration_doc,
insurance_doc, created_at, updated_at
```
**Indexes:** user_id, status

### security_agencies
```sql
id, user_id, agency_name, registration_number, license_number,
address, phone, emergency_phone, email, services_offered,
year_established, pan_vat_number, certifications, logo, status,
approved_at, is_verified, admin_notes, document_paths (JSON),
created_at, updated_at
```
**Indexes:** user_id, status, is_verified

### loaders
```sql
id, name, phone, address, citizenship_number, dob, photo,
emergency_contact, is_available, rate_per_hour, skills (JSON),
experience_years, status, created_at, updated_at
```
**Indexes:** (none - should add status)

### warehouse_requests
```sql
id, client_id, warehouse_id, total_quantity, price, status,
payment_status, requested_at, approved_at, delivered_at,
returned_at, created_at, updated_at
```
**Indexes:** client_id, warehouse_id, status

---

## 💾 Common Query Patterns

### Get All Pending Pickup Requests for a Client
```sql
SELECT * FROM pickup_requests 
WHERE client_id = ? AND status = 'pending'
ORDER BY created_at DESC;
```

### Get Active Assignments for a Driver
```sql
SELECT * FROM pickup_requests 
WHERE driver_id = ? AND status IN ('assigned', 'in_progress')
ORDER BY assigned_at ASC;
```

### Get Invoice Summary with Transactions
```sql
SELECT i.*, COUNT(t.id) as transaction_count, SUM(t.amount) as paid_amount
FROM invoices i
LEFT JOIN transactions t ON i.id = t.invoice_id AND t.status = 'completed'
WHERE i.client_id = ?
GROUP BY i.id;
```

### Get Driver Earnings for Period
```sql
SELECT 
    d.id,
    d.name,
    COUNT(pr.id) as pickups_completed,
    SUM(pr.grand_total - pr.admin_margin) as earnings
FROM users d
LEFT JOIN pickup_requests pr ON d.id = pr.driver_id 
    AND pr.status = 'completed'
    AND pr.delivered_at BETWEEN ? AND ?
WHERE d.role = 'driver'
GROUP BY d.id;
```

### Get Equipment Utilization Rate
```sql
SELECT 
    e.id,
    e.name,
    COUNT(er.id) as total_requests,
    COUNT(CASE WHEN er.status = 'completed' THEN 1 END) as completed,
    ROUND(COUNT(CASE WHEN er.status = 'completed' THEN 1 END) / 
          COUNT(er.id) * 100, 2) as utilization_rate
FROM equipment e
LEFT JOIN equipment_requests er ON e.id = er.equipment_id
GROUP BY e.id;
```

### Get Outstanding Invoices (Overdue)
```sql
SELECT * FROM invoices
WHERE payment_status = 'unpaid' 
AND due_date < CURDATE()
ORDER BY due_date ASC;
```

### Get Active Loaders for Assignment
```sql
SELECT * FROM loaders
WHERE status = 'active' 
AND is_available = true
ORDER BY rate_per_hour ASC;
```

---

## 🔍 Performance Optimization Tips

### Queries to Optimize (Add Indexes)
1. `SELECT * FROM pickup_requests WHERE client_id = ? AND status = ?`
   - **Add:** `INDEX(client_id, status)`

2. `SELECT * FROM dispatch_orders WHERE driver_id = ? AND status IN (...)`
   - **Add:** `INDEX(driver_id, status)`

3. `SELECT * FROM invoices WHERE payment_status = ? AND due_date < ?`
   - **Add:** `INDEX(payment_status, due_date)`

4. `SELECT * FROM equipment WHERE type = ? AND status = ?`
   - **Add:** `INDEX(type, status)`

### N+1 Query Prevention
Use eager loading in Laravel:
```php
// ❌ Bad (N+1 query):
$pickups = PickupRequest::all();
foreach ($pickups as $pickup) {
    echo $pickup->client->name;  // Query for each pickup!
}

// ✅ Good (Eager load):
$pickups = PickupRequest::with('client', 'driver')->get();
foreach ($pickups as $pickup) {
    echo $pickup->client->name;  // No additional queries
}
```

### Pagination for Large Datasets
```php
// Instead of loading all records
$invoices = Invoice::paginate(15);

// Use cursor pagination for better performance
$invoices = Invoice::orderBy('id')->cursorPaginate(15);
```

---

## 🛡️ Data Integrity Rules

### Status State Machines

#### Pickup Request Status Flow
```
pending → assigned → in_progress → completed
                  ↘              ↗
                   → cancelled ←
```

#### Invoice Payment Status
```
unpaid → paid
  ↓
refunded
```

#### Equipment Request Status
```
pending → approved → delivered → returned → completed
   ↓
rejected
```

---

## 🎯 Critical Fields to Always Check

### High-Impact Fields
- `users.role` - Controls user access and permissions
- `pickup_requests.status` - Drives business workflow
- `invoices.payment_status` - Critical for financial tracking
- `dispatch_orders.assigned_driver_id` - Determines responsibility
- `warehouse_requests.status` - Inventory management

### Financial Calculations
- `invoices.grand_total` = subtotal - discount + tax_amount
- `pickup_requests.total_price` = sum of all stops prices
- `dispatch_orders.admin_margin` = total_price * margin_tier
- `driver_earnings` = grand_total - admin_margin - tax

### Audit Trails to Track
- When status changes
- Who approved/assigned the order
- Payment date and method
- Delivery timestamp
- Cancellation reason

---

## 📈 Database Statistics Query

```sql
-- Get table size information
SELECT 
    TABLE_NAME,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
    TABLE_ROWS,
    AVG_ROW_LENGTH
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'kwdc_db'
ORDER BY (data_length + index_length) DESC;

-- Count records by table
SELECT 
    'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'pickup_requests', COUNT(*) FROM pickup_requests
UNION ALL
SELECT 'dispatch_orders', COUNT(*) FROM dispatch_orders
UNION ALL
SELECT 'invoices', COUNT(*) FROM invoices
ORDER BY count DESC;
```

---

## 🔧 Common Maintenance Tasks

### Update User Role
```php
$user->role = 'driver';
$user->is_driver = true;
$user->save();
```

### Create New Pickup Request
```php
$pickup = PickupRequest::create([
    'client_id' => auth()->id(),
    'tracking_id' => Str::upper(Str::random(8)),
    'status' => 'pending',
    'payment_status' => 'pending',
]);

// Add stops
$pickup->stops()->create([
    'stop_number' => 1,
    'address' => '...',
    'contact_name' => '...',
    'contact_phone' => '...',
]);
```

### Update Invoice Status
```php
$invoice->update([
    'payment_status' => 'paid',
    'paid_at' => now(),
]);
```

### Assign Driver to Order
```php
$pickup->update([
    'driver_id' => $driver->id,
    'status' => 'assigned',
    'assigned_at' => now(),
]);
```

---

## 🎓 Database Best Practices Used

✅ **Foreign Keys:** Most tables have proper referential integrity  
✅ **Indexes:** Common query columns indexed  
✅ **Timestamps:** created_at, updated_at on all tables  
✅ **Soft Deletes:** Could be implemented  
✅ **Status Columns:** Good for workflow tracking  
✅ **Decimal Types:** Correct for financial data  

❌ **To Improve:**  
- Add more compound indexes  
- Implement soft deletes  
- Add audit trails  
- Document JSON structures  
- Standardize status enums  

---

## 📞 Support & Documentation

For detailed information on each table, refer to:
- Individual migration files in `database/migrations/`
- Model files in `app/Models/`
- API documentation in `docs/api/`

---

**Last Updated:** August 14, 2026  
**Database:** MySQL 8.0+  
**ORM:** Laravel Eloquent  
**Tables:** 34 | **Relationships:** 50+
