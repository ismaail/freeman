# Design Decisions

_This file is auto-imported by CLAUDE.md. Log every significant architectural decision here with the reason._

---

## DD-001 — SQLite as the only database
**Decision:** Ship with SQLite only. No MySQL/PostgreSQL support.
**Reason:** Simplifies self-hosting dramatically. Target users are small teams running this on a VPS or local machine. SQLite is more than sufficient for the expected data volume (API request collections, not transactional data).

---

## DD-002 — Laravel proxies all HTTP requests via Guzzle
**Decision:** All outgoing API calls go through `RequestRunnerService` on the server, not the browser.
**Reason:** Browser-based fetch hits CORS restrictions on most APIs, making it unusable as an API client. Server-side proxying bypasses CORS entirely and enables server-side logging, response storage, and response time measurement.

---

## DD-003 — No frontend build step (CDN only)
**Decision:** Tailwind CSS and Alpine.js loaded via CDN. No Vite, no npm for frontend.
**Reason:** This is an open-source self-hosted tool. Eliminating the build step makes contribution and deployment simpler. Trade-off is no tree-shaking on Tailwind, which is acceptable for an internal tool.

---

## DD-004 — Session-based auth (not JWT)
**Decision:** Standard Laravel session auth via `Auth::attempt()`.
**Reason:** This is a web app with Blade views, not a decoupled SPA consuming an API. Sessions are simpler, more secure for this use case, and require no token management.

---

## DD-005 — Per-user isolated workspaces (no sharing in v1)
**Decision:** Collections, environments, and history are all scoped to the owning user. No sharing between users in v1.
**Reason:** Sharing requires permissions, conflict resolution, and visibility rules — significant complexity for v1. Sharing can be added in v2 as an optional feature.

---

## DD-006 — Super Admin creates all users (no self-registration)
**Decision:** Only Super Admin can create user accounts. No public registration.
**Reason:** This is a self-hosted internal tool. The deployer (Super Admin) controls who has access. Self-registration would be a security risk for teams exposing this on a network.

---

## DD-007 — Postman-compatible collection export format
**Decision:** Export collections as Postman Collection v2.1 JSON format.
**Reason:** Allows users to migrate from Postman without losing their saved requests. Also enables importing existing Postman collections into Freeman.

---

## DD-008 — No pre-request or test scripts in v1
**Decision:** Skip JavaScript scripting (pre-request scripts, test assertions) for v1.
**Reason:** Requires sandboxed JS execution on the server — significant complexity and security surface area. Not core to v1 usefulness. Planned for v2.

---

## DD-009 — `{{variable}}` syntax for environment variables
**Decision:** Use double-curly `{{VARIABLE_NAME}}` syntax in URLs, headers, and body — matching Postman's syntax.
**Reason:** Familiar to Postman users. Makes imported Postman collections work without modification.