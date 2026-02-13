# AtlasPress Security Policy Matrix

Last updated: 2026-02-13

This matrix documents default access expectations and current enforcement after the hardening pass.

## Global REST Policy (Default)

| Policy | Expected Default | Current Behavior |
|---|---|---|
| Non-public AtlasPress REST routes (`/wp-json/atlaspress/v1/*`) | Require authenticated WP user or valid API key | Enforced in `ApiSecurity::validate_request` + permission callbacks |
| API keys | Per-key scoped permissions + optional expiry/IP allowlists | Enforced via `ApiSecurity::has_valid_api_key(..., $required_scopes)` with status, expiry, and source-IP checks |
| Public routes | Explicit allowlist only | `form-capture` and `hubspot/webhook` are allowlisted |
| CORS | Explicit origins only, no wildcard fallback | Enforced; unknown origins do not get CORS headers, disallowed preflight gets 403 |
| Anonymous request throttling | Per-route/IP (or API key) hourly limits | Enforced in `Security::rate_limit_check` (default: 100 private, 300 public) |
| Custom throttling rules | Pro-only configurable public/private defaults + route overrides | Enforced via `Security::get_effective_rate_limit_rules()`; free/default mode ignores custom rule option |
| Signed request verification | HMAC + timestamp + nonce with replay blocking | Implemented in `ApiSecurity::verify_signed_request`; strict mode default for public ingest endpoints |
| Public read access | Disabled by default | Disabled; can be re-enabled via `atlaspress_allow_public_read` filter |
| Public submission access | Disabled by default | Disabled; can be re-enabled via `atlaspress_allow_public_submit` filter |

## REST Endpoint Matrix

| Endpoint Pattern | Methods | Expected Default Access | Current Enforcement |
|---|---|---|---|
| `/content-types` | `GET, POST` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/content-types/{id}` | `GET, DELETE` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/content-types/{id}/schema` | `PUT` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/content-types/bulk-delete` | `POST` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/field-types` | `GET` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/dashboard` | `GET` | WP user with `atlaspress_view_dashboard` | `Permissions::can_view_dashboard`; baseline stats for all licensed tiers, `advancedAnalytics` payload included when Pro license is active |
| `/content-types/{type_id}/entries` | `GET` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/content-types/{type_id}/entries` | `POST` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_submit_entries` |
| `/content-types/{type_id}/entries` | `DELETE` | WP user with `atlaspress_delete_entries` | `Permissions::can_delete_entries` |
| `/entries/{id}` | `GET, PUT` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/entries/{id}` | `DELETE` | WP user with `atlaspress_delete_entries` | `Permissions::can_delete_entries` |
| `/entries/{id}/duplicate` | `POST` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/entries/bulk-update` | `POST` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/entries/bulk-delete` | `POST` | WP user with `atlaspress_delete_entries` | `Permissions::can_delete_entries` |
| `/upload` | `POST` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/files/{id}` | `DELETE` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/relationships/{type_id}` | `GET` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/search/entries` | `GET` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/export/content-types` | `GET` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/export/entries/{type_id}` | `GET` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/export/{format}/{type_id}` | `GET` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/import/content-types` | `POST` | WP user with `atlaspress_manage_types` | `Permissions::can_manage_types` |
| `/import/entries/{type_id}` | `POST` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/graphql` | `GET, POST` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/entries/poll` | `GET` | WP user `atlaspress_edit_entries` or valid API key | `Permissions::can_edit_entries` |
| `/form-capture` | `POST` | Public ingest endpoint (explicit exception), signed by default | `__return_true` + public route allowlist + signed request verification/replay protection by default (`atlaspress_require_signed_form_capture`), legacy signature compatibility toggle (`atlaspress_allow_legacy_signed_ingest`), secret source `atlaspress_form_capture_secret` (fallback `atlaspress_webhook_secret`) |
| `/hubspot/webhook` | `POST` | Public ingest endpoint (explicit exception), signed by default | `__return_true` + public route allowlist + signed request verification/replay protection by default (`atlaspress_require_signed_hubspot_webhook`), legacy signature compatibility toggle (`atlaspress_allow_legacy_signed_ingest`), secret source `atlaspress_hubspot_secret` (fallback `atlaspress_webhook_secret`) |

## API Key Scope Matrix

| Scope | Permissions Granted |
|---|---|
| `entries.read` | Read entries/relationships, GraphQL queries, entry polling, entry exports |
| `entries.write` | Create/update/duplicate entries and imports requiring edit permission |
| `entries.delete` | Delete entries/files and bulk deletes |
| `types.manage` | Manage content types, schemas, field types, and type imports/exports |
| `dashboard.read` | Access dashboard endpoint |
| `*` | Legacy full access (used for backward compatibility with old key format) |

## API Key Constraints

| Constraint | Behavior |
|---|---|
| `expires_at` | Optional per key; expired keys are rejected as invalid |
| `allowed_ips` | Optional per key; supports exact IP and CIDR ranges (`203.0.113.10`, `198.51.100.0/24`) |
| Constraint updates | Admin can update per-key expiration/IP allowlists via authenticated `save_security_settings` key action (`update_constraints`) |

## Admin AJAX Matrix

| Action | Expected Default Access | Current Enforcement |
|---|---|---|
| `atlaspress_setup` | `manage_options` + nonce | Capability + `wp_verify_nonce(..., 'atlaspress_setup')` |
| `atlaspress_reset_setup` | `manage_options` + nonce | Capability + `wp_verify_nonce(..., 'atlaspress_setup')` |
| `save_security_settings` | `manage_options` + nonce | Capability + `check_ajax_referer('atlaspress_security_settings')` |
| `save_security_settings` (`api_key_action=update_constraints`) | `manage_options` + nonce | Capability + nonce + key name validation + server-side sanitization of `expires_at` and `allowed_ips` |
| `save_security_settings` (`rate_limit_rules`) | `manage_options` + nonce + Pro license for custom rules | Capability + nonce + server-side sanitization; rejected when Pro is inactive |
| `atlaspress_save_webhooks` | `manage_options` + nonce | Capability + `check_ajax_referer('atlaspress_webhooks')` |
| `atlaspress_check_license` | `manage_options` + nonce | Capability + `check_ajax_referer('atlaspress_license_action')` |
| `atlaspress_sync_content` | `manage_network` + nonce | Capability + `check_ajax_referer('atlaspress_network_sync')` |

## Remaining Hardening Opportunities

| Area | Recommended Next Step |
|---|---|
| API keys | Add audit log stream for key lifecycle events (create/rotate/disable/delete) |
| Public ingest secret management | Add admin UI to generate/store/rotate capture/webhook shared secrets |
| Security headers | Migrate away from deprecated `X-XSS-Protection` and add CSP policy |
