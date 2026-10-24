# Homepage assets and authentication below /magicai

The shared `custom_theme_url` helper now resolves theme and uploaded asset paths
through APP_URL/ASSET_URL rather than returning domain-root paths. External/CDN
URLs and plain display text remain unchanged. Homepage logo srcsets and the
blog/footer links also use application-aware URLs.

The guest authentication script receives its base path from APP_URL in the
rendered Blade page. Login, registration, password reset and local dashboard
redirects use that prefix. Already-prefixed URLs are not prefixed twice. A
non-JSON error response is shown as a normal error instead of throwing while
accessing responseJSON. CSRF, CAPTCHA and email-confirmation checks are unchanged.

## Deploy after syncing to the VPS-authorized repository

Preserve any server edits and the original .env/APP_KEY. Pull the intended commit
from the existing synced remote as the repository owner with appropriate GitHub
access. No SQL import, migrations, seeding, npm build or Composer update is
needed for this patch. It changes PHP helpers, Blade templates and a public JS
file; the guest JS URL includes a modification-time query to refresh the cache.

The existing production configuration must include:

```dotenv
APP_URL=https://products.algofort.com/magicai
ASSET_URL=https://products.algofort.com/magicai
```

After pulling, run as the MagicAI application user from /opt/magicai:

```bash
php8.4 artisan view:clear
php8.4 scripts/test-subdirectory.php
node --test scripts/test-auth-subdirectory.mjs
```

Open the homepage, /magicai/login and /magicai/register with a hard refresh.
Check their images, styles, scripts and auth POST URLs. Authentication requests
must go to /magicai/login or /magicai/register, not the domain root. Test real
registration and any email/CAPTCHA configuration separately after deployment.

Local checks cover root/subdirectory asset generation, external/CDN paths,
duplicate-prefix prevention, auth requests/redirects and non-JSON error recovery.
They do not create a user, send mail, alter activation or consume AI credits.
This patch is not a full audit of every dashboard feature's hardcoded URLs.

## Dashboard chat and settings requests

The panel now loads `subdirectory-requests.js` before its application bundle.
It prefixes root-relative dashboard, Livewire, file-upload and editor-save
requests using APP_URL's path. A jQuery prefilter handles chat creation and
admin saves; the fetch adapter handles chat lists and streamed generation.
Already-prefixed, absolute provider/CDN URLs, other projects' paths and Request
objects remain unchanged. Request bodies, headers, abort signals and response
streams are passed through. No global server configuration changes are needed.

Deploy this update as the MagicAI owner, with normal `umask 022` for Git writes
(use `umask 077` only inside backup operations). Check local changes before
pulling; retain the existing public/storage symlink. No dependency installation,
asset build, migrations, service restarts or database reset is needed. Run:

```bash
php8.4 scripts/test-subdirectory.php
node --test scripts/test-auth-subdirectory.mjs scripts/test-dashboard-subdirectory.mjs
php8.4 artisan view:clear
```

Then hard-refresh AI Chat, create a conversation, select a configured Gemini
model and send one short message. The chat page may initially select an OpenAI
model; adding a Gemini API key does not change that selection. Confirm model
access/credits and API quota separately. A live generation result still needs
verification after this patch is deployed.
