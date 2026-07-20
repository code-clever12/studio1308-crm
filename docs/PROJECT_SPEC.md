# Complete Salon & Appointment Booking Management Platform
## Full-Stack Laravel Development Specification (USA Single-Location Edition)

> NOTE: Pasted into chat across two messages on 2026-07-21 (first message hit
> the chat's 50,000-character limit mid-way through "STEP 5: Blade Views &
> Frontend"; the remainder — rest of Step 5 through Step 12, timeline, success
> criteria, and recommended packages — was supplied in a follow-up and is now
> merged below). This document is complete.

---

## 📋 Project Overview

You are an expert Full-Stack Laravel Developer with 6+ years of experiance. Build a complete **Salon & Appointment Booking Management Platform** for a **single, independent salon operating in the USA** using Laravel (latest version), Blade, Laravel Breeze for Authentication, and Tailwind CSS.

This is a **single-tenant, single-location salon booking system** where:
- **Salon Owner/Admin** manages their business, staff, services, and appointments
- **Customers** book appointments online and pay deposits
- **All data belongs to ONE salon** (no multi-location or multi-tenant complexity)

---

## 🇺🇸 USA-Specific Features (Built-In)

This specification is **optimized for USA salons** with the following features:

1. **Tipping System** ⭐
   - Customers can tip at checkout (15%, 18%, 20%, custom)
   - Tips tracked per staff member
   - Industry-standard for USA beauty/wellness services

2. **Sales Tax Handling**
   - State-based tax calculation (varies by state)
   - Admin configures salon's state and tax rate
   - Tax applied at checkout and displayed on receipt
   - Tax reports generated for monthly/yearly filing

3. **No-Show Penalties (Credit Card Auto-Charge)**
   - Admin configures no-show fee ($25 or custom amount)
   - If customer doesn't show up → fee auto-charged to card on file
   - Tracking of repeat no-shows with auto-blocking

4. **ACH Bank Transfers for Staff Payouts**
   - Staff provide bank account details (encrypted)
   - Stripe processes ACH transfers directly to bank accounts
   - Combines commission earnings + tips
   - Admin-controlled payout schedule (weekly, bi-weekly, monthly)

5. **Stripe Payment Processing (USA)**
   - Industry-leading payment processor for USA
   - PCI DSS Level 1 compliance (no raw card data stored)
   - Webhook support for payment status updates
   - Built-in refund handling

---

## 🛠️ Tech Stack & Technical Architecture

### Core Framework & Tools
1. **Framework:** Laravel (latest version) with Blade Templating Engine
2. **Authentication & Authorization:**
   - Laravel Breeze for auth (Login, Register, Password Reset)
   - Multi-role authorization system (`admin`/`salon_owner` vs `customer`)
   - Gate/Policy-based access control

3. **Styling & CSS Architecture:**
   - **Tailwind CSS** for UI components
   - **Separate CSS Entry Points (CRITICAL):**
     - `resources/css/app.css` → Customer/Public Views
     - `resources/css/admin.css` → Admin/Dashboard Views
     - Configure both in `vite.config.js`
     - Load via separate master layouts: `layouts/app.blade.php` and `layouts/admin.blade.php`

4. **Database:**
   - MySQL/PostgreSQL with Eloquent ORM
   - Strategic indexing for performance
   - Transaction management for critical operations

5. **Payment Processing (USA):**
   - **Stripe Integration** (primary payment processor for USA)
   - Webhook handling for payment confirmations
   - Secure token management (never store raw card data)
   - PCI DSS Level 1 compliance

6. **Tipping System:**
   - Integrated tipping at checkout (15%, 18%, 20%, custom)
   - Staff tip distribution tracking
   - Tip reporting for admin

7. **Sales Tax Handling:**
   - State-based sales tax calculation (USA)
   - Tax exempt service options
   - Tax remittance reports
   - Configuration per service

8. **ACH Bank Transfers:**
   - Direct deposit for staff commission payouts
   - Admin-controlled payout schedule
   - Bank account verification

9. **Notifications & Messaging:**
   - Laravel Queue for background job processing
   - Email notifications (Mailable + Queue)
   - Optional SMS (Twilio integration for USA)
   - Database notifications for in-app alerts

10. **File Storage:**
   - Local storage for development
   - AWS S3 (or similar) for production
   - Image optimization before storage

11. **Caching:**
   - Redis for caching availability slots
   - Cache invalidation on appointment changes

---

## 👥 User Roles, Dashboards & Navigation

### Role 1: Customer (End User)

#### Navigation & Features
* **Navbar / Dashboard Tabs:**
  - Home / Explore (Browse Salons, Services, Staff, Locations)
  - Search & Filter
  - My Appointments (Upcoming, Past, Cancelled)
  - Bookings & Checkout (Multi-step wizard)
  - Waitlist Status (If joined)
  - Loyalty Points & Rewards
  - Ratings & Reviews History
  - Profile & Account Settings
  - Notifications & Messages

* **Key Features:**
  - **Browse Services:**
    - Filter by location, price range, duration, staff member, rating
    - Search by service name or staff name
    - View service details: pricing, estimated duration, consent form requirements, staff availability
    - See staff photos, bios, and average ratings

  - **Dynamic Time Slot Selection:**
    - Pick a date and see available time slots generated based on:
      - Chosen staff member's working hours
      - Existing appointments and buffer times
      - Staff breaks/lunch hours
      - Service duration + buffer time

  - **Booking Workflow (Multi-step):**
    1. Select Service (with pricing & duration display)
    2. Choose Preferred Staff or "Any Available"
    3. Select Date & Time Slot
    4. Complete Mandatory Consent/Intake Forms
    5. Review & Edit Appointment Details
    6. Pay Deposit (with amount breakdown: deposit vs balance due)
    7. Confirmation & Calendar Invite

  - **Waitlist Option:**
    - If preferred date/staff is fully booked, join waitlist
    - Automated notification when a slot becomes available
    - One-click booking from waitlist notification

  - **Post-Appointment:**
    - Rate & review completed services
    - One-click re-booking for favorite services
    - Loyalty points earned + balance display

  - **Cancellation & Rescheduling:**
    - Cancel with automatic cancellation fee calculation based on cancellation policy
    - Reschedule within allowed timeframe

---

### Role 2: Admin / Salon Owner (Business Management)

#### Sidebar / Dashboard Navigation
* **Overview / Analytics:**
  - Daily/Weekly/Monthly revenue charts
  - Total bookings & conversion rates
  - Active customers count
  - Top-performing services
  - Staff performance metrics
  - Occupancy rates

* **Calendar / Appointments Management:**
  - Real-time grid/calendar view (day, week, month)
  - Drag & drop to reschedule appointments
  - Manual walk-in booking
  - Waitlist management (notify, convert to booking)
  - Appointment status tracking (pending, confirmed, in-progress, completed, cancelled)
  - Bulk appointment operations (reschedule, cancel, mark no-show)

* **Services, Packages & Pricing:**
  - CRUD operations for services
  - Service fields: name, description, price, duration, buffer time, deposit % required, category, consent form requirement
  - Package bundles (e.g., "Bridal Package: Hair + Makeup + Nails")
  - Seasonal pricing/promotions
  - Service images & descriptions

* **Staff Management & Scheduling:**
  - Add/Edit/Delete staff members
  - Set working days (e.g., Mon-Fri, Sat)
  - Set shift timings per day (e.g., 9:00 AM - 6:00 PM)
  - Breaks/Lunch hours management
  - Days off and vacation periods
  - Assign specific services to each staff
  - Set custom commission rates (%) per staff
  - Staff performance dashboard (appointments completed, revenue generated, ratings)
  - Upload staff photos & bios

* **Client / CRM Directory:**
  - Customer database with detailed profiles
  - Booking history per customer
  - Contact notes & follow-ups
  - Loyalty points balance
  - Penalty/block status (for no-shows, late cancellations)
  - Communication history (emails, SMS, notes)

* **Digital Consent / Intake Forms:**
  - Form builder (drag-and-drop or template-based)
  - Form field types: text, email, checkbox, radio, date, file upload
  - Link forms to specific services
  - View form responses per appointment
  - Export form data as PDF

* **Inventory & POS:**
  - Product catalog (retail products, supplies)
  - Stock tracking with low-stock alerts
  - SKU management
  - Sales history
  - Barcode scanning support (optional)

* **Marketing, Promotions & Loyalty:**
  - Coupon/Discount code management
  - Membership packages (monthly, quarterly, annual)
  - Loyalty points system setup (points per $, point redemption value)
  - Email marketing templates & campaigns
  - SMS promotions
  - Referral rewards program

* **Business Settings & Policies:**
  - Salon info (name, logo, address, phone, email, website)
  - Cancellation policy (e.g., free cancellation up to 24 hours, $25 fee after)
  - No-show penalties & credit card auto-charge feature
  - Deposit rules (% of service price or flat amount)
  - Operating hours & holidays (single location)
  - Sales tax configuration by state
  - Notification preferences (email, SMS, push)
  - Timezone (single timezone for salon location)
  - ACH bank account setup for staff payouts
  - Tipping settings (enable/disable, default percentages)

* **Reports & Analytics:**
  - Revenue reports (by date, service, staff)
  - Staff commission reports
  - Customer acquisition & retention metrics
  - Appointment conversion funnel
  - Waitlist conversion rates
  - Export reports as PDF/CSV

---

## 🗄️ Database Architecture & Key Models

### Complete Migrations, Models & Relationships

#### 1. User Model
```
Users Table:
- id (Primary Key)
- name (string)
- email (string, unique)
- phone (string, nullable)
- password (string, hashed)
- role (enum: 'customer', 'admin', 'staff')
- avatar (string, nullable)
- timezone (string, default: 'UTC')
- is_active (boolean, default: true)
- created_at, updated_at

Relationships:
- One-to-Many with Staff (if role = staff)
- One-to-Many with Appointments (if role = customer)
- One-to-Many with Reviews (if role = customer)
```

#### 2. Salon Model (Single Location)
```
Salons Table:
- id (Primary Key)
- owner_id (Foreign Key → Users)
- name (string)
- description (text, nullable)
- address (string)
- city (string)
- state (string) [USA State abbreviation: CA, NY, TX, etc.]
- zip_code (string)
- phone (string)
- email (string)
- website (string, nullable)
- logo (string, nullable)
- timezone (string, default: 'America/New_York')
- opens_at (time)
- closes_at (time)
- cancellation_policy (text)
- deposit_percentage (decimal: default 25)
- no_show_fee (decimal, default: 25) [USD - auto-charge on credit card]
- enable_tips (boolean, default: true)
- sales_tax_rate (decimal, nullable) [State-based tax percentage]
- acct_stripe_connect_id (string, nullable) [For ACH payouts]
- is_active (boolean)
- created_at, updated_at

Relationships:
- One-to-Many with Staff
- One-to-Many with Services
- One-to-Many with Appointments
- One-to-Many with Payments
- One-to-One with ACHBankAccount (optional)
```

#### 4. Category Model
```
Categories Table:
- id (Primary Key)
- name (string)
- slug (string)
- description (text, nullable)
- icon (string, nullable)
- display_order (integer)
- created_at, updated_at

Relationships:
- One-to-Many with Services
```

#### 5. Service Model
```
Services Table:
- id (Primary Key)
- category_id (Foreign Key → Categories)
- name (string)
- description (text, nullable)
- price (decimal) [USD]
- duration_minutes (integer)
- buffer_time_minutes (integer, default: 15)
- deposit_amount (decimal, nullable) [If NULL, use salon default %]
- requires_consent_form (boolean, default: false)
- consent_form_id (Foreign Key → ConsentForms, nullable)
- is_taxable (boolean, default: true) [Apply sales tax?]
- image (string, nullable)
- is_active (boolean, default: true)
- display_order (integer)
- created_at, updated_at

Relationships:
- Many-to-One with Category
- Many-to-One with ConsentForm (optional)
- Many-to-Many with Staff (ServiceStaff pivot)
- One-to-Many with Appointments
```

#### 6. Staff Model
```
Staff Table:
- id (Primary Key)
- user_id (Foreign Key → Users)
- bio (text, nullable)
- photo (string, nullable)
- commission_rate (decimal, default: 20) [% of service price]
- status (enum: 'active', 'inactive', 'on_leave')
- hire_date (date)
- bank_account_routing_number (string, encrypted, nullable) [For ACH]
- bank_account_number (string, encrypted, nullable) [For ACH]
- bank_account_holder_name (string, nullable)
- stripe_connect_account_id (string, nullable) [Stripe Connect for payouts]
- created_at, updated_at

Relationships:
- Many-to-One with User
- Many-to-Many with Services (ServiceStaff pivot)
- One-to-Many with StaffSchedules
- One-to-Many with Appointments
- One-to-Many with Reviews
- One-to-Many with ACHPayouts
```

#### 7. ServiceStaff Pivot Table
```
Service_Staff Table:
- id (Primary Key)
- service_id (Foreign Key → Services)
- staff_id (Foreign Key → Staff)
- is_available (boolean, default: true)
- created_at

Relationships:
- Many-to-Many linking Services & Staff
```

#### 8. StaffSchedule Model
```
Staff_Schedules Table:
- id (Primary Key)
- staff_id (Foreign Key → Staff)
- day_of_week (enum: 0-6 or 'Monday'-'Sunday')
- start_time (time)
- end_time (time)
- break_start (time, nullable)
- break_end (time, nullable)
- is_working_day (boolean, default: true)
- created_at, updated_at

Relationships:
- Many-to-One with Staff
```

#### 9. DayOff Model (For Vacations & Days Off)
```
Days_Off Table:
- id (Primary Key)
- staff_id (Foreign Key → Staff)
- date (date)
- reason (string, nullable) [e.g., 'Vacation', 'Sick Leave']
- created_at

Relationships:
- Many-to-One with Staff
```

#### 10. ConsentForm Model
```
Consent_Forms Table:
- id (Primary Key)
- salon_id (Foreign Key → Salons)
- name (string)
- description (text, nullable)
- fields_json (json) [Structure: [{id, label, type, required, options}]]
- is_active (boolean)
- created_at, updated_at

Relationships:
- Many-to-One with Salon
- One-to-Many with Services
- One-to-Many with AppointmentFormResponses
```

#### 11. Appointment Model
```
Appointments Table:
- id (Primary Key)
- customer_id (Foreign Key → Users)
- staff_id (Foreign Key → Staff, nullable if not selected)
- service_id (Foreign Key → Services)
- appointment_date (date)
- start_time (time)
- end_time (time)
- status (enum: 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show')
- service_price (decimal) [Price at time of booking]
- subtotal (decimal) [Before tax & tip]
- sales_tax_amount (decimal, default: 0)
- deposit_paid (decimal, default: 0)
- deposit_percentage (decimal)
- total_amount (decimal) [Including tax]
- remaining_balance (decimal)
- cancellation_fee (decimal, nullable)
- cancellation_reason (text, nullable)
- notes (text, nullable)
- payment_status (enum: 'pending', 'partial', 'paid', 'refunded')
- stripe_payment_intent_id (string, nullable)
- tip_amount (decimal, default: 0)
- no_show_fee_charged (boolean, default: false)
- reminder_sent (boolean, default: false)
- created_at, updated_at, cancelled_at

Relationships:
- Many-to-One with User (Customer)
- Many-to-One with Staff
- Many-to-One with Service
- One-to-Many with AppointmentFormResponses
- One-to-One with Review (optional)
- One-to-Many with Payments
- One-to-Many with Tips
```

#### 12. AppointmentFormResponse Model
```
Appointment_Form_Responses Table:
- id (Primary Key)
- appointment_id (Foreign Key → Appointments)
- consent_form_id (Foreign Key → ConsentForms)
- form_data_json (json) [Structure: {field_id: answer}]
- created_at, updated_at

Relationships:
- Many-to-One with Appointment
- Many-to-One with ConsentForm
```

#### 13. Waitlist Model
```
Waitlists Table:
- id (Primary Key)
- customer_id (Foreign Key → Users)
- staff_id (Foreign Key → Staff, nullable)
- service_id (Foreign Key → Services)
- requested_date (date)
- time_preference (time, nullable) [e.g., morning, afternoon]
- status (enum: 'waiting', 'notified', 'booked', 'expired', 'cancelled')
- notification_sent_at (timestamp, nullable)
- converted_appointment_id (Foreign Key → Appointments, nullable)
- created_at, updated_at

Relationships:
- Many-to-One with User (Customer)
- Many-to-One with Staff
- Many-to-One with Service
```

#### 14. Review Model
```
Reviews Table:
- id (Primary Key)
- appointment_id (Foreign Key → Appointments, unique)
- customer_id (Foreign Key → Users)
- staff_id (Foreign Key → Staff)
- rating (tinyint: 1-5)
- comment (text, nullable)
- is_verified_booking (boolean, default: true)
- helpful_count (integer, default: 0)
- created_at, updated_at

Relationships:
- Many-to-One with Appointment
- Many-to-One with User (Customer)
- Many-to-One with Staff
```

#### 15. Product Model
```
Products Table:
- id (Primary Key)
- name (string)
- description (text, nullable)
- price (decimal, nullable) [For retail products in future POS]
- cost (decimal, nullable) [For profit calculation]
- sku (string, unique)
- stock (integer)
- low_stock_threshold (integer, default: 10)
- image (string, nullable)
- is_active (boolean)
- created_at, updated_at

Relationships:
- One-to-Many with OrderItems (if implementing POS)
```

#### 16. Package Model
```
Packages Table:
- id (Primary Key)
- name (string)
- description (text, nullable)
- price (decimal)
- services_json (json) [Array of service_ids]
- total_duration_minutes (integer, computed)
- discount_percentage (decimal, nullable)
- image (string, nullable)
- is_active (boolean)
- created_at, updated_at

Relationships:
- Many-to-Many with Services (through services_json)
```

#### 17. Coupon Model
```
Coupons Table:
- id (Primary Key)
- code (string, unique)
- description (text, nullable)
- discount_type (enum: 'percentage', 'fixed_amount')
- discount_value (decimal)
- max_uses (integer, nullable)
- current_uses (integer, default: 0)
- min_booking_amount (decimal, nullable)
- valid_from (date)
- valid_until (date)
- is_active (boolean)
- created_at, updated_at

Relationships:
- One-to-Many with Payments (coupons applied)
```

#### 18. LoyaltyPoints Model
```
Loyalty_Points Table:
- id (Primary Key)
- customer_id (Foreign Key → Users, unique)
- balance (integer, default: 0)
- earned_total (integer, default: 0)
- redeemed_total (integer, default: 0)
- last_activity_at (timestamp)
- created_at, updated_at

Relationships:
- One-to-One with User
```

#### 19. LoyaltyTransaction Model
```
Loyalty_Transactions Table:
- id (Primary Key)
- loyalty_points_id (Foreign Key → LoyaltyPoints)
- appointment_id (Foreign Key → Appointments, nullable)
- type (enum: 'earn', 'redeem')
- points (integer)
- description (string)
- created_at

Relationships:
- Many-to-One with LoyaltyPoints
- Many-to-One with Appointment (optional)
```

#### 20. Tip Model (USA Specific)
```
Tips Table:
- id (Primary Key)
- appointment_id (Foreign Key → Appointments)
- staff_id (Foreign Key → Staff)
- customer_id (Foreign Key → Users)
- amount (decimal) [USD]
- percentage (decimal) [15, 18, 20, or custom]
- paid_via_stripe (boolean, default: true)
- status (enum: 'pending', 'completed', 'failed')
- created_at, updated_at

Relationships:
- Many-to-One with Appointment
- Many-to-One with Staff
- Many-to-One with User (Customer)
```

#### 21. Payment Model (Stripe)
```
Payments Table:
- id (Primary Key)
- appointment_id (Foreign Key → Appointments)
- customer_id (Foreign Key → Users)
- amount (decimal) [USD - includes tax & tip]
- breakdown_json (json) [{ service_price, tax, tip, total }]
- payment_method (enum: 'stripe_card', 'stripe_ach') [Stripe only]
- stripe_payment_intent_id (string, unique)
- stripe_charge_id (string, nullable)
- currency (string, default: 'USD')
- status (enum: 'pending', 'succeeded', 'failed', 'refunded')
- refund_amount (decimal, nullable)
- refund_reason (string, nullable) [cancellation, no-show, correction]
- refund_date (timestamp, nullable)
- refund_stripe_id (string, nullable)
- payment_date (timestamp, nullable)
- created_at, updated_at

Relationships:
- Many-to-One with Appointment
- Many-to-One with User
```

#### 22. SalesTaxTransaction Model (USA Specific)
```
Sales_Tax_Transactions Table:
- id (Primary Key)
- appointment_id (Foreign Key → Appointments)
- state (string) [USA State abbreviation]
- tax_rate (decimal) [Percentage]
- taxable_amount (decimal)
- tax_amount (decimal) [Calculated]
- created_at

Relationships:
- Many-to-One with Appointment
```

#### 23. ACHBankAccount Model (USA Specific)
```
ACH_Bank_Accounts Table:
- id (Primary Key)
- staff_id (Foreign Key → Staff, unique)
- bank_account_routing_number (string, encrypted)
- bank_account_number (string, encrypted)
- bank_account_holder_name (string)
- bank_name (string, nullable)
- verification_status (enum: 'pending', 'verified', 'failed')
- stripe_bank_account_token (string, nullable)
- last_4_digits (string) [For display only]
- created_at, updated_at

Relationships:
- One-to-One with Staff
```

#### 24. ACHPayout Model (USA Specific)
```
ACH_Payouts Table:
- id (Primary Key)
- staff_id (Foreign Key → Staff)
- amount (decimal) [USD]
- status (enum: 'pending', 'in_transit', 'completed', 'failed')
- stripe_payout_id (string, nullable)
- payout_date (date, nullable)
- expected_arrival_date (date, nullable)
- failure_reason (text, nullable)
- commission_amount (decimal)
- tips_amount (decimal)
- adjustments_amount (decimal, nullable)
- notes (text, nullable)
- created_at, updated_at

Relationships:
- Many-to-One with Staff
```

---

## ⚡ Critical Business Logic & Advanced Rules

### 1. USA-Specific: Tipping System at Checkout

**Setup:**
- Admin configures tipping in settings: Enable/Disable, Default tip percentages
- Example: [15%, 18%, 20%, Custom amount]
- Tipping is OPTIONAL for customers

**Checkout Flow:**
1. After deposit payment amount is calculated
2. Display service breakdown:
   ```
   Service Price:  $75.00
   Sales Tax:      $6.75 (9%)
   Subtotal:       $81.75
   Deposit (25%):  $20.44
   ```
3. Show tipping options:
   ```
   Tip (Optional):
   [ 15% ($11.25) ]  [ 18% ($13.50) ]  [ 20% ($15.00) ]  [ Custom $__ ]
   ```
4. Final total = Deposit + Optional Tip
5. Process both through Stripe in single charge
6. Create Tip record with appointment

**Tip Distribution:**
- Tips are recorded in `tips` table linked to specific staff
- Staff can view their earned tips in dashboard
- Tips included in ACH payouts (separate line item)

---

### 2. USA-Specific: Sales Tax Calculation

**Setup:**
1. Admin configures salon's state in settings
2. Lookup state sales tax rate (or manually enter)
3. Mark which services are taxable (default: true)
   - Most services taxed in USA (haircuts, massages, etc.)
   - Some states exempt certain services

**Calculation at Checkout:**
1. Calculate service subtotal
2. If service is taxable:
   ```
   tax_amount = service_price * (salon.sales_tax_rate / 100)
   ```
3. Create SalesTaxTransaction record
4. Display on invoice:
   ```
   Service:  $75.00
   Tax (9%): $6.75
   Total:    $81.75
   ```

**Reporting:**
- Admin dashboard shows tax collected by month
- Export tax data for state filing
- Track by service (which services generate most tax)

---

### 3. USA-Specific: No-Show Penalties (Credit Card Auto-Charge)

**Setup:**
- Admin configures no-show fee: $25 (or custom amount)
- Enabled by default for USA compliance
- Policy displayed at booking

**Trigger:**
1. Appointment start time passes
2. Cron job runs 30 minutes after start_time
3. If appointment status still 'pending' → auto-mark as 'no_show'
4. If customer paid deposit with credit card:
   - **Charge remaining no-show fee to card on file** (Stripe)
   - Create Payment record for the fee
   - Mark appointment.no_show_fee_charged = true
   - Send email receipt to customer

**Tracking:**
- Admin views no-show report (frequency per customer)
- After 3 no-shows → Customer blocked from booking
- No-show fee goes to salon's account (not staff)

---

### 4. USA-Specific: ACH Bank Transfers for Staff Payouts

**Setup Phase:**
1. Staff member provides bank account details:
   - Routing number (encrypted)
   - Account number (encrypted)
   - Account holder name
2. Save in `ACHBankAccount` model
3. Stripe Connect verification (optional but recommended)

**Payout Calculation:**
- Admin initiates payout manually (weekly, bi-weekly, monthly)
- Calculate per-staff earnings:
  ```
  Commission = SUM(completed_appointments.service_price * staff.commission_rate / 100)
  Tips = SUM(tips.amount) for staff
  Adjustments = Manual adjustments (if any)
  Total Payout = Commission + Tips + Adjustments
  ```

**Processing:**
1. Create ACHPayout record with status='pending'
2. Use Stripe Connect to initiate ACH transfer:
   ```
   stripe.transfers.create({
     amount: total_amount_cents,
     currency: 'usd',
     destination: staff.stripe_connect_account_id
   })
   ```
3. Store stripe_payout_id
4. Update status to 'in_transit'
5. Expected delivery: 1-2 business days
6. Webhook confirms when delivered (status = 'completed')

**Staff View:**
- View pending payouts
- View completed payouts history
- Download payout statements

**Admin View:**
- Trigger payouts manually
- View payout status
- Edit or adjust amounts before processing
- Export payout records

---

### 5. Double-Booking & Overlap Prevention (CRITICAL)

**Database-Level Validation:**
```
For a new appointment:
- staff_id = selected staff
- appointment_date = requested date
- start_time = requested start
- end_time = start_time + service.duration + service.buffer_time

Query existing appointments:
SELECT * FROM appointments
WHERE staff_id = ?
  AND appointment_date = ?
  AND status IN ('pending', 'confirmed')
  AND (
    (start_time < ?) AND (end_time > ?)
  )

If any overlap found:
→ Reject booking or suggest waitlist
```

**Include in Slot Calculation:**
- Staff's working hours from StaffSchedule
- Service duration + buffer time
- Breaks and lunch hours
- Days off / vacation
- Existing appointments (confirmed + pending)

**Prevent Race Conditions:**
- Use database transactions
- Lock row during slot check: `SELECT ... FOR UPDATE`

---

### 6. Slot Generation Algorithm

**Input:** `staff_id, appointment_date, service_id`

**Logic:**
1. Retrieve staff's schedule for the day of week
2. If day off, return empty slots
3. If no working hours set, return empty slots
4. Calculate service duration + buffer time
5. Iterate through working hours in 15-min intervals (or configurable)
6. For each potential slot:
   - Check for overlaps with confirmed/pending appointments
   - Check for staff breaks
   - Verify slot fits within working hours
   - Add to available slots if valid
7. Return sorted list of available time slots

**Caching:**
- Cache available slots in Redis with TTL = 5 minutes
- Invalidate cache on:
  - New appointment booked
  - Appointment cancelled
  - Schedule changed
  - Day off added/removed

---

### 7. Partial Deposit & Cancellation Policy

**Booking Phase:**
1. Calculate deposit amount:
   - If `service.deposit_amount` is set → use that
   - Else → use `salon.deposit_percentage * service.price`
2. Display deposit to customer
3. Process payment via Stripe/Razorpay
4. On success: Mark `appointment.deposit_paid = amount`
5. Calculate remaining balance: `total_amount - deposit_paid`

**Cancellation Phase:**
1. Check appointment date against current date
2. Compare with cancellation policy:
   - Example: "Free cancellation up to 24 hours before; $25 fee after"
3. Calculate cancellation fee based on policy
4. Process refund:
   - Refund amount = `deposit_paid - cancellation_fee`
   - Keep cancellation fee in salon's account
5. Update payment status to 'refunded'
6. Mark appointment status as 'cancelled'
7. Send confirmation email with refund details

**Webhook Handling (Payment Refund):**
- Listen for refund events from Stripe
- Update payment status if refund succeeds/fails

---

### 8. Waitlist & Notification Logic

**Joining Waitlist:**
1. Customer tries to book but no slots available
2. Prompt to join waitlist
3. Create Waitlist entry with:
   - `customer_id, staff_id (optional), service_id, salon_id`
   - `requested_date, time_preference (optional)`
   - `status = 'waiting'`

**Automatic Notification (Queue Job):**
- Listen for appointment cancellations
- Find matching waitlist entries:
  - Same `service_id`
  - Same `salon_id`
  - Requested date <= cancelled date
  - Status = 'waiting'
- Send notification (email/SMS/database alert):
  - Message: "A slot just opened up! [Staff name] is available on [Date] at [Time]. Click here to book."
  - Waitlist status = 'notified'
  - Provide one-click booking link (pre-fill form)
- Expiration: Mark as 'expired' if not booked within 48 hours

---

### 9. Staff Commission Tracking

**Setup:**
- Each staff has `commission_rate` (e.g., 20%)
- Rate applies to service price (not deposit)

**Calculation (Trigger on Appointment Completion):**
```
commission = service.price * (staff.commission_rate / 100)
```

**Reporting:**
- Admin view: Commission report by date range
- Breakdown: Total completed appointments, total revenue, total commission owed
- Export as PDF/CSV
- Payment status: pending, paid, held

**Payroll Integration (Future):**
- Export commission data for payroll systems
- Record commission payments as transactions

---

### 10. Email Notifications & Queue Jobs

**Notification Events:**
1. **Booking Confirmation**
   - To: Customer
   - Content: Appointment details, staff info, location, cancellation policy, iCal attachment
   - Trigger: After deposit payment succeeds

2. **Appointment Reminder**
   - To: Customer
   - Trigger: 24 hours before appointment
   - Content: Reminder, directions, staff photo, contact info
   - Allow confirmation/reschedule/cancel links

3. **Cancellation Confirmation**
   - To: Customer
   - Content: Refund amount, refund timeline, cancellation reason, option to reschedule
   - Trigger: Immediately after cancellation

4. **Waitlist Notification**
   - To: Customer
   - Content: Slot available, booking link, expiration time
   - Trigger: When slot becomes available

5. **Staff Assignment Notification** (Admin → Staff)
   - To: Staff member
   - Content: New appointment, customer details, service, time
   - Trigger: When admin manually assigns appointment

6. **Admin Daily Summary** (Optional)
   - To: Salon admin
   - Content: Today's appointments, no-shows, cancellations, revenue
   - Trigger: 8:00 AM daily

**Implementation:**
- Use Laravel Queue (Jobs)
- Queue connection: database or Redis
- Mail class with customizable Blade templates
- Retry logic: 3 retries if failed

---

### 11. Timezone Handling

**Database:**
- Store all times in UTC
- Store `datetime` fields (not just `time`)

**Display:**
- Retrieve user's timezone from `users.timezone` or detect from browser
- Convert appointment times to user's local timezone
- Display as: "2:00 PM EST" or "14:00"

**Booking:**
- When customer selects date/time, convert to UTC before saving
- Example: Customer in EST sees "2:00 PM", system saves as "19:00 UTC"

**Staff Schedule:**
- Staff sets working hours in their local timezone
- System converts to UTC for storage and overlap checking
- Cron jobs must account for timezone changes (DST)

---

### 12. Customer Blocklist & Penalties (Separate from No-Show Fees)

**Blocking Conditions:**
1. Mark appointment as 'no_show' (instead of completed)
2. Auto-trigger penalty:
   - Deduct loyalty points (if enrolled)
   - Block customer from further bookings (if repeat offender)
   - Charge no-show fee (if policy permits)
3. Send notification to customer explaining penalty

**Automatic Trigger:**
- Cron job: 30 minutes after appointment start time, if status still 'pending' → auto-mark as 'no_show'

---

### 13. Single-Location Salon Architecture (Not Multi-Tenant SaaS)

**Important Clarification:**
- This system is built for **ONE independent salon owner**
- **Not** a SaaS platform where multiple salons share the system
- **Not** multi-tenant (no data isolation between salons needed)
- All data belongs to one salon
- Simplifies database design (no salon_id required on most tables)
- Simplifies authorization (no need to check salon ownership)

**Implications:**
- Authentication: Single admin user (salon owner) + staff users + customer users
- Database: Minimal `salons` table (essentially config storage)
- Routing: No `/salons/:id` namespace needed
- Authorization: Simple role-based (admin, staff, customer) instead of org-based

---

### 14. Data Validation & Business Rules

**Appointment Booking:**
1. Multiple no-shows (e.g., >= 3)
2. Late cancellations (within 24 hours) repeatedly
3. Manual admin block

**Block Effects:**
- Customer cannot book new appointments
- Customer cannot join waitlist
- Display message: "Your account has booking restrictions. Contact support."

**Unblocking:**
- Admin review and manual unblock
- Auto-unblock after X days (configurable)

---

### 10. Data Validation & Business Rules (duplicate section number in source)

**Appointment Booking:**
- Service must be active
- Staff must have service assigned
- Selected date must not be in the past
- Start time must be within salon's operating hours
- Customer must have filled consent forms (if required)

**Staff Schedule:**
- Start time < end time
- Break times must be within shift hours
- Cannot work more than 16 hours/day

**Pricing:**
- Service price >= 0
- Deposit >= 0 and <= service price
- Coupon discount <= total price

**Service Setup:**
- Duration >= 15 minutes
- Buffer time >= 0
- At least one staff member assigned

---

## 🎨 UI/UX & Styling Guidelines

### Frontend (Customer Facing)
- **Aesthetic:** Modern, clean, minimalist design suitable for beauty/wellness
- **Color Scheme:** Soft pastels or professional blues/purples with accent colors
- **Typography:** Modern sans-serif (Tailwind default: Inter, or similar)
- **Components:**
  - Hero section with salon search/filter
  - Service cards with images, pricing, duration, ratings
  - Staff profile cards with photos and bios
  - Multi-step booking widget with step indicator and progress bar
  - Appointment confirmation card with printable receipt
  - Interactive calendar with available slots highlighted
  - Toast notifications for errors/success
  - Modal dialogs for consent forms
- **Responsiveness:** Mobile-first design (working on 320px - 1920px screens)
- **Accessibility:** WCAG 2.1 AA compliance (semantic HTML, ARIA labels, color contrast)

### Admin Dashboard
- **Layout:** Sidebar navigation (collapsible on mobile) + main content area
- **Aesthetic:** Professional, data-dense, high contrast for readability
- **Components:**
  - Sidebar with main sections + collapsible sub-menus
  - Header with user profile + notifications + logout
  - KPI cards showing key metrics (revenue today, bookings, active customers)
  - Calendar grid view (day/week/month) with appointment blocks
  - Drag & drop for rescheduling appointments
  - Data tables with sorting, filtering, pagination
  - Forms for adding/editing staff, services, schedules
  - Status badges for appointments (pending, confirmed, completed, cancelled)
  - Charts for revenue trends, staff performance, customer acquisition
- **Color Scheme:** Professional with status indicators (green = confirmed, yellow = pending, red = cancelled)
- **Responsive:** Optimized for 1024px+ screens (desktop-first)

### Asset Separation
- `resources/css/app.css` → Customer views, loaded in `layouts/app.blade.php`
- `resources/css/admin.css` → Admin views, loaded in `layouts/admin.blade.php`
- Configure in `vite.config.js`:
  ```javascript
  build: {
    rollupOptions: {
      input: {
        app: 'resources/css/app.css',
        admin: 'resources/css/admin.css',
      }
    }
  }
  ```

---

## 🔒 Security Considerations

1. **Authentication:**
   - Use Laravel Breeze's built-in hashing
   - Implement 2FA (optional) for admin accounts
   - Session timeout after 30 minutes of inactivity

2. **Authorization:**
   - Use Policies for resource-level access control
   - Admin can only access own salon data
   - Customers can only view/edit own appointments

3. **Payment Security:**
   - Never store raw card data
   - Use Stripe/Razorpay tokens only
   - PCI DSS compliance

4. **Data Protection:**
   - Encrypt sensitive fields (phone numbers, health data from consent forms)
   - GDPR compliance: Data deletion, export, consent records
   - Log access to sensitive data (audit trail)

5. **Input Validation:**
   - Validate all user inputs on backend (never trust frontend)
   - Rate limiting on booking endpoints (max 10 requests/minute/IP)
   - CSRF token protection on all forms

6. **SQL Injection Prevention:**
   - Use Eloquent parameterized queries
   - Never use raw SQL with user input

---

## ⚡ Performance & Scalability

1. **Database Indexing:**
   - Index frequently queried columns:
     ```sql
     - appointments: (salon_id, appointment_date, status)
     - appointments: (staff_id, appointment_date)
     - staff_schedules: (staff_id, day_of_week)
     - customers: (salon_id, email)
     ```

2. **Caching Strategy:**
   - Cache available slots (5-min TTL) → Redis
   - Cache salon settings (1-hour TTL)
   - Cache staff data (30-min TTL)
   - Invalidate on updates

3. **Query Optimization:**
   - Use eager loading (with()) to avoid N+1 queries
   - Pagination on all list views (15-50 results per page)
   - Lazy loading for images

4. **Async Processing:**
   - Queue heavy tasks: email sending, PDF generation, reports
   - Use database connection pooling for batch operations

5. **Horizontal Scaling:**
   - Stateless application design
   - Use Redis for session storage (not file-based)
   - Load balancer for multiple servers
   - Database replication for read scaling

---

## ✅ Testing Strategy

### Unit Tests
- **Overlap Detection:** Verify slots don't overlap with existing appointments
- **Commission Calculation:** Verify commission amounts are correct
- **Cancellation Fee:** Verify fees calculated per policy
- **Waitlist Logic:** Verify notifications sent on cancellation
- **Deposit Calculation:** Verify deposit amounts per service

### Feature Tests
- **Booking Flow:** Complete end-to-end booking with payment
- **Staff Scheduling:** Add schedule, create appointments, verify no overlaps
- **Cancellation:** Cancel appointment, verify refund, verify waitlist notification
- **Admin Operations:** Create service, assign staff, view appointments

### Integration Tests
- **Payment Gateway:** Mock Stripe webhooks, verify appointment status updates
- **Email Queue:** Verify emails sent on key events
- **Database Transactions:** Verify rollback on failed payments

### Test Coverage Target
- Minimum 80% code coverage
- Priority: Business logic (booking, payments, scheduling)

---

## 📦 File Organization

```
salon-booking-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AppointmentController.php
│   │   │   │   ├── StaffController.php
│   │   │   │   ├── ServiceController.php
│   │   │   │   ├── ScheduleController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── ReportController.php
│   │   │   ├── Customer/
│   │   │   │   ├── BookingController.php
│   │   │   │   ├── AppointmentController.php
│   │   │   │   ├── ReviewController.php
│   │   │   ├── PaymentController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   ├── CustomerMiddleware.php
│   │   ├── Requests/ (Form validation classes)
│   ├── Models/ (All Eloquent models)
│   ├── Services/
│   │   ├── SlotService.php (Slot generation)
│   │   ├── BookingService.php (Booking logic)
│   │   ├── PaymentService.php (Payment processing)
│   │   ├── NotificationService.php (Email/SMS)
│   ├── Jobs/
│   │   ├── SendBookingConfirmation.php
│   │   ├── SendAppointmentReminder.php
│   │   ├── NotifyWaitlistCustomers.php
│   ├── Policies/ (Authorization)
│   ├── Events/ (Event classes)
│   ├── Listeners/ (Event listeners)
├── database/
│   ├── migrations/
│   ├── seeders/
│   ├── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php (Customer layout)
│   │   │   ├── admin.blade.php (Admin layout)
│   │   ├── customer/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── browse.blade.php
│   │   │   ├── booking/ (Multi-step booking)
│   │   │   ├── appointments.blade.php
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── appointments/
│   │   │   ├── staff/
│   │   │   ├── services/
│   │   │   ├── schedules/
│   │   │   ├── customers/
│   │   │   ├── forms/
│   │   │   ├── inventory/
│   │   │   ├── reports/
│   ├── css/
│   │   ├── app.css (Customer styles)
│   │   ├── admin.css (Admin styles)
│   ├── js/
│   │   ├── app.js
│   │   ├── admin.js
├── routes/
│   ├── web.php (Main routes)
│   ├── api.php (Future API endpoints)
├── tests/
│   ├── Unit/ (Unit tests)
│   ├── Feature/ (Feature tests)
├── .env.example
├── composer.json
├── vite.config.js
├── tailwind.config.js
```

---

## 🚀 Step-by-Step Execution Plan

### **STEP 1: Database Migrations, Models & Relationships**

#### Tasks:
1. Create migrations for all 20 tables (in order of dependencies)
2. Create Eloquent models with properties & relationships
3. Set up foreign key constraints (cascade/restrict rules)
4. Add database indexes for performance
5. Run migrations and verify schema
6. Create model factories for testing
7. Create database seeders for demo data

#### Deliverables:
- ✅ `/database/migrations/` (all migration files)
- ✅ `/app/Models/` (all model classes)
- ✅ `/database/factories/` (factories for testing)
- ✅ `/database/seeders/` (demo data seeder)

#### Commands to Run:
```bash
php artisan make:migration create_users_table
php artisan make:model User -m
php artisan migrate
php artisan seed:SalonSeeder
```

---

### **STEP 2: Authentication, Authorization & Middleware**

#### Tasks:
1. Set up Laravel Breeze (Login, Register, Password Reset, Email Verification)
2. Implement multi-role system (`admin`, `customer`, `staff`)
3. Create Policies for resource authorization (AppointmentPolicy, etc.)
4. Create Gates for simple checks (is_admin, is_customer)
5. Create Middleware for role-based routing
6. Configure Vite for separate CSS assets (admin.css vs app.css)
7. Create base layouts: `layouts/app.blade.php` and `layouts/admin.blade.php`

#### Deliverables:
- ✅ `/app/Policies/` (all policy classes)
- ✅ `/app/Http/Middleware/` (role-based middleware)
- ✅ `/resources/views/layouts/` (master layouts)
- ✅ `vite.config.js` (separate CSS entry points)
- ✅ `tailwind.config.js` (Tailwind setup)

#### Commands:
```bash
composer require laravel/breeze --dev
php artisan breeze:install
php artisan make:policy AppointmentPolicy -m Appointment
php artisan make:middleware AdminMiddleware
```

---

### **STEP 3: Service Classes & Business Logic (USA-Specific)**

#### Tasks:
1. Create `SlotService.php` for dynamic slot generation
2. Create `BookingService.php` for appointment creation
3. Create `PaymentService.php` for Stripe integration (USA only)
4. Create `TipService.php` (USA-Specific)
5. Create `SalesTaxService.php` (USA-Specific)
6. Create `NoShowFeeService.php` (USA-Specific)
7. Create `ACHPayoutService.php` (USA-Specific)
8. Create `NotificationService.php` for email/SMS
9. Create `CommissionService.php` for staff earnings
10. Create `CancellationService.php` for refunds & fees

#### Deliverables:
- ✅ `/app/Services/SlotService.php`
- ✅ `/app/Services/BookingService.php`
- ✅ `/app/Services/PaymentService.php`
- ✅ `/app/Services/NotificationService.php`
- ✅ `/app/Services/CommissionService.php`
- ✅ `/app/Services/CancellationService.php`

---

### **STEP 4: Controllers, Jobs & Events**

(Admin controllers: Appointment, Staff, Service, Schedule, Customer, FormBuilder,
Dashboard, Report, Payout, Settings. Customer controllers: Browse, Booking,
Appointment, Review, Tip. Plus PaymentController for Stripe webhooks.
Queue Jobs: SendBookingConfirmation, SendAppointmentReminder,
SendCancellationConfirmation, NotifyWaitlistCustomers, MarkNoShowAppointments,
ChargeNoShowFee, SendTipReceipt, ProcessACHPayouts, SyncStripePaymentStatus.
Events: AppointmentBooked, AppointmentCancelled, PaymentSucceeded.)

---

### **STEP 5: Blade Views & Frontend**

#### Tasks:

**Admin Views:**
1. `/admin/dashboard.blade.php` → KPIs, charts, recent activity, revenue breakdown (including tax & tips)
2. `/admin/appointments/index.blade.php` → Calendar grid, drag-drop, bulk actions
3. `/admin/appointments/create.blade.php` → Manual walk-in booking form (with tax & tip calculation)
4. `/admin/staff/index.blade.php` → Staff list, add/edit
5. `/admin/staff/schedule.blade.php` → Staff schedule editor (day/shift/breaks)
6. `/admin/staff/ach-accounts.blade.php` (USA-Specific) → Manage staff bank accounts for ACH payouts
7. `/admin/services/index.blade.php` → Service CRUD, mark taxable/non-taxable
8. `/admin/customers/index.blade.php` → CRM directory, no-show tracking
9. `/admin/forms/builder.blade.php` → Consent form builder
10. `/admin/reports/commission.blade.php` → Commission reports per staff
11. `/admin/reports/tips.blade.php` (USA-Specific) → Tips earned by staff
12. `/admin/reports/tax.blade.php` (USA-Specific) → Sales tax collected by month
13. `/admin/payouts.blade.php` (USA-Specific) → Manage ACH payouts to staff
14. `/admin/settings.blade.php` → Salon info, address, state, timezone; cancellation policy; no-show fee configuration; tipping settings (enable/disable, default percentages); sales tax configuration by state; ACH settings for staff payouts

**Customer Views:**
1. `/customer/dashboard.blade.php` → Home, upcoming appointments, quick stats
2. `/customer/browse.blade.php` → Search/filter services and staff
3. `/customer/booking/step1.blade.php` → Select service (with price, duration, tax indicator)
4. `/customer/booking/step2.blade.php` → Choose staff (with photos, ratings)
5. `/customer/booking/step3.blade.php` → Select date & time (dynamic slot loader)
6. `/customer/booking/step4.blade.php` → Consent form modal
7. `/customer/booking/step5.blade.php` → Review & checkout
   ```
   Service:          $75.00
   Tax (9%):         +$6.75
   Subtotal:         $81.75
   Deposit (25%):    $20.44
   Tip Options: [15%] [18%] [20%] [Custom]  (USA-Specific)
   Total:            $20.44 + tip
   ```
8. `/customer/booking/payment.blade.php` → Stripe card form
9. `/customer/booking/confirmation.blade.php` → Success page with calendar invite, receipt, tip info
10. `/customer/appointments.blade.php` → Upcoming, past, cancelled tabs
11. `/customer/reviews.blade.php` → Rate & review past appointments
12. `/customer/tips-history.blade.php` (USA-Specific) → View tips given history

**Reusable Components (Blade includes):**
- `/components/appointment-card.blade.php`
- `/components/staff-card.blade.php`
- `/components/service-card.blade.php`
- `/components/booking-widget.blade.php`
- `/components/calendar-grid.blade.php`
- `/components/time-slot-picker.blade.php`
- `/components/form-builder.blade.php`

**Layouts:**
- `/layouts/app.blade.php` → Customer-facing layout (navbar, footer)
- `/layouts/admin.blade.php` → Admin dashboard layout (sidebar, header)
- `/layouts/booking.blade.php` → Multi-step booking layout (progress indicator)

#### Deliverables:
- ✅ `/resources/views/admin/` (all admin views)
- ✅ `/resources/views/customer/` (all customer views)
- ✅ `/resources/views/components/` (reusable components)
- ✅ `/resources/views/layouts/` (master layouts)

---

### **STEP 6: Styling with Tailwind CSS**

#### Tasks:
1. Configure `tailwind.config.js` with custom theme
2. Create `/resources/css/app.css` (customer styles) — import Tailwind utilities, custom component styles (buttons, cards, modals), responsive design (mobile-first)
3. Create `/resources/css/admin.css` (admin dashboard styles) — dark mode support (optional), sidebar navigation styles, data table styles
4. Configure Vite to build both CSS separately
5. Load appropriate CSS in each layout (`@vite(['resources/css/app.css'])`)

#### Deliverables:
- ✅ `/resources/css/app.css` (Customer styles)
- ✅ `/resources/css/admin.css` (Admin styles)
- ✅ `tailwind.config.js` (Configuration)
- ✅ `vite.config.js` (Vite configuration)

---

### **STEP 7: Routes & Configuration**

#### Tasks:
1. Set up route groups with middleware:
   - `/customer/*` → Customer routes (web middleware + customer policy)
   - `/admin/*` → Admin routes (web middleware + admin middleware)
   - `/api/slots` → Public API for slot generation with tax/tip calculation (AJAX)
   - `/api/appointments` → Customer API endpoints
   - `/webhooks/stripe` → Stripe webhooks (public, no auth)
   - `/webhooks/stripe/payouts` → Stripe payout status webhooks
2. Configure environment variables (`.env`): database credentials, Stripe API keys, email driver (SMTP, Mailgun, etc.), Redis connection (for caching), queue driver (database, Redis)
3. Queue configuration: set `QUEUE_CONNECTION=database` in `.env`; run `php artisan queue:work` for background jobs
4. Email configuration: set `MAIL_DRIVER=smtp` or mailgun; configure sender email

#### Deliverables:
- ✅ `/routes/web.php` (All routes)
- ✅ `/routes/api.php` (API routes for AJAX)
- ✅ `/.env.example` (Example environment file)
- ✅ `/config/services.php` (Stripe, payment settings)

---

### **STEP 8: Stripe Integration for USA (Payments, Tips, Payouts)**

#### Tasks:
1. Install Stripe Laravel package: `composer require stripe/stripe-php`
2. Set up Stripe Connect for USA ACH payouts
3. Implement payment intent creation in `PaymentService.php` — include service price, calculate and include sales tax, add tip amount (optional), calculate final total, create Stripe PaymentIntent
4. Create `/resources/views/customer/booking/payment.blade.php` — cost breakdown (service, tax, tip options), Stripe card element (via Stripe.js), tip selection (15%, 18%, 20%, custom), AJAX form submission
5. Create `/app/Http/Controllers/PaymentController.php` — handle payment webhook from Stripe, verify webhook signature, update appointment status on success, create Payment record, create Tip record (if tip selected), queue confirmation email with receipt
6. Create `/app/Http/Controllers/PayoutController.php` (USA-Specific) — initiate ACH transfers to staff via Stripe Connect, track payout status, handle payout webhooks
7. Implement Stripe Connect for staff payouts — staff provides ACH bank details, Stripe verifies bank account, create Stripe Connect account link for staff
8. Configure Stripe webhook endpoints in dashboard:
   - `payment_intent.succeeded` → Update appointment
   - `charge.refunded` → Process refund
   - `payout.paid` → Update payout status
   - `payout.failed` → Alert admin
9. Test with Stripe test keys; set up production Stripe keys

#### Deliverables:
- ✅ `/app/Http/Controllers/PaymentController.php`
- ✅ `/app/Http/Controllers/PayoutController.php`
- ✅ Stripe integration in `PaymentService.php`, `TipService.php`, `ACHPayoutService.php`
- ✅ Payment view with Stripe card element and tip options
- ✅ Webhook signature verification for payments and payouts

---

### **STEP 9: Email & Notification Templates**

#### Tasks:
1. Create Mailable classes in `/app/Mail/`:
   - `BookingConfirmation.php` → Confirmation with appointment details & iCal
   - `AppointmentReminder.php` → 24-hour reminder
   - `CancellationNotice.php` → Cancellation with refund info
   - `WaitlistNotification.php` → Slot available alert
2. Create Blade email templates in `/resources/views/emails/`
3. Configure email driver (SMTP, Mailgun, SendGrid)
4. Test emails with Mailtrap or similar

#### Deliverables:
- ✅ `/app/Mail/` (Mailable classes)
- ✅ `/resources/views/emails/` (Email templates)
- ✅ Queue jobs dispatch Mailables

---

### **STEP 10: Testing & Quality Assurance**

#### Tasks:
1. Write Unit Tests:
   - `Tests/Unit/SlotServiceTest.php` → Test slot generation logic
   - `Tests/Unit/CommissionServiceTest.php` → Test commission calculations
   - `Tests/Unit/CancellationServiceTest.php` → Test refund logic
2. Write Feature Tests:
   - `Tests/Feature/BookingFlowTest.php` → End-to-end booking
   - `Tests/Feature/AppointmentControllerTest.php` → Admin operations
   - `Tests/Feature/PaymentWebhookTest.php` → Webhook handling
3. Run tests: `php artisan test`
4. Check coverage: `php artisan test --coverage`
5. Fix any failing tests

#### Deliverables:
- ✅ `/tests/Unit/` (Unit tests)
- ✅ `/tests/Feature/` (Feature tests)
- ✅ >80% code coverage

---

### **STEP 11: Deployment & DevOps**

#### Tasks:
1. Create production `.env` file with real API keys
2. Set up database (PostgreSQL recommended for production)
3. Configure Redis for caching/sessions
4. Set up queue worker (Supervisor for long-running process)
5. Configure mail driver (Mailgun, SendGrid)
6. Set up storage (S3 for images)
7. Enable HTTPS/SSL
8. Set up monitoring & error tracking (Sentry)
9. Create deployment script (CI/CD with GitHub Actions)
10. Database backups configuration

#### Deliverables:
- ✅ Production `.env` configuration
- ✅ Supervisor config for queue worker
- ✅ S3 storage setup
- ✅ GitHub Actions workflow (auto-deploy on push)

---

### **STEP 12: Documentation & Launch**

#### Tasks:
1. Write README with setup instructions
2. Create API documentation (if building API later)
3. Create admin user guide
4. Create customer FAQ
5. Set up error logging & monitoring
6. Perform security audit
7. Load testing & performance optimization
8. Soft launch with beta users; gather feedback and iterate
9. Public launch

#### Deliverables:
- ✅ `/README.md`
- ✅ Admin guide (PDF)
- ✅ FAQ & support docs
- ✅ Security audit report

---

## 📅 Timeline Estimate (USA Single-Location Edition)

- **Phase 1: Setup & Database** (Week 1-2) — Step 1-2: Migrations (simplified without multi-location), Models, Auth, Middleware
- **Phase 2: USA-Specific Business Logic** (Week 3-5) — Step 3: Service classes with tipping, sales tax, ACH payout logic
- **Phase 3: UI & Styling** (Week 5-6) — Step 4-6: Admin & Customer views, Tailwind CSS, Components including tipping UI
- **Phase 4: Stripe & USA Payments** (Week 7-8) — Step 7-8: Stripe payment processing, ACH staff payouts, Webhook handling
- **Phase 5: Notifications & Additional Features** (Week 8-9) — Step 9-10: Email templates, no-show fee automation, tax reporting
- **Phase 6: Testing & Launch** (Week 10) — Step 11-12: Tests, USA compliance checks, DevOps, Launch

**Total: ~10-12 weeks** (includes USA-specific features: tipping, sales tax, ACH, no-show fees)

---

## 🎯 Success Criteria

**Core Functionality:**
- ✅ Customers can book appointments with deposit payment via Stripe
- ✅ Admins can manage staff schedules, services, and appointments
- ✅ Double-booking prevented with zero overlaps
- ✅ Email notifications sent reliably for all key events

**USA-Specific Features:**
- ✅ Tipping system working at checkout (15%, 18%, 20%, custom)
- ✅ Sales tax calculated correctly for salon's state
- ✅ No-show fees auto-charged to credit card on file
- ✅ ACH payouts to staff processed correctly with proper timing
- ✅ Staff commission + tips combined in payout calculations
- ✅ Tax reports generated for admin (monthly/yearly)

**Admin & Analytics:**
- ✅ Admin dashboard shows real-time analytics (revenue, bookings, tax collected, tips)
- ✅ Staff can view earned tips and commission separately
- ✅ Payout history tracked with status updates
- ✅ Customer no-show tracking with auto-blocking after 3+ no-shows

**Technical:**
- ✅ Payment processing via Stripe (test & production)
- ✅ Stripe Connect ACH payouts working
- ✅ Webhook handling for payments and payouts
- ✅ Mobile-responsive design (320px - 1920px)
- ✅ >80% test coverage with passing tests
- ✅ Performance: Page loads < 2 seconds, API responses < 500ms
- ✅ Zero security vulnerabilities in OWASP Top 10
- ✅ PCI DSS Level 1 compliance (no raw card data stored)

---

## 📚 Resources & Recommended Packages

**Packages to Install:**
```bash
# Payment Processing (Stripe - USA)
composer require stripe/stripe-php

# Utilities
composer require laravel/cashier
composer require spatie/laravel-activitylog
composer require spatie/laravel-permission
composer require barryvdh/laravel-ide-helper

# Queue & Background Jobs
composer require laravel/horizon

# Error Tracking
composer require sentry/sentry-laravel

# Encryption for sensitive fields
composer require illuminate/encryption (built-in)
```

**Documentation:**
- Laravel Documentation
- Stripe API Documentation: Stripe Payments, Stripe Connect for ACH, Webhook Handling
- Tailwind CSS Documentation
- Blade Template Documentation
- USA Compliance: PCI DSS Compliance, State Sales Tax Rates, NAICS Codes for Salons

**Useful Tools:**
- Stripe Testing Cards - Test payment processing
- State Sales Tax Calculator - Verify tax rates
- TaxJar API - Automate tax calculation (optional)

---

*End of Complete Specification Document*
