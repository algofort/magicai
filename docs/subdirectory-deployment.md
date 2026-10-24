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
