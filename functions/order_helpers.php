<?php
/* =====================================================
   ORDER PRESENTATION HELPERS
   Pure helpers (no DB, no session) shared by the storefront
   order-tracking page and the admin order screens so a status
   always looks and reads the same wherever it is shown. Kept
   side-effect free so any page can include it safely.
   ===================================================== */

if (!function_exists('orderStatuses')) {
    // The fulfilment states an order can move through.
    function orderStatuses() {
        return array('pending', 'processing', 'shipped', 'completed', 'cancelled');
    }
}

if (!function_exists('statusBadge')) {
    // Render a coloured status pill, e.g. statusBadge('paid').
    function statusBadge($status) {
        $status = strtolower(trim($status));
        $safe   = htmlspecialchars($status);
        return "<span class='status_badge status_$safe'>" . ucfirst($safe) . "</span>";
    }
}
