# Project Context

_This file is auto-imported by CLAUDE.md. Keep it updated after every feature._

---

## Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| username | string unique | Login identifier |
| password | string | Bcrypt hashed |
| is_super_admin | boolean | Default false |
| must_change_password | boolean | True on first login |
| created_at / updated_at | timestamps | |

### `collections`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | Owner |
| name | string | |
| description | string nullable | |
| created_at / updated_at | timestamps | |

### `collection_folders`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| collection_id | FK → collections | |
| parent_folder_id | FK → self nullable | Nested folders |
| name | string | |
| created_at / updated_at | timestamps | |

### `requests`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| collection_id | FK → collections nullable | Null = unsaved/scratch |
| folder_id | FK → collection_folders nullable | |
| user_id | FK → users | Owner |
| name | string | |
| method | enum | GET, POST, PUT, PATCH, DELETE |
| url | string | Can contain `{{variable}}` placeholders |
| headers | json nullable | Array of {key, value, enabled} |
| body_type | enum nullable | none, raw, form-data, x-www-form-urlencoded |
| body | text nullable | Raw body content |
| auth_type | enum nullable | none, bearer, basic, api_key |
| auth_data | json nullable | Stores token/credentials |
| created_at / updated_at | timestamps | |

### `environments`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | Owner |
| name | string | e.g. "Production", "Local" |
| is_active | boolean | Only one active per user |
| created_at / updated_at | timestamps | |

### `environment_variables`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| environment_id | FK → environments | |
| key | string | e.g. "BASE_URL" |
| value | string | e.g. "https://api.example.com" |
| enabled | boolean | Default true |

### `request_logs` (Request History)
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | |
| request_id | FK → requests nullable | Null if run from scratch |
| method | string | |
| url | string | Final URL after variable substitution |
| request_headers | json | |
| request_body | text nullable | |
| response_status | integer | HTTP status code |
| response_headers | json | |
| response_body | text | Raw response |
| response_time_ms | integer | Round-trip time in ms |
| executed_at | timestamp | |

---

## Services

| Service | Location | Responsibility |
|---|---|---|
| `RequestRunnerService` | `app/Services/RequestRunnerService.php` | Executes outgoing HTTP calls via Guzzle, substitutes env variables, logs to `request_logs` |
| `EnvironmentService` | `app/Services/EnvironmentService.php` | Resolves active environment, substitutes `{{variables}}` in URLs/headers/body |
| `CollectionExportService` | `app/Services/CollectionExportService.php` | Exports collection to JSON (Postman-compatible format) |
| `CollectionImportService` | `app/Services/CollectionImportService.php` | Imports Postman collection JSON |

---

## Key Routes (planned)

```
GET  /                          → redirect to /workspace
GET  /workspace                 → main app view (Blade SPA-like)
POST /run                       → execute a request (RequestRunnerService)
GET  /collections               → list user's collections
POST /collections               → create collection
GET  /collections/{id}/export   → download collection JSON
POST /collections/import        → upload + import collection JSON
GET  /environments              → list environments
POST /environments/{id}/activate → set active environment
GET  /history                   → request history log
GET  /admin/users               → super admin user management
POST /admin/users               → create user
DELETE /admin/users/{id}        → delete user
```

---

## Module Status

| Module | Status |
|---|---|
| Laravel scaffold + SQLite setup | ✅ Done |
| Database migrations (all tables) | ✅ Done |
| Auth (login/logout/change password) | ✅ Done |
| Super Admin user management | ⏳ Not started |
| Collections + folders | ⏳ Not started |
| Request builder UI | ⏳ Not started |
| Request execution (Guzzle proxy) | ⏳ Not started |
| Environments + variables | ⏳ Not started |
| Request history | ⏳ Not started |
| Import/Export collections | ⏳ Not started |
| Auth helpers (Bearer/Basic/API Key) | ⏳ Not started |
| Response pretty-print (JSON/XML) | ⏳ Not started |