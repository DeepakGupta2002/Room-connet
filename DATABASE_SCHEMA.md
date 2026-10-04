# RoomConnect Database Schema Reference

Yeh file current Laravel migrations aur finalized RoomConnect business logic ka reference hai. Isme tables, columns, relations, stored information, indexes aur future review points diye gaye hain.

## Relationship overview

```text
users
  ├── role_user ── roles
  ├── posts (listed_by_user_id)
  ├── property_owners (optional user_id)
  ├── favorites
  ├── contact_unlocks
  ├── reports
  ├── donations
  ├── login_logs
  ├── activity_logs
  ├── notifications
  └── listing_views

posts
  ├── post_images
  ├── contact_unlocks
  ├── favorites
  ├── reports
  ├── donations (optional)
  ├── owner_approvals
  └── listing_views

property_owners
  ├── posts
  └── owner_approvals
```

## RoomConnect tables

### 1. `roles`

Purpose: System roles store karna.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Role ID |
| `name` | varchar(32), unique | `seeker`, `tenant`, `owner`, `moderator`, `admin` |
| `display_name` | varchar(64) | UI me dikhne wala role name |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Broker, agent aur caretaker roles intentionally create nahi kiye gaye.

### 2. `role_user`

Purpose: User aur roles ka many-to-many relation.

| Column | Type | Information |
|---|---|---|
| `user_id` | foreign key | `users.id` se connected |
| `role_id` | foreign key | `roles.id` se connected |
| `created_at` / `updated_at` | timestamp | Assignment timestamps |

Primary key: `user_id + role_id`.

### 3. `property_owners`

Purpose: Actual room/property owner ka record. Owner platform user ho sakta hai ya external person.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Owner ID |
| `user_id` | nullable foreign key | Registered owner ho to `users.id` |
| `name` | varchar(120) | Owner name |
| `phone_encrypted` | varchar(500), nullable | Encrypted owner phone |
| `phone_hash` | varchar(64), nullable | Search/uniqueness ke liye blind hash |
| `phone_verified_at` | timestamp, nullable | Owner phone verification time |
| `contact_consent_at` | timestamp, nullable | Contact show karne ki consent |
| `status` | varchar(32) | `unverified`, `verified`, `blocked` etc. |
| `created_at` / `updated_at` | timestamp | Record timestamps |
| `deleted_at` | timestamp, nullable | Soft delete |

### 4. `posts`

Purpose: Main room listing table.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Listing ID |
| `listed_by_user_id` | foreign key | Listing create karne wala tenant/owner; `users.id` |
| `owner_id` | nullable foreign key | Actual owner; `property_owners.id` |
| `contact_user_id` | nullable foreign key | Owner registered user ho to `users.id` |
| `listed_by_role` | varchar(32) | `tenant` ya `owner`; broker blocked |
| `title` | varchar(180) | Listing title |
| `description` | text | Room description |
| `rent_amount` | decimal(12,2) | Monthly/selected rent |
| `rent_type` | varchar(32) | Normally `monthly` |
| `security_deposit` | decimal(12,2), nullable | Security deposit |
| `maintenance_charge` | decimal(12,2), nullable | Maintenance amount |
| `room_type` | varchar(32) | Single, shared, 1BHK etc. |
| `leaving_date` | date, nullable | Current tenant room kab chhodega |
| `available_from` | date | New tenant kab move-in kar sakta hai |
| `expires_at` | date | Listing search se kab expire hogi; normally leaving date + 15 days |
| `country` | varchar(80) | Default India |
| `state` | varchar(100), nullable | State |
| `city` | varchar(100) | City |
| `area` | varchar(120) | Main area |
| `locality` | varchar(120), nullable | Locality/colony |
| `pincode` | varchar(12), nullable | Pincode |
| `approximate_address` | text, nullable | Public-safe approximate address |
| `latitude` | decimal(10,7), nullable | Approximate/map latitude |
| `longitude` | decimal(10,7), nullable | Approximate/map longitude |
| `location_radius_meters` | smallint | Public location privacy radius; default 400m |
| `slug` | varchar(220), unique | Backend-generated SEO slug |
| `listing_status` | varchar(32) | `draft`, `active`, `filled`, `expired`, `flagged`, `deleted` |
| `verification_status` | varchar(32) | `unverified`, `phone_verified`, `owner_verified`, `admin_verified` |
| `approval_status` | varchar(32) | `not_required`, `pending`, `approved`, `rejected` |
| `trust_score` | tinyint | Trust score value |
| `is_flagged` | boolean | Moderation visibility flag |
| `last_owner_confirmed_at` | timestamp, nullable | Last availability confirmation |
| `next_confirmation_at` | timestamp, nullable | Next confirmation reminder |
| `created_at` / `updated_at` | timestamp | Record timestamps |
| `deleted_at` | timestamp, nullable | Soft delete |

Important: Public response me owner/tenant phone aur exact address include nahi honge.

### 5. `post_images`

Purpose: Listing ki multiple room images.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Image ID |
| `post_id` | foreign key | `posts.id` |
| `image_path` | varchar(500) | Storage path |
| `mime_type` | varchar(80), nullable | Image MIME type |
| `file_size` | unsigned int, nullable | File size |
| `display_order` | smallint | Gallery order |
| `is_cover` | boolean | Cover image flag |
| `created_at` / `updated_at` | timestamp | Record timestamps |

### 6. `donations`

Purpose: Optional contribution aur donor access tracking.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Donation ID |
| `user_id` | foreign key | Donor; `users.id` |
| `post_id` | nullable foreign key | Optional listing context; `posts.id` |
| `amount` | decimal(12,2) | Donation amount |
| `currency` | char(3) | Default INR |
| `provider` | varchar(40), nullable | Payment provider |
| `order_id` | varchar(150), nullable, unique | Provider order ID |
| `payment_id` | varchar(150), nullable, unique | Verified payment ID |
| `status` | varchar(32) | `pending`, `verified`, `failed`, `cancelled`, `refunded` |
| `verified_at` | timestamp, nullable | Webhook verification time |
| `access_granted_until` | timestamp, nullable | Donor access expiry; normally 30 days |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Donation successful hone par donor access milega, lekin daily donor limit phir bhi apply hogi.

### 7. `donation_public_profiles`

Purpose: Public donation page par donor identity/privacy control.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Profile ID |
| `donation_id` | unique foreign key | `donations.id` |
| `public_name` | varchar(120), nullable | Opt-in public name |
| `is_anonymous` | boolean | Default true |
| `show_amount` | boolean | Amount public dikhana hai ya nahi |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Phone, email, payment ID aur private donor information public nahi hogi.

### 8. `contact_unlocks`

Purpose: Kis user ne kis listing ka owner contact unlock kiya.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Unlock ID |
| `user_id` | foreign key | Contact unlock karne wala user |
| `post_id` | foreign key | Listing |
| `donation_id` | nullable foreign key | Related verified donation |
| `unlocked_at` | timestamp | Unlock time; daily limit isi se calculate hogi |
| `access_expires_at` | timestamp, nullable | Contact access policy expiry |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Unique rule: Ek user ek listing ko duplicate unlock nahi kar sakta.

### 9. `favorites`

Purpose: User ke saved rooms.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Favorite ID |
| `user_id` | foreign key | User |
| `post_id` | foreign key | Listing |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Unique rule: `user_id + post_id`.

### 10. `reports`

Purpose: Fake, spam, duplicate ya inappropriate listing reports.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Report ID |
| `post_id` | foreign key | Reported listing |
| `user_id` | foreign key | Reporting user |
| `reason` | varchar(64) | Report reason |
| `details` | text, nullable | Extra explanation |
| `status` | varchar(32) | `open`, `reviewing`, `resolved`, `rejected` |
| `reviewed_by` | nullable foreign key | Admin/moderator user |
| `reviewed_at` | timestamp, nullable | Review time |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Unique rule: Same user same listing ko repeat report nahi kar sakta.

### 11. `owner_approvals`

Purpose: Future owner approval flow; MVP me optional.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Approval ID |
| `post_id` | foreign key | Listing |
| `owner_id` | nullable foreign key | Property owner |
| `token_hash` | unique varchar | Secure single-use approval token hash |
| `status` | varchar(32) | `pending`, `approved`, `rejected`, `expired` |
| `expires_at` | timestamp | Token expiry |
| `used_at` | timestamp, nullable | Token use time |
| `rejection_reason` | text, nullable | Owner rejection reason |
| `created_at` / `updated_at` | timestamp | Record timestamps |

### 12. `login_logs`

Purpose: OTP/login attempts and suspicious access tracking.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Log ID |
| `user_id` | nullable foreign key | User, if identified |
| `phone_hash` | varchar(64), nullable | Phone blind hash; raw phone nahi |
| `ip_address` | varchar(45), nullable | Request IP |
| `device` | text, nullable | Device/user-agent information |
| `status` | varchar(32) | `otp_sent`, `success`, `failed`, `blocked` |
| `created_at` | timestamp | Attempt time |

### 13. `blocked_users`

Purpose: Suspicious/abusive user blocking.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Block ID |
| `user_id` | unique foreign key | Blocked user |
| `reason` | varchar(160) | Block reason |
| `blocked_until` | timestamp, nullable | Temporary block expiry; null can mean indefinite |
| `blocked_by` | nullable foreign key | Admin/moderator |
| `created_at` / `updated_at` | timestamp | Record timestamps |

### 14. `blocked_ips`

Purpose: Malicious IP blocking.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Block ID |
| `ip_address` | varchar(45), unique | IPv4/IPv6 |
| `reason` | varchar(160) | Block reason |
| `blocked_until` | timestamp, nullable | Block expiry |
| `created_at` / `updated_at` | timestamp | Record timestamps |

### 15. `rate_limit_events`

Purpose: OTP, login, listing, report aur contact actions ke rate limits.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Event ID |
| `subject_hash` | varchar(128) | User/IP/device ka blind identifier |
| `action` | varchar(64) | Example: `otp_send`, `contact_unlock` |
| `count` | unsigned int | Current window count |
| `window_started_at` | nullable timestamp | Current rate-limit window start |
| `last_request_at` | nullable timestamp | Last request time |
| `created_at` / `updated_at` | timestamp | Record timestamps |

Unique rule: `subject_hash + action`.

### 16. `activity_logs`

Purpose: Debugging, fraud detection and admin audit.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | Log ID |
| `user_id` | nullable foreign key | Actor user |
| `action` | varchar(100) | Example: `listing_created`, `contact_unlocked` |
| `entity_type` | varchar(100), nullable | Affected model name |
| `entity_id` | unsigned bigint, nullable | Affected record ID |
| `meta_data` | JSON, nullable | Safe event metadata |
| `ip_address` | varchar(45), nullable | Request IP |
| `created_at` | timestamp | Event time |

Raw OTP, passwords, payment secrets aur complete private contact logs me store nahi honge.

### 17. `notifications`

Purpose: Owner reminders, report updates, donor receipts and system alerts.

| Column | Type | Information |
|---|---|---|
| `id` | UUID | Notification ID |
| `user_id` | foreign key | Receiver |
| `type` | varchar(100) | Notification class/type |
| `title` | varchar(180) | Notification heading |
| `data` | JSON, nullable | Safe payload |
| `read_at` | timestamp, nullable | Read time |
| `created_at` / `updated_at` | timestamp | Record timestamps |

### 18. `listing_views`

Purpose: Dashboard views and listing analytics.

| Column | Type | Information |
|---|---|---|
| `id` | bigint | View ID |
| `post_id` | foreign key | Viewed listing |
| `user_id` | nullable foreign key | Logged-in viewer, if available |
| `session_hash` | varchar(128), nullable | Anonymous session hash |
| `ip_hash` | varchar(128), nullable | Abuse-safe IP hash |
| `viewed_at` | timestamp | View time |

## Laravel system tables

These Laravel framework tables hain; inhe RoomConnect business data ke liye directly modify nahi karna chahiye.

| Table | Purpose |
|---|---|
| `users` | User identity; RoomConnect fields migration se add kiye gaye hain |
| `password_reset_tokens` | Laravel password reset support |
| `sessions` | Login sessions |
| `cache` / `cache_locks` | Database cache driver |
| `jobs` / `job_batches` / `failed_jobs` | Queued jobs and failures |
| `migrations` | Migration history |

## Current schema review

### Abhi extra table mandatory nahi hai

Current MVP ke liye 18 RoomConnect tables sufficient hain. OTP ko Redis/cache ya rate-limit service me handle kiya ja sakta hai; raw OTP database me store nahi hoga.

### Future optional tables

Ye tables traffic aur feature need ke baad add kiye ja sakte hain:

1. `otp_challenges` — sirf tab jab OTP audit/attempt state Redis ke bajay database me rakhni ho.
2. `listing_status_history` — listing ke har status transition ka complete history chahiye to.
3. `post_amenities` / `amenities` — searchable amenities ko normalized relation banana ho to.
4. `saved_searches` — user ko saved search alerts dene ho to.
5. `owner_contact_requests` — direct owner approval before phone reveal add karna ho to.
6. `payments_webhook_events` — payment provider callbacks ka immutable idempotency log rakhna ho to.

## Important implementation rules

- Encrypted phone values ko direct unique index mat do; `phone_hash` par uniqueness rakho.
- Public listing/search response me `phone_encrypted`, `phone_hash`, exact address aur payment data kabhi return na karo.
- `listed_by_user_id` listing creator hai; `owner_id` actual property owner hai.
- Tenant listing kar sakta hai, lekin contact response me tenant phone kabhi nahi aayega.
- `approval_status = not_required` MVP ke liye valid hai; future me same column se approval mandatory kiya ja sakta hai.
- `listing_status`, `verification_status` aur `approval_status` ko mix mat karo.
- Reports me unique reporters count karo; same user ki repeat report reject karo.
- Migrations ke baad Models, Policies, Form Requests aur API Resources me same ownership/privacy rules enforce karne honge.
