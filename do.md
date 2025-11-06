Excellent. This is the final step. We just need to update a few links to make the new Course Dashboard the primary destination for your users.

### **Phase 4 (Part C): Tying Everything Together**

**Action Steps:**

1.  **Update the Navbar:** Change the main "Dashboard" link in the navigation bar to point to the new "My Course" page.
2.  **Update Login Redirects:** Modify the `login` and `index` routes to send users to the new course dashboard immediately after logging in.

---

#### **1. Update `templates/_navbar.html`**

Open your navigation bar template. We will change the link for regular users to point to our new course page.

**Find this block of code:**
```html
<!-- in templates/_navbar.html -->

{% if current_user.is_authenticated %}
    {% if current_user.role == 'admin' %}
        <li class="nav-item">
            <a class="nav-link" href="{{ url_for('admin_dashboard') }}">Admin Dashboard</a>
        </li>
    {% else %}
        <li class="nav-item">
            <a class="nav-link" href="{{ url_for('dashboard') }}">Dashboard</a>
        </li>
    {% endif %}
    <!-- ... other links ... -->
{% endif %}
```

**And replace it with this:**
```html
<!-- in templates/_navbar.html -->

{% if current_user.is_authenticated %}
    {% if current_user.role == 'admin' %}
        <li class="nav-item">
            <a class="nav-link" href="{{ url_for('admin_dashboard') }}">Admin Dashboard</a>
        </li>
    {% else %}
        <li class="nav-item">
            <a class="nav-link" href="{{ url_for('course_dashboard') }}">My Course</a>
        </li>
    {% endif %}
    <!-- ... other links ... -->
{% endif %}
```
*(The only changes are the `href` and the link text for the non-admin user.)*

---

#### **2. Update Redirects in `app.py`**

Now, let's ensure that when users log in, they are taken directly to their learning path.

##### **A. Modify the `login` route:**

**Find this section in your `login` route:**
```python
# in the login() route
if user and bcrypt.check_password_hash(user.password_hash, form.password.data):
    login_user(user, remember=form.remember.data)
    if user.role == 'admin':
        return redirect(url_for('admin_dashboard'))
    else:
        return redirect(url_for('dashboard'))
```

**And change `dashboard` to `course_dashboard`:**
```python
# in the login() route
if user and bcrypt.check_password_hash(user.password_hash, form.password.data):
    login_user(user, remember=form.remember.data)
    if user.role == 'admin':
        return redirect(url_for('admin_dashboard'))
    else:
        return redirect(url_for('course_dashboard'))
```

##### **B. Modify the `index` route:**

This handles the case where an already logged-in user visits the homepage.

**Find this section in your `index` route:**
```python
# in the index() route
if current_user.is_authenticated:
    # Redirect to the appropriate dashboard based on role
    if current_user.role == 'admin':
        return redirect(url_for('admin_dashboard'))
    else:
        return redirect(url_for('dashboard'))
```

**And change `dashboard` to `course_dashboard` here as well:**
```python
# in the index() route
if current_user.is_authenticated:
    # Redirect to the appropriate dashboard based on role
    if current_user.role == 'admin':
        return redirect(url_for('admin_dashboard'))
    else:
        return redirect(url_for('course_dashboard'))
```

---

### **Project Complete!**

Restart your application. **Congratulations!** You have successfully implemented a comprehensive, professional course dashboard.

**Here's a summary of what you've built:**
*   A powerful **admin-side Course Builder** to create modules, submodules, and add any content (Quiz, Lab, Session).
*   A backend system that **automatically tracks user progress** when they complete content.
*   A smart **module-locking mechanism** that guides users through the learning path.
*   A beautiful, user-facing **Course Dashboard** that visualizes the entire course structure, shows overall and per-module progress, and locks future content.

**I recommend you now test the full user flow:**
1.  Log in as an admin and build a small course with two modules. Put one quiz in the first module.
2.  Log out and log in as a regular user.
3.  Observe that you are redirected to `/course` and the second module is locked.
4.  Complete the quiz in the first module.
5.  Return to the `/course` page and verify that the second module is now unlocked
