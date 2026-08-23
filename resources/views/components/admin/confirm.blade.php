{{-- The "are you sure?" dialog. A single instance for the whole page: any form
     carrying data-confirm="…" is routed through it by admin.js.

     Without JS the forms submit as usual — a confirmation is a courtesy, and
     losing it must not lock the owner out of deleting anything. --}}
<div class="admin-confirm" id="admin-confirm" hidden>
    <div class="admin-confirm-veil" data-confirm-cancel></div>

    <x-frame class="admin-confirm-box" role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title">
        <div class="admin-confirm-inner">
            <span class="admin-confirm-mark" aria-hidden="true">
                <x-icon.admin name="warning" class="h-6 w-6" />
            </span>

            <h2 class="admin-confirm-title" id="admin-confirm-title">حذف تأیید می‌شود؟</h2>
            <p class="admin-confirm-text" data-confirm-text></p>

            <div class="admin-confirm-actions">
                <button type="button" class="admin-btn admin-btn--ghost" data-confirm-cancel>انصراف</button>
                <button type="button" class="admin-btn admin-btn--danger" data-confirm-accept>بله، حذف کن</button>
            </div>
        </div>
    </x-frame>
</div>
