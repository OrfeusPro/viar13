<style>
    .viar-management-page .fi-page-main, .viar-management-page .fi-section { min-width: 0; }
    .viar-management-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
    .viar-management-tabs { display: flex; gap: 8px; padding: 8px; max-width: 100%; overflow-x: auto; background: #eef2f8; border: 1px solid #dbe3ee; border-radius: 12px; }
    .viar-management-tabs > * { flex-shrink: 0; }
    .viar-management-tab { border: 1px solid transparent; padding: 9px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; color: #475569; white-space: nowrap; }
    .viar-management-tab:hover { background: #e0eaff; }
    .viar-management-tab[aria-selected="true"] { background: #2563eb; color: white; }
    .viar-management-tab:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
    .viar-management-scroll { overflow-x: auto; max-width: 100%; border: 1px solid #dbe3ee; border-radius: 10px; }
    .viar-management-table { width: 100%; min-width: 850px; border-collapse: separate; border-spacing: 0; font-size: 14px; }
    .viar-management-table th { padding: 13px 16px; background: #eef2f8; color: #334155; text-align: left; font-weight: 600; white-space: nowrap; }
    .viar-management-table td { padding: 12px 16px; border-top: 1px solid #e5e7eb; vertical-align: middle; }
    .viar-management-table tbody tr:hover td { background: #f8fafc; }
    .viar-management-table th:last-child, .viar-management-table td:last-child { position: sticky; right: 0; width: 112px; min-width: 112px; background: #fff; border-left: 1px solid #e5e7eb; }
    .viar-management-table th:last-child { background: #eef2f8; }
    .viar-management-actions { display: flex; align-items: center; gap: 8px; }
    .viar-management-title { display: flex; align-items: center; gap: 10px; min-width: 180px; font-weight: 500; }
    .viar-management-title svg { width: 20px; height: 20px; flex-shrink: 0; color: #64748b; }
    .viar-management-code { font-family: ui-monospace, monospace; font-size: 12px; color: #64748b; overflow-wrap: anywhere; }
    .viar-management-route { min-width: 200px; max-width: 340px; }
    .viar-management-muted { color: #64748b; font-size: 13px; }
    .viar-management-badge { display: inline-flex; border-radius: 6px; padding: 3px 8px; background: #f1f5f9; color: #64748b; font-size: 12px; }
    .viar-management-badge.is-enabled { background: #ecfdf5; color: #047857; }
    .viar-management-setting-value { min-width: 240px; max-width: 500px; overflow-wrap: anywhere; }
    .viar-setting-label { font-size: 14px; font-weight: 600; margin-bottom: 6px; }
    .viar-setting-image { width: auto; max-width: 160px; max-height: 96px; object-fit: contain; border-radius: 8px; background: #f8fafc; }
    .viar-management-form { display: grid; gap: 24px; }
    .viar-management-form-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .dark .viar-management-tabs, .dark .viar-management-table th { background: #243047; border-color: #334155; color: #e2e8f0; }
    .dark .viar-management-scroll { border-color: #334155; }
    .dark .viar-management-table td:last-child { background: #18181b; }
    .dark .viar-management-table td { border-color: #334155; }
    .dark .viar-management-table tbody tr:hover td { background: #243047; }
    .dark .viar-management-table th:last-child { background: #243047; }
    .dark .viar-management-tab { color: #e2e8f0; }
    .dark .viar-management-tab[aria-selected="true"] { background: #2563eb; }
    .dark .viar-management-tab:hover { background: #334155; }
    .dark .viar-management-muted, .dark .viar-management-code { color: #94a3b8; }
</style>
