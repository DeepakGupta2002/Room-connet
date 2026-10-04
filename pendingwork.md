# RoomConnect Pending Work

This file tracks the remaining work after the current MVP foundation.

## P0 — Must complete before real users

### 1. End-to-end authentication testing

- Test registration with valid name, email and phone.
- Confirm browser location permission saves latitude and longitude only after OTP verification.
- Confirm incomplete OTP registration creates no `users` row.
- Test invalid OTP, expired OTP, five failed attempts and resend cooldown.
- Test login using email OTP.
- Test login using registered phone OTP.
- Test duplicate email and duplicate phone validation.
- Test OTP email delivery on the actual deployment server.
- Add automated feature tests for registration, OTP verification and passwordless login.

### 2. Security testing

- Verify that tenant phone numbers never appear in public listing, search, room details or API responses.
- Verify that owner contact is returned only after authenticated and verified unlock.
- Verify that User A cannot edit User B's listing.
- Verify admin and moderator authorization on every admin route.
- Test OTP brute-force protection, resend rate limit and login rate limit.
- Test file upload MIME validation, size limits and unsafe file names.
- Add security headers, production HTTPS and secure cookie settings.
- Rotate all credentials that were exposed during development before production launch.

### 3. Production email configuration

- Confirm SMTP works from the final cPanel/server environment.
- Keep `MAIL_SCHEME=smtp` for Gmail port 587 or configure the selected provider correctly.
- Never commit `.env` or real mail/payment credentials.
- Configure queue/worker or a transactional email provider for reliable OTP delivery.

## P1 — Product completion

### 4. Firebase SMS OTP mode

- Implement Firebase phone OTP when `AUTH_VERIFICATION_MODE=sms`.
- Add Firebase web configuration through environment variables.
- Add phone OTP UI and resend/expiry handling.
- Keep email OTP mode working as the current default.

### 5. Listing workflow improvements

- Add Google Maps autocomplete and current-location support to Edit Listing.
- Add listing image preview, remove/reorder controls and image moderation.
- Add owner approval flow as an optional future feature.
- Add owner/listing confirmation reminders.
- Add duplicate listing detection using owner contact, location and listing similarity.
- Add listing expiry and archival scheduled job.

### 6. Contact unlock workflow

- Add automated tests for free daily limit and donor daily limit.
- Test one-day unlock expiry and one-month donor access expiry.
- Add contact access history for the user.
- Add a clear user-facing limit/retry message when the daily limit is reached.

### 7. Donation workflow

- Complete Razorpay test-mode browser testing with success, cancel and failed payment cases.
- Test signature mismatch, wrong order ID, wrong amount and duplicate payment callbacks.
- Keep manual QR/UTR verification working.
- Add QR image and UPI ID in deployment environment.
- Add donation receipt/confirmation email.
- Razorpay webhook is intentionally skipped for now; enable it before production for reliable payment reconciliation.

## P1 — UI and Stitch alignment

- Complete English copy cleanup on all remaining legacy screens.
- Preserve catchy brand phrases such as `Broker-free`, `Zero Brokerage` and `Direct Connect`.
- Polish Search Results against the Stitch Search screen.
- Polish Room Details against the Stitch Room Details screen.
- Polish Post a Room and Edit Listing screens against Stitch.
- Polish Contact Unlock and Donations screens against Stitch.
- Add consistent authenticated navigation and logout to all relevant pages.
- Add loading, empty, success and error states to every screen.
- Test mobile widths from 320px to 767px.
- Test tablet and desktop layouts from 768px upward.
- Add accessible labels, keyboard focus states and screen-reader-friendly error messages.

## P2 — Admin and operations

- Add admin user management and block/unblock controls.
- Add listing search, filters and pagination in moderation.
- Add report details, moderation notes and action history UI.
- Add donation proof preview/download with access protection.
- Add dashboard charts for listings, unlocks, reports and donations.
- Add audit log filters by user, action, entity and date.
- Add notification center and email notifications for important actions.

## P2 — Performance and SEO

- Add SEO metadata, canonical URLs and Open Graph previews for public room pages.
- Add sitemap and robots configuration.
- Add structured data for room/listing pages where appropriate.
- Add database query/index review after realistic seed volume.
- Add image optimization, responsive thumbnails and lazy loading.
- Add caching for public search and featured listings where safe.
- Run load testing with realistic listing and unlock traffic.

## P2 — Deployment

- Configure Laravel document root to the `public` directory on cPanel.
- Configure PHP version, extensions, Composer and storage symlink.
- Configure production database and run migrations/seeder only as intended.
- Configure `.env`, app key, mail, Google Maps, Razorpay and Firebase values.
- Configure cron for scheduled tasks and queue worker if used.
- Configure SSL, backups, log rotation and monitoring.
- Run a production smoke test after deployment.

## Current verification baseline

- Laravel + React/Inertia application is running locally.
- Database migrations are implemented and run successfully.
- Demo seeder is available through `php artisan db:seed`.
- Passwordless email OTP flow is implemented.
- Owner/tenant listing, contact unlock, moderation and donation foundations are implemented.
- `php artisan test` currently passes the existing baseline tests.
- `npm.cmd run build` currently passes.
- Demo SMTP email was successfully sent after fixing the SMTP scheme configuration.

## Important local testing notes

- Use `php artisan optimize:clear` after changing `.env` or mail configuration.
- Use `php artisan migrate` after pulling new migrations.
- Use `php artisan db:seed` to load demo users and rooms.
- Do not use real credentials in commits, screenshots or logs.
- Do not run the demo seeder against production without reviewing its data first.
