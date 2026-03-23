# Test Issue #824 — Mailchimp Site Tracking Pixel

## Install
1. Download `mailchimp-for-wp-tracking-pixel-824.zip`
2. WP Admin → Plugins → Add New → Upload Plugin
3. Activate (or replace existing MC4WP plugin)

## Test

### 1. Settings UI
1. Go to **MC4WP → Other** settings page
2. Verify the "Site Tracking Pixel" section appears with a text input
3. Enter a test tracking pixel ID (e.g., `abc123def456`)
4. Click **Save Changes**
5. Verify the value persists after page reload

### 2. Frontend Script Output
1. Visit any frontend page
2. **View Page Source** (Ctrl+U)
3. Search for `mcjs` — you should find:
   ```html
   <script id="mcjs" defer src="https://mc.mailchimp.com/mcjs/abc123def456.js"></script>
   ```
4. Verify it appears in the `<head>` section

### 3. Script Removal
1. Go back to **MC4WP → Other**, clear the tracking pixel ID
2. Save
3. View frontend source again — the `mcjs` script tag should **not** be present

### 4. Subscriber Identification
1. Configure a valid tracking pixel ID
2. Submit an MC4WP sign-up form on the frontend
3. View the page source of the response — you should see an inline script near `</body>` calling:
   ```js
   window.$mcSite.pixel.api.identify({type:"EMAIL",value:"submitted@email.com"})
   ```

### 5. Existing Functionality
1. Verify MC4WP forms still submit and display success/error messages correctly
2. Verify the admin API Settings page still works

## Requirements
- WordPress 5.0+
- PHP 7.4+
