
# Admin Course Management Refactoring Plan

## 1. Consolidate and Organize Code
- [ ] Move all database models to `models.py`.
- [ ] Remove duplicated API endpoints from `app.py`.
- [ ] Use API endpoints from `admin_api_endpoints.py`.
- [ ] Clean up `app.py` by removing old form-based routes.

## 2. Fix Frontend-Backend Mismatch
- [ ] Modify Flask routes in `admin_api_endpoints.py` to handle JSON.
- [ ] Update JavaScript in `static/js/admin_dashboard.js`.

## 3. Improve User Interface
- [ ] Redesign `admin_dashboard.html` and `admin_module_detail.html`.
- [ ] Implement robust drag-and-drop.
- [ ] Add modals for create/edit operations.
