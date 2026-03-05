# Phase 04 — Admin UI & Role-Based Access Control

## Context Links
- Plan: [plan.md](plan.md)
- Blueprint §5 Admin Dashboard, RBAC

## Overview
| Item | Detail |
|---|---|
| **Priority** | High |
| **Status** | ⏳ pending |
| **Timeline** | Weeks 4–5 |
| **Depends on** | Phase 01 (CPT + ACF registered) |
| **Blocks** | Phase 05 (data import needs clean admin UX) |

## Key Insights
- Minimalist admin = hide default WordPress menus irrelevant to Alkana content team.
- RBAC: 3 roles only — Administrator, Content Editor (Marketing), Technical Editor (R&D/Safety).
- Lock data inputs to dropdowns/checkboxes = no garbage data from typos.
- All ACF field groups already use select/checkbox — RBAC restricts which groups each role can see/edit.
- Do NOT build a custom admin dashboard from scratch — use ACF + custom capabilities only. KISS principle.

## Requirements

### Functional
- WordPress admin sidebar: hide irrelevant menus (Comments, Appearance > Themes/Customizer, Plugins, Tools > Import/Export for non-admins).
- ACF field group visibility restricted by user role.
- Content Editor: can edit product content (title, description, images, category) — NOT technical specs or PDF uploads.
- Technical Editor (R&D): can edit technical specs + upload TDS/MSDS PDFs — NOT product descriptions.
- Administrator: full access.
- Custom Admin "Welcome" screen with quick links to: Add Product, Add Project, All Products, Taxonomy management.

### Non-Functional
- Admin changes do NOT affect frontend performance.
- Role restrictions enforced server-side (not just UI hints).
- Use built-in `add_cap()` / `remove_cap()` — no admin permission plugins needed.

## Architecture

### Role Definitions
```php
// inc/admin/roles.php (run once on theme activation)

function alkana_setup_roles(): void {
    // Content Editor (Marketing team)
    $editor = get_role('editor') ?? add_role('alkana_content_editor', 'Content Editor', []);
    $editor->add_cap('edit_alkana_products');
    $editor->add_cap('publish_alkana_products');
    $editor->remove_cap('edit_themes');
    $editor->remove_cap('activate_plugins');

    // Technical Editor (R&D)
    $tech = add_role('alkana_tech_editor', 'Technical Editor', [
        'read'                   => true,
        'edit_alkana_products'   => true,
        'upload_files'           => true,  // for TDS/MSDS PDF
    ]);
}
register_activation_hook(__FILE__, 'alkana_setup_roles');
```

### ACF Field Group Visibility by Role
```php
// inc/admin/acf-role-restrictions.php

add_filter('acf/load_field_groups', function($groups) {
    $user = wp_get_current_user();
    $is_tech = in_array('alkana_tech_editor', $user->roles);
    $is_content = in_array('alkana_content_editor', $user->roles)
               || in_array('editor', $user->roles);

    return array_filter($groups, function($group) use ($is_tech, $is_content) {
        $key = $group['key'];
        // Technical specs + Resources: tech editors only
        if (in_array($key, ['group_alkana_specs', 'group_alkana_resources'])) {
            return $is_tech || current_user_can('administrator');
        }
        // Identification + Variants: content editors can access
        return true;
    });
});
```

### Admin Menu Cleanup
```php
// inc/admin/clean-menu.php

add_action('admin_menu', function() {
    if (current_user_can('administrator')) return;
    remove_menu_page('edit-comments.php');
    remove_menu_page('tools.php');
    remove_submenu_page('themes.php', 'themes.php');
    remove_submenu_page('themes.php', 'customize.php');
    remove_menu_page('plugins.php');
}, 999);
```

### Custom Admin Welcome Dashboard
```php
// inc/admin/dashboard.php

add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'alkana_quick_links',
        'Alkana Quick Links',
        function() {
            echo '<ul>
              <li><a href="' . admin_url('post-new.php?post_type=alkana_product') . '">➕ Thêm sản phẩm mới</a></li>
              <li><a href="' . admin_url('edit.php?post_type=alkana_product') . '">📦 Quản lý sản phẩm</a></li>
              <li><a href="' . admin_url('post-new.php?post_type=alkana_project') . '">➕ Thêm công trình mới</a></li>
              <li><a href="' . admin_url('edit-tags.php?taxonomy=product_category') . '">🏷️ Danh mục sản phẩm</a></li>
              <li><a href="' . admin_url('edit-tags.php?taxonomy=surface_type') . '">🎨 Loại bề mặt</a></li>
            </ul>';
        }
    );
}, 1);

// Remove default WP dashboard widgets
add_action('wp_dashboard_setup', function() {
    remove_meta_box('dashboard_activity',      'dashboard', 'normal');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_quick_press',   'dashboard', 'side');
    remove_meta_box('dashboard_primary',       'dashboard', 'side');
}, 20);
```

## Related Code Files
- Create: `inc/admin/roles.php`
- Create: `inc/admin/acf-role-restrictions.php`
- Create: `inc/admin/clean-menu.php`
- Create: `inc/admin/dashboard.php`
- Modify: `functions.php` (require new inc/admin/ files)

## Implementation Steps

1. Create `inc/admin/roles.php` — define 3 roles with appropriate capabilities.
2. Run role setup once via `after_switch_theme` hook (not activation hook, since it's a theme).
3. Implement `inc/admin/clean-menu.php` — hide menus for non-admin roles.
4. Implement `inc/admin/acf-role-restrictions.php` — restrict ACF group visibility by role.
5. Implement `inc/admin/dashboard.php` — quick links widget.
6. Test: login as Content Editor → verify can edit product description but NOT specs.
7. Test: login as Technical Editor → verify can edit specs + upload PDF but NOT product titles.
8. Test: login as Administrator → full access.
9. Create sample user accounts for Alkana team testing (Content Editor + Tech Editor).

## Todo List
- [ ] Define 3 roles with `add_role()` / `add_cap()`
- [ ] Register roles on `after_switch_theme`
- [ ] Admin menu cleanup for non-admin roles
- [ ] ACF field group visibility by role
- [ ] Custom dashboard quick links widget
- [ ] Remove default WP dashboard widgets
- [ ] Test Content Editor role permissions
- [ ] Test Technical Editor role permissions
- [ ] Create sample user accounts for Alkana team UAT

## Success Criteria
- Content Editor cannot see Technical Specs or Resources ACF groups
- Technical Editor cannot change product title/description/categories
- Admin menu is clean with only relevant items for each role
- Quick links dashboard widget visible for all roles
- Adding a product with all fields takes < 5 minutes for trained user

## Risk Assessment
| Risk | Probability | Mitigation |
|---|---|---|
| ACF group filter breaks on ACF update | Low | Lock ACF Pro version in theme dependencies |
| Role caps not applied on existing users | Low | Run `alkana_setup_roles()` via admin notice or WP CLI command |

## Security Considerations
- Never rely on frontend-only role hiding — always enforce via `current_user_can()` checks server-side
- `upload_files` cap granted only to roles that need PDF uploads

## Next Steps
→ Phase 05: Data import uses admin UI to verify product data entry
