# Development shuru karne se pehle ki checklist

## Current system direction (finalized for MVP)

Platform ko high-level, India-wide scalable room marketplace ke roop mein design kiya jayega. MVP mein kisi bhi area ka verified user room list kar sakta hai; owner approval abhi optional rahega, lekin future owner-verification flow ke liye database aur APIs pehle se ready rahenge.

### User model

- [x] Caretaker role include nahi hoga.
- [x] User roles: `seeker`, `tenant`, `owner`, `admin`, `moderator`.
- [x] Ek user ek se zyada role use kar sakta hai; example: seeker aur tenant.
- [x] Tenant ya owner listing create kar sakta hai.
- [x] Listing creator aur actual property owner ko alag fields/relations mein store kiya jayega.
- [x] Broker/agent role completely banned hoga; broker listings allow nahi hongi.

### Listing and contact rules

- [x] Listing form mein user batayega ki woh `tenant` hai ya `owner`.
- [x] Owner approval MVP mein optional rahega.
- [x] Listing par submitter ka naam/role dikhaya ja sakta hai; private phone, email aur sensitive details public nahi hongi.
- [x] Contact unlock ke baad owner ka verified/consented contact dikhaya jayega; tenant ka number owner contact ke naam par nahi dikhaya jayega.
- [x] Tenant ka phone kisi bhi public ya unlocked contact response mein kabhi show nahi hoga.
- [x] Public listing par role label dikhaya jayega, jaise `Listed by Tenant` ya `Listed by Owner`.
- [x] Public listing par `Owner Contact Available` label dikhaya ja sakta hai.
- [x] Owner approval, phone verification aur admin verification ko alag statuses mein rakha jayega.
- [x] Public listing/search API mein contact phone include nahi hoga.
- [x] Contact unlock ke liye OTP/login aur rate limit mandatory honge.
- [x] Bina login user browse/search kar sakta hai, lekin contact unlock ke liye OTP/login compulsory hoga.

### Listing lifecycle

`listing_status`, `verification_status` aur `approval_status` alag fields honge.

```text
listing_status: draft, active, filled, expired, flagged, deleted
verification_status: unverified, phone_verified, owner_verified, admin_verified
approval_status: not_required, pending, approved, rejected
```

- [x] `leaving_date` tenant ke room chhodne ki date hogi.
- [x] `available_from` seeker ke move-in ki date hogi.
- [x] `expires_at` leaving date ke 15 din baad automatically calculate hoga.
- [x] Leaving date ke baad listing `expires_at` tak visible reh sakti hai, phir `expired` hogi.
- [x] Listing creator, owner ya admin confirmation ke baad listing `filled` hogi; sirf public report se automatically filled nahi hogi.
- [x] Duplicate repost ko warning/review milega; system bina confirmation ke automatically booked mark nahi karega.
- [ ] Exact timezone, same-day availability aur renewal rules finalize karne hain.

### Donation and public social proof

- [x] Donation optional rahega; donation skip karne par bhi contact unlock ho sakta hai.
- [x] Successful payment verification ke baad donor access 30 din ke liye active hoga.
- [x] Donor access ke dauraan bhi daily contact unlock limit apply hogi.
- [x] Failed, cancelled ya unverified payment par donor access nahi milega.
- [x] Active donor period me duplicate donation se unlimited access nahi milega.
- [x] Donation records aur payment status secure/restricted rahenge.
- [x] Donor name public dikhane ke liye explicit opt-in hoga.
- [x] Anonymous donation default option hogi.
- [x] Public donation page par phone, email, payment IDs ya sensitive data nahi dikhaya jayega.
- [ ] Payment gateway, allowed amounts, refund aur webhook rules finalize karne hain.

Contact unlock configuration:

```text
FREE_DAILY_CONTACT_UNLOCK_LIMIT=5
DONOR_DAILY_CONTACT_UNLOCK_LIMIT=10
DONOR_ACCESS_DURATION_DAYS=30
CONTACT_UNLOCK_REQUIRES_LOGIN=true
DONATION_REQUIRED=false
OWNER_APPROVAL_REQUIRED=false
```

- [x] Har listing ke liye user manually `Unlock Contact` click karega.
- [x] Same user ke same listing par duplicate unlock record nahi banega.
- [x] Daily limit sirf naye contact unlocks par apply hogi.
- [x] Pehle se unlocked listing dobara daily limit consume nahi karegi.
- [x] Free aur donor users ki limits environment configuration se control hongi.

### Security and scale level

System ka target level **secure, modular, scalable MVP** hoga—enterprise complexity ke bina strong foundation ke saath.

- [x] OTP, password aur approval tokens hash form mein store honge.
- [x] Phone/owner contact ke liye encrypted value aur separate blind hash rakha jayega: `phone_encrypted`, `phone_hash`, `phone_verified_at`.
- [x] `phone_hash` uniqueness/search ke liye indexed hoga; encrypted phone public response mein nahi jayega.
- [x] Owner details aur required sensitive data encryption/restricted access ke saath store honge.
- [x] Har sensitive API par authentication, authorization aur ownership checks honge.
- [x] OTP, login, listing, report aur contact actions par rate limits honge.
- [x] Activity logs aur admin audit logs maintain honge; OTP/password/payment secrets logs mein nahi jayenge.
- [x] Images/file uploads validate, size-limit aur optimized honge.
- [x] Soft delete, foreign keys, unique constraints aur query-based composite indexes use honge.
- [ ] Backup, restore, secret storage, monitoring aur deployment environment finalize karne hain.

### SEO and location foundation

- [x] User manually slug enter nahi karega.
- [x] Backend city, area, room type aur unique ID se sanitized SEO slug generate karega.
- [x] Duplicate slug par unique suffix generate hoga.
- [x] Old slug ke liye redirect/canonical handling rakhi jayegi.
- [x] Location fields structured honge: country, state, city, area, locality, pincode, latitude, longitude.
- [ ] Exact address/map privacy level aur SEO metadata rules finalize karne hain.

### Core database modules

```text
users, roles, user_roles, posts, post_images, property_owners,
owner_approvals, contact_unlocks, favorites, reports, donations,
donation_public_profiles, login_logs, activity_logs, notifications,
blocked_users, blocked_ips, rate_limit_events, listing_views
```

Listing/contact ownership fields:

```text
listed_by_user_id
listed_by_role
owner_id nullable
owner_name
owner_phone_encrypted
owner_phone_hash
contact_consent_at
expires_at
```

Expected unique constraints:

```text
users.phone_hash
posts.slug
favorites(user_id, post_id)
contact_unlocks(user_id, post_id)
reports(user_id, post_id)
```

`users.phone_encrypted` ko direct unique nahi rakha jayega; normalized `phone_hash` par unique index hoga.

## Implementation readiness status

- [x] MVP business direction defined.
- [x] Open listing model defined.
- [x] Optional owner approval defined.
- [x] Tenant/owner contact distinction defined.
- [x] Donation privacy direction defined.
- [x] Scalable schema direction defined.
- [ ] Backend/frontend/database stack choose karna hai.
- [ ] OTP provider and exact limits choose karne hain.
- [ ] API contract and error format likhna hai.
- [ ] Exact database data types/migrations likhne hain.
- [x] Test areas define hain; detailed automated test cases implementation ke saath likhne hain.

> Note: Upar diya gaya **Current system direction** section latest agreed decision hai. Neeche wali original checklist ko implementation ke dauraan detail-level follow-up tasks ke roop mein use karein; agar kisi point par conflict ho, to upar wala finalized direction apply hoga.

Yeh checklist `buinesslogic.md`, `frontend.md` aur `schema.md` ke analysis par based hai. Abhi workspace mein planning documents hain; application code nahi hai. Neeche diye gaye decisions finalize karke existing documents mein update karein.

## 1. MVP ka scope finalize karein

- [ ] Platform ka naam aur target launch city/cities decide karein.
- [x] Existing tenants aur owners dono listing kar sakte hain.
- [x] MVP features: search, listing details, OTP login, room posting, listing management, contact unlock, favorites aur basic moderation.
- [x] Donation optional rahega aur skip karne par contact unlock possible hoga; successful donor payment se 30 din ka donor access milega.
- [ ] Notifications, advanced trust score aur detailed analytics ko MVP mein rakhna hai ya later phase mein, mark karein.
- [x] Browsing bina login allowed; posting, favorites, reporting aur contact unlock ke liye login/OTP required hoga.

## 2. Listing ki dates aur status rules correct karein

Current documents mein `leaving_date` ke baad listing expire hoti hai. Room tenant ke jaane ke baad available ho sakta hai, isliye expiry ko leaving date se alag define karna zaroori hai.

- [x] `leaving_date`: existing tenant kab room chhodega.
- [x] `available_from`: seeker kab move in kar sakta hai.
- [x] `expires_at`: `leaving_date + 15 days`, jab tak listing filled na ho.
- [ ] Already available rooms aur same-day dates ke exact validation rules finalize karein.
- [ ] Allowed statuses aur transitions define karein, jaise draft → active → filled/expired; review/rejected states moderation ke hisaab se add karein.
- [ ] Filled, expired aur flagged listings par detail page/contact unlock ka exact behavior finalize karein.
- [ ] Listing renewal, editing aur deletion ke rules define karein.
- [ ] Date comparisons ke liye application timezone fix karein.

## 3. OTP aur user verification ka flow finalize karein

- [ ] OTP provider select karein aur development/testing setup decide karein.
- [ ] OTP validity, resend cooldown, maximum attempts aur request limits ke exact values likhein.
- [ ] Phone number normalization aur unique phone rule define karein.
- [x] Owner contact alag encrypted field aur verified/hash value ke saath store hoga; contact unlock ke baad owner ka consented number return hoga.
- [ ] Phone change hone par re-verification aur existing listings ka behavior define karein.
- [ ] Session-based authentication ya token-based authentication choose karein.
- [ ] Blocked users, logout aur expired sessions ka behavior document karein.

## 4. Contact unlock aur optional donation clear karein

- [x] Flow: View Contact → OTP login → optional donation/skip → authorization check → contact unlock.
- [x] Donation skip karne par contact free unlock hoga.
- [x] Already unlocked listing par popup repeat nahi hoga.
- [x] Public listing/search responses mein contact phone include nahi hoga; authorized endpoint se contact return hoga.
- [x] Contact unlock requests par free/donor daily limits environment configuration se enforce hongi.
- [ ] Contact history mein edited, deleted, filled aur blocked listings ka behavior decide karein.
- [ ] Agar donation MVP mein hai, gateway, currency, allowed amounts aur failed/cancelled payment flow define karein.
- [ ] Payment order ID, payment ID, status aur verified webhook processing ka design karein.
- [ ] Duplicate payment callbacks safe tarike se handle hon; failed payment contact access ko block na kare.

## 5. Database schema implementation-ready banayein

- [ ] Har field ka data type, nullable rule aur default value define karein.
- [ ] Rent, deposit, maintenance aur donation amounts ke liye fixed-precision storage choose karein.
- [ ] Foreign keys aur related records ke delete/archive rules define karein.
- [x] Normalized `users.phone_hash` aur `posts.slug` par unique constraints define honge.
- [x] `favorites`, `contact_unlocks` aur `reports` par `(user_id, post_id)` unique constraints define honge.
- [ ] Room type, rent type, user status aur listing status ke allowed values define karein.
- [ ] Images ke display order, cover image aur maximum count ka design karein.
- [x] `expires_at` aur moderation-related fields required hain.
- [ ] Dashboard views aur notifications MVP mein hain to unka storage/logic add karein.
- [ ] Search queries ke hisaab se indexes plan karein; sirf har field ka separate index banane par depend na karein.
- [ ] Rate limiting ke liye Redis/framework limiter ya database approach choose karein; duplicate mechanisms avoid karein.

## 6. Reporting, moderation aur trust rules finalize karein

- [ ] Duplicate reports reject karein aur unique reporters count karein.
- [ ] Three reports par review ya temporary hiding ka exact rule decide karein.
- [ ] Admin ke liye report review, listing restore/reject aur user block actions define karein.
- [ ] Report reasons aur moderation status define karein.
- [x] Duplicate listing detection aur posting limit abuse prevention ka part hoga; exact numeric values configuration me rakhi jayengi.
- [ ] Trust score use karna hai to formula aur update triggers define karein.
- [ ] Phone verification aur room verification ke badges alag meanings rakhein.

## 7. Frontend requirements consolidate karein

- [ ] `frontend.md` mein duplicate dashboard specification consolidate karein.
- [ ] Post Room form mein dates, deposit, maintenance, room type aur contact fields complete karein.
- [ ] Location permission deny/error hone par manual city/area entry available rakhein.
- [ ] Search filters ke allowed values aur default sorting finalize karein.
- [ ] Search aur dashboard pagination values clearly specify karein; current documents mein 20 aur 10 per page hain.
- [ ] Loading, empty, validation, OTP failure, payment failure aur retry states define karein.
- [ ] Mobile navigation aur contact unlock history ka access clear karein.
- [ ] Form labels, keyboard access aur readable contrast ki requirements include karein.
- [ ] Exact address/map pin public kitna dikhana hai aur photo privacy rules decide karein.

## 8. Technical setup aur API contract decide karein

- [ ] Backend, frontend, database aur deployment stack choose karein.
- [ ] Image storage, upload limits, supported formats aur image optimization approach choose karein.
- [ ] API endpoints, request fields, response fields aur error format document karein.
- [ ] Listing ownership checks define karein: sirf authorized owner/admin edit ya delete kar sake.
- [ ] Image validation, OTP abuse protection aur contact access checks backend par enforce karein.
- [ ] Redis use karna hai to cache keys, TTL aur invalidation triggers define karein.
- [ ] Listing edit, filled, expiry aur flagging par stale search results invalidate hon.
- [ ] Daily expiry job, failed-job handling aur logs ka setup plan karein.
- [ ] Environment variables, secret storage, backups aur restore procedure define karein.

## 9. Development start karne ka acceptance checklist

Implementation shuru karne se pehle neeche ke scenarios ka expected result likha hona chahiye:

- [ ] User bina login rooms browse kar sakta hai.
- [ ] Location permission reject hone par manual search chalti hai.
- [ ] Verified user valid room listing create kar sakta hai.
- [ ] Unverified alternate phone listing contact nahi ban sakta.
- [ ] Listing available hone par sirf leaving date ke kaaran disappear nahi hoti.
- [ ] Donation skip ya payment fail hone par bhi free contact unlock possible hai.
- [ ] Contact phone public API response mein expose nahi hota.
- [ ] Repeat unlock duplicate record/popup create nahi karta.
- [ ] Dusra user kisi aur ki listing edit/delete nahi kar sakta.
- [ ] Ek user repeat reports se report count nahi badha sakta.
- [ ] Filled, expired aur flagged listings chosen visibility rules follow karti hain.
- [ ] Listing changes ke baad cached search updated result dikhati hai.

## Suggested working order

1. MVP scope aur listing lifecycle finalize karein.
2. OTP, contact privacy, donation aur moderation decisions complete karein.
3. Schema aur API contract update karein.
4. Frontend specification consolidate karein.
5. Technical setup ready karein aur acceptance scenarios approve karein.
6. Implementation shuru karein: authentication → listings → search → contact unlock → dashboard → moderation → donation, agar MVP mein included ho.

Checklist mein unresolved decisions hone par unhe explicitly pending mark karein. Development ke dauraan unhe silently assume karne ke bajay pehle clear karein.
