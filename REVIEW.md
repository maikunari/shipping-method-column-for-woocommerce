# Review: wordpress.org submission prep (branch arjuna/shipping-method-column-org-rename-readme)

> **Fix status (2026-08-18, follow-up commit):** at the human's request, all MUST-FIX and SHOULD-FIX findings below have been applied. Findings 1–7: fixed. `Tested up to` was bumped to 7.0 and `WC tested up to` to 11.0 based on the live wordpress.org APIs (WordPress 7.0.4 / WooCommerce 11.0.1 current as of today) — a human should still smoke-test on those versions since this machine cannot run WordPress. The "NEEDS THE HUMAN" items (screenshots, wp.org username, local `php -l`/Plugin Check) remain open. Line numbers below refer to the pre-fix code.

Scope reviewed: commits `be6186c`..`e12744e` (file rename, Text Domain/License headers, string domains, readme rewrite) against the brief "prepare for first wordpress.org submission". Three independent review lenses (correctness, wp.org compliance, security/escaping) were run and consolidated; every finding below was verified against the working tree.

## Verdict on the previous agent's work

The rename itself is **solid and complete**:

- Zero stragglers: repo-wide grep finds no `woocommerce-shipping-method-column`, no "WooCommerce Shipping Method Column", and no gettext call still using the old `woocommerce` text domain.
- `Text Domain: shipping-method-column-for-woocommerce` (shipping-method-column-for-woocommerce.php:15) is byte-identical to the slug and main file name; both gettext calls (:54, :87) use exactly that string.
- The agent also fixed a real pre-existing bug: user-facing strings previously borrowed WooCommerce's own text domain.
- Guideline 17 satisfied everywhere: header (:3), readme title (readme.txt:1), `@package` (:18).
- readme.txt structure is complete and honest: all required header fields and sections present, `Stable tag: 1.0.0` matches `Version: 1.0.0`, short description is 125 chars (<150), tags are plausible search terms, and no invented features — the HPOS limitation is even disclosed.
- DB-preservation rule is trivially satisfied: the plugin persists nothing (no options, transients, or meta), so there were no stored keys to protect.

What follows are issues that predate or survive that work and block/degrade an actual submission.

## MUST-FIX

### 1. Unescaped translated output — Plugin Check blocker
`shipping-method-column-for-woocommerce.php:86-87` — `__('No shipping', …)` is echoed raw. Plugin Check flags this as `WordPress.Security.EscapeOutput.OutputNotEscaped` and the review team treats it as blocking (a malicious translation file can inject HTML/JS into wp-admin).

Fix:
```php
echo '<span style="color: #999;">' .
esc_html__('No shipping', 'shipping-method-column-for-woocommerce') . '</span>';
```

### 2. `Tested up to: 6.3` is ~3 years stale
`readme.txt:5` and `shipping-method-column-for-woocommerce.php:11` — as of August 2026 this is several majors behind. wp.org shows the "not tested with the last 3 major releases" warning, downranks the plugin in search, and Plugin Check flags an outdated tested-up-to header on submission.

Fix: actually test on the current WordPress release and bump both (readme.txt is the authoritative one; use major.minor only, e.g. `6.9`). This needs a human with a test site — do not bump without testing.

### 3. Plugin is inert on HPOS installs (the WooCommerce default since 8.2)
`shipping-method-column-for-woocommerce.php:60`, `:92-97`, `:107` — only the classic `shop_order` post-table hooks are registered. New WooCommerce installs default to High-Performance Order Storage, whose orders screen uses `manage_woocommerce_page_wc-orders_columns` / `manage_woocommerce_page_wc-orders_custom_column` — so on a majority of new 2026 stores the plugin's sole feature never appears. The readme discloses this (readme.txt:21, :50-52), but disclosure doesn't make a single-purpose plugin that does nothing on default installs shippable; expect "doesn't work" reviews immediately.

There is also no HPOS compatibility declaration, so WooCommerce lists the plugin as incompatible/uncertain under Settings → Advanced → Features.

Fix: register the two HPOS hooks (the HPOS custom-column action receives `($column, $order)` with a `WC_Order` object — use it directly instead of `wc_get_order($post_id)`), extend the screen check at :107 to also match `woocommerce_page_wc-orders`, and declare compatibility on `before_woocommerce_init`:
```php
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
```
Then update readme.txt (:21, :50-52) which currently documents the limitation. If HPOS support is deliberately deferred, at minimum declare `false` instead of `true` and keep the readme as-is — but expect the submission to be functionally dead-on-arrival for new stores.

## SHOULD-FIX

### 4. WooCommerce-active check misses multisite network activation
`shipping-method-column-for-woocommerce.php:30-36` — network-activated WooCommerce lives in `active_sitewide_plugins`, not the per-site `active_plugins` option, so the plugin silently bails on multisite. Fix: add the WP 6.5+ header `Requires Plugins: woocommerce` and/or gate on `class_exists('WooCommerce')` from a `plugins_loaded` hook instead of reading the option at file load.

### 5. `WC tested up to: 8.0` is stale
`shipping-method-column-for-woocommerce.php:13` — WooCommerce is far past 8.0; the plugins screen shows an "untested with your version" notice. Retest and bump alongside finding 2.

### 6. Inline `<style>` echo in `admin_head`
`shipping-method-column-for-woocommerce.php:104-118` — static CSS, so no security issue and likely only a Plugin Check warning, but `wp_add_inline_style()` on an admin handle (hooked to `admin_enqueue_scripts`) is the preferred pattern.

### 7. Defense-in-depth on the column label
`shipping-method-column-for-woocommerce.php:54` — the label is returned to a filter, not echoed, so Plugin Check won't flag it, but WP core prints column headers without escaping. Cheap hardening: `esc_html__()` instead of `__()`.

## NEEDS THE HUMAN

- **Screenshots** (readme.txt:62-66): three screenshots are described but no `screenshot-1..3.(png|jpg)` files exist. They aren't part of this repo's zip anyway — they must be committed to the SVN `/assets` directory after approval. Either prepare the three images or delete the section before submitting.
- **Contributors** (readme.txt:2): `mikesewell` must be the exact wordpress.org username of the submitting account, or the profile link 404s. Verify.
- **No PHP lint was possible** — there is no PHP binary on this machine (verified: the previous agent could not lint either; all changed files were re-read end-to-end instead and no syntax issue is visible). Before submitting, run `php -l` and the Plugin Check plugin locally.

## Verified claims from the previous agent

- File rename preserved git history (`--follow` shows the chain back to the initial commit). ✔
- License / License URI headers present and consistent (GPLv2 or later, gnu.org URI) in both files. ✔
- Direct-access `ABSPATH` guard present (:25-27); `get_method_title()` output escaped via `esc_html()` (:84); no state-changing actions, so no nonces/capability checks needed. ✔
