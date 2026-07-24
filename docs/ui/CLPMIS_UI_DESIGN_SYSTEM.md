# CLPMIS UI Design System

## Design direction

The interface uses a modern public-service case-management identity rather than a generic admin template.

- Deep mineral teal communicates trust, protection, and formality.
- Warm paper backgrounds reduce visual fatigue during long profiling sessions.
- Restrained amber accents identify attention, review, and important system actions.
- Rounded but structured surfaces improve approachability without making the system informal.
- Grid details reference official records, ledgers, and monitoring work.

## Core interface principles

1. **Role clarity** — navigation and dashboards reflect the tasks available to each role.
2. **Case-first hierarchy** — profile identifiers, workflow status, child information, and assigned responsibility remain visually prominent.
3. **Safe actions** — destructive actions use red, review actions use amber, and completed states use green.
4. **Readable forms** — labels stay above inputs, controls have large click targets, and focus states are visible.
5. **Consistent navigation** — all authenticated modules use the same responsive sidebar and top bar.
6. **Formal reporting** — printable reports use the same teal identity with clean borders and compact typography.
7. **Accessible feedback** — success, warning, validation, empty, and error states are visually distinct.

## Color roles

- Deep ink: navigation, primary headings, secure actions
- Mineral teal: primary actions, active states, links
- Seafoam: selected and informational surfaces
- Warm paper: application background
- Amber: attention, returned records, pending review
- Emerald: approved, active, completed
- Red: archived, inactive, destructive, failed

## Shared files

The system-wide appearance is controlled primarily by:

```text
resources/css/app.css
resources/views/components/workspace-shell.blade.php
resources/views/components/dashboard-shell.blade.php
resources/views/layouts/guest.blade.php
tailwind.config.js
```

Pages using `x-dashboard-shell`, `x-workspace-shell`, `x-app-layout`, and `x-guest-layout` automatically inherit the design.
