@php
    $admin = Auth::guard('admin')->user();
    $canAdd = $admin?->hasModuleAccess('post', 'add');
    $canEdit = $admin?->hasModuleAccess('post', 'edit');
    $canDelete = $admin?->hasModuleAccess('post', 'delete');

    $statCards = [
        ['Total Posts', $summary['total'], 'fe-file-text'],
        ['Published', $summary['published'], 'fe-check-circle'],
        ['Draft & Review', $summary['draft_review'], 'fe-edit-3'],
        ['Scheduled', $summary['scheduled'], 'fe-calendar'],
    ];

    $statusOptions = ['draft', 'review', 'scheduled', 'published', 'archived'];

    // Numeric rank/position values are ordering codes, not designations shown to editors.
    $staffPosition = function ($person): string {
        foreach ([$person->position, $person->rank] as $designation) {
            if ($designation && !is_numeric($designation)) return $designation;
        }

        return match ($person->type) {
            'superadmin' => 'Super Admin',
            'crospondent', 'correspondent' => 'Correspondent',
            'reporter' => 'Reporter',
            'manager' => 'Manager',
            'admin' => 'Admin',
            default => 'Staff',
        };
    };

    $prioritySwitches = [
        ['is_breaking', 'Breaking news'],
        ['is_featured', 'Featured story'],
        ['is_pinned', 'Pin story'],
    ];

    $featuredPlaces = [
        'home_top' => 'Home top',
        'home_middle' => 'Home middle',
        'category_top' => 'Category top',
        'sidebar' => 'Sidebar',
    ];
@endphp

<style>
    .post-page {
        --p: #4f5fe7;
        --line: #dfe4f1;
    }
    .post-card {
        border: 1px solid var(--line);
        border-radius: 13px;
        overflow: hidden;
    }
    .post-stat {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-height: 96px;
    }
    .post-stat i {
        display: grid;
        place-items: center;
        width: 48px;
        height: 48px;
        border-radius: 13px;
        background: #edf0ff;
        color: var(--p);
        font-size: 1.2rem;
    }
    .post-thumb {
        display: grid;
        place-items: center;
        width: 48px;
        height: 42px;
        object-fit: cover;
        border-radius: 8px;
        background: #edf0ff;
        color: var(--p);
    }
    .post-modal .modal-dialog {
        max-width: 1180px;
        height: calc(100vh - 2rem);
        margin: 1rem auto;
    }
    .post-modal .modal-content {
        height: 100%;
        overflow: hidden;
        border-radius: 15px;
    }
    .post-modal form {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
    }
    .post-modal-body {
        flex: 1;
        min-height: 0;
        padding: 1.2rem;
        overflow-y: auto;
    }
    .post-block {
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
    }
    .post-block-head {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--line);
        font-weight: 700;
    }
    .step {
        display: grid;
        place-items: center;
        width: 29px;
        height: 29px;
        color: #fff;
        background: var(--p);
        border-radius: 8px;
        font-size: 0.72rem;
    }
    .post-block-body {
        padding: 1rem;
    }
    .post-page .form-label {
        font-weight: 600;
    }
    .hint {
        display: block;
        margin-top: 0.25rem;
        color: #7d8599;
        font-size: 0.73rem;
    }
    /* Compact newsroom validation feedback replaces the old full-width alert banner. */
    .post-validation {
        margin-bottom: 1rem;
        padding: .85rem 1rem;
        color: #842029;
        background: #fff5f5;
        border: 1px solid #f3b8bd;
        border-left: 4px solid #dc3545;
        border-radius: 9px;
        box-shadow: 0 5px 18px rgba(220, 53, 69, .08);
    }
    .post-validation-inner { display: flex; align-items: flex-start; gap: .75rem; }
    .post-validation-icon {
        display: grid;
        flex: 0 0 28px;
        place-items: center;
        width: 28px;
        height: 28px;
        color: #fff;
        background: #dc3545;
        border-radius: 50%;
        font-weight: 800;
    }
    .post-validation-title { display: block; margin-bottom: .15rem; font-size: .9rem; }
    .post-validation-copy { margin: 0; color: #9b4b52; font-size: .78rem; }
    .post-validation-list { margin: .4rem 0 0; padding-left: 1.15rem; font-size: .8rem; }
    .post-validation-list li + li { margin-top: .2rem; }
    .dark-mode .post-validation {
        color: #ffd9dc;
        background: rgba(220, 53, 69, .1);
        border-color: rgba(255, 118, 128, .28);
        border-left-color: #ff6670;
        box-shadow: none;
    }
    .dark-mode .post-validation-copy { color: #e9a7ac; }
    /* Read-only editorial status timeline shown only while editing an existing story. */
    .post-audit {
        margin: -.15rem 0 1rem;
        padding: .8rem .9rem;
        background: #f7f8fc;
        border: 1px solid var(--line);
        border-radius: 9px;
    }
    .post-audit-title { display: flex; align-items: center; gap: .4rem; margin-bottom: .55rem; font-size: .78rem; font-weight: 700; }
    .post-history-empty { margin: 0; color: #7d8599; font-size: .74rem; }
    .post-history-item { position: relative; padding: .45rem 0 .55rem 1.15rem; font-size: .74rem; }
    .post-history-item::before { content: ""; position: absolute; top: .72rem; left: .1rem; width: 7px; height: 7px; background: var(--primary-bg-color); border-radius: 50%; }
    .post-history-item::after { content: ""; position: absolute; top: 1.15rem; bottom: -.72rem; left: .28rem; border-left: 1px solid var(--line); }
    .post-history-item:last-child::after { display: none; }
    .post-history-transition { font-weight: 700; overflow-wrap: anywhere; }
    .post-history-meta { margin-top: .12rem; color: #7d8599; }
    .post-history-note { margin-top: .3rem; padding: .35rem .5rem; background: rgba(0, 0, 0, .025); border-left: 2px solid var(--line); border-radius: 3px; white-space: pre-wrap; overflow-wrap: anywhere; }
    .dark-mode .post-audit { background: rgba(0, 0, 0, .12); border-color: var(--dark-border); }
    .dark-mode .post-history-meta, .dark-mode .post-history-empty { color: var(--dark-color2); }
    .dark-mode .post-history-item::after, .dark-mode .post-history-note { border-color: var(--dark-border); }
    .dark-mode .post-history-note { background: rgba(255, 255, 255, .035); }
    .story-body {
        min-height: 330px;
        line-height: 1.75;
    }
    .upload-box {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 170px;
        padding: 1rem;
        border: 2px dashed #c9d0e5;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
    }
    .upload-box img {
        width: 100%;
        max-height: 210px;
        object-fit: cover;
        border-radius: 8px;
    }
    .gallery-row {
        display: grid;
        grid-template-columns: 1fr 1fr 36px;
        gap: 0.5rem;
    }
    .existing-image {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem;
        border: 1px solid var(--line);
        border-radius: 8px;
    }
    .existing-image img {
        width: 60px;
        height: 45px;
        object-fit: cover;
        border-radius: 6px;
    }
    .switch-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0;
        border-bottom: 1px dashed var(--line);
    }
    .news-toggle {
        position: relative;
        display: inline-flex;
        flex: 0 0 auto;
        align-items: center;
        gap: .45rem;
        margin: 0;
        cursor: pointer;
        user-select: none;
    }
    .news-toggle-input {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }
    .news-toggle-track {
        position: relative;
        display: block;
        width: 2.75rem;
        height: 1.5rem;
        background: #dce1ec;
        border: 1px solid #aab3c7;
        border-radius: 999px;
        box-shadow: inset 0 1px 2px rgba(31, 42, 78, .14);
        transition: background .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .news-toggle-track::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 2px;
        width: 1.125rem;
        height: 1.125rem;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(31, 42, 78, .28);
        transition: transform .18s ease;
    }
    .news-toggle-state {
        width: 1.65rem;
        color: #737d94;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .news-toggle-state::before { content: "Off"; }
    .news-toggle-input:checked + .news-toggle-track {
        background: #5062e9;
        border-color: #5062e9;
        box-shadow: 0 0 0 1px rgba(80, 98, 233, .1), 0 3px 10px rgba(80, 98, 233, .24);
    }
    .news-toggle-input:checked + .news-toggle-track::after { transform: translateX(1.25rem); }
    .news-toggle-input:checked ~ .news-toggle-state { color: #4354d7; }
    .news-toggle-input:checked ~ .news-toggle-state::before { content: "On"; }
    .news-toggle-input:focus-visible + .news-toggle-track {
        outline: 3px solid rgba(80, 98, 233, .24);
        outline-offset: 2px;
    }
    .placement-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    .placement {
        position: relative;
    }
    .placement input {
        position: absolute;
        opacity: 0;
    }
    .placement label {
        display: block;
        margin: 0;
        padding: 0.55rem;
        border: 1px solid var(--line);
        border-radius: 7px;
        text-align: center;
        cursor: pointer;
    }
    .placement input:checked + label {
        color: var(--p);
        border-color: var(--p);
        background: #eef0ff;
    }
    .seo-preview {
        padding: 0.85rem;
        border: 1px solid var(--line);
        border-radius: 9px;
    }
    .seo-preview [data-seo-title] {
        color: #1a0dab;
        font-size: 1.05rem;
    }
    .seo-preview [data-seo-url] {
        color: #188038;
        font-size: 0.76rem;
    }
    @media (max-width: 767px) {
        .post-modal .modal-dialog {
            height: 100vh;
            margin: 0;
        }
        .post-modal .modal-content {
            border-radius: 0;
        }
        .gallery-row {
            grid-template-columns: 1fr 36px;
        }
        .gallery-caption {
            grid-column: 1/3;
        }
    }
    /* Let the modal own scrolling so the editor never creates a cramped nested scrollbar. */
    .post-rich-editor-wrap { overflow: visible; }
    .post-rich-editor-wrap .ql-toolbar.ql-snow {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
        padding: 9px;
        border-color: var(--line);
        border-radius: 9px 9px 0 0;
        background: #f8f9fc;
    }
    .post-rich-editor-wrap .ql-toolbar .ql-formats { margin-right: 8px; margin-bottom: 2px; }
    .post-rich-editor-wrap .ql-container.ql-snow {
        height: auto !important;
        min-height: 320px;
        overflow: visible !important;
        border-color: var(--line);
        border-radius: 0 0 9px 9px;
        font-size: 16px;
        background: #fff;
    }
    .post-rich-editor-wrap .ql-editor {
        height: auto !important;
        min-height: 320px;
        max-height: none !important;
        padding: 20px;
        overflow: visible !important;
        line-height: 1.75;
        overflow-wrap: anywhere;
    }
    /* Keep article images inside the editor instead of letting them stretch or get cropped. */
    .post-rich-editor-wrap .ql-editor img {
        display: block;
        width: auto;
        max-width: 100%;
        max-height: 420px;
        margin: 18px auto;
        object-fit: contain;
        border-radius: 7px;
    }
    .post-rich-editor-wrap .ql-editor iframe { display: block; width: 100%; max-width: 720px; min-height: 360px; margin: 18px auto; }
    .post-rich-editor-wrap .ql-editor p { margin-bottom: .8rem; }
    .post-rich-editor-wrap .ql-editor.ql-blank::before { color: #9aa2b4; font-style: normal; left: 20px; right: 20px; }
    .post-rich-editor-wrap + .hint { clear: both; margin-top: 9px; padding: 0 2px; }
    /* Keep every native Post field consistent with the surrounding dark modal. */
    .dark-mode .post-modal .form-control,
    .dark-mode .post-modal .form-select {
        color: var(--dark-color) !important;
        background-color: var(--dark-theme) !important;
        border-color: var(--dark-border) !important;
        color-scheme: dark;
    }
    .dark-mode .post-modal .form-control::placeholder { color: var(--dark-color2) !important; opacity: 1; }
    .dark-mode .post-modal .form-select {
        --bs-form-select-bg-img: none;
        background-image: var(--bs-form-select-bg-img),
            linear-gradient(45deg, transparent 50%, var(--dark-color2) 50%),
            linear-gradient(135deg, var(--dark-color2) 50%, transparent 50%) !important;
        background-position: right .75rem center, right .9rem center, right .65rem center !important;
        background-size: 0, 5px 5px, 5px 5px !important;
        background-repeat: no-repeat !important;
    }
    .dark-mode .post-modal .form-select option {
        color: var(--dark-color);
        background: var(--dark-theme);
    }
    .dark-mode .post-modal .form-control:focus,
    .dark-mode .post-modal .form-select:focus {
        border-color: #6978ee !important;
        box-shadow: 0 0 0 .16rem rgba(79, 95, 231, .2) !important;
    }
    .dark-mode .post-modal .form-control:disabled,
    .dark-mode .post-modal .form-control[readonly],
    .dark-mode .post-modal .form-select:disabled {
        color: var(--dark-color2) !important;
        background-color: rgba(0, 0, 0, .18) !important;
        opacity: 1;
    }
    .dark-mode .post-modal input[type="datetime-local"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        opacity: .72;
    }
    .dark-mode .post-modal .upload-box,
    .dark-mode .post-modal .existing-image { border-color: var(--dark-border); }
    /* Match Quill to the admin palette whenever the existing body dark mode is active. */
    .dark-mode .post-rich-editor-wrap .ql-toolbar.ql-snow {
        background: var(--dark-body);
        border-color: var(--dark-border);
    }
    .dark-mode .post-rich-editor-wrap .ql-container.ql-snow,
    .dark-mode .post-rich-editor-wrap .ql-editor {
        color: var(--dark-color);
        background: var(--dark-theme);
        border-color: var(--dark-border);
    }
    .dark-mode .post-rich-editor-wrap .ql-editor.ql-blank::before { color: var(--dark-color2); }
    .dark-mode .post-rich-editor-wrap .ql-stroke { stroke: var(--dark-color); }
    .dark-mode .post-rich-editor-wrap .ql-fill { fill: var(--dark-color); }
    .dark-mode .post-rich-editor-wrap .ql-picker { color: var(--dark-color); }
    .dark-mode .post-rich-editor-wrap .ql-picker-label,
    .dark-mode .post-rich-editor-wrap button {
        color: var(--dark-color);
        border-radius: 5px;
    }
    .dark-mode .post-rich-editor-wrap .ql-picker-options {
        color: var(--dark-color);
        background: var(--dark-body);
        border-color: var(--dark-border);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .28);
    }
    .dark-mode .post-rich-editor-wrap .ql-toolbar button:hover,
    .dark-mode .post-rich-editor-wrap .ql-toolbar button:focus,
    .dark-mode .post-rich-editor-wrap .ql-toolbar button.ql-active,
    .dark-mode .post-rich-editor-wrap .ql-picker-label:hover,
    .dark-mode .post-rich-editor-wrap .ql-picker-label.ql-active {
        color: #8f9aff;
        background: rgba(79, 95, 231, .18);
    }
    .dark-mode .post-rich-editor-wrap .ql-toolbar button:hover .ql-stroke,
    .dark-mode .post-rich-editor-wrap .ql-toolbar button:focus .ql-stroke,
    .dark-mode .post-rich-editor-wrap .ql-toolbar button.ql-active .ql-stroke,
    .dark-mode .post-rich-editor-wrap .ql-picker-label:hover .ql-stroke,
    .dark-mode .post-rich-editor-wrap .ql-picker-label.ql-active .ql-stroke { stroke: #8f9aff; }
    .dark-mode .post-rich-editor-wrap .ql-toolbar button:hover .ql-fill,
    .dark-mode .post-rich-editor-wrap .ql-toolbar button:focus .ql-fill,
    .dark-mode .post-rich-editor-wrap .ql-toolbar button.ql-active .ql-fill { fill: #8f9aff; }
    /* Professional newsroom editor polish: visual-only, no workflow changes. */
    .post-modal {
        --editor-bg: #f5f7fb;
        --editor-surface: #ffffff;
        --editor-head: #fafbfe;
        --editor-text: #25304a;
        --editor-muted: #77819a;
        --editor-shadow: 0 8px 26px rgba(31, 42, 78, .07);
    }
    .post-modal .modal-dialog { max-width: 1320px; }
    .post-modal .modal-content {
        color: var(--editor-text);
        background: var(--editor-surface);
        border: 1px solid rgba(79, 95, 231, .18);
        box-shadow: 0 24px 70px rgba(18, 27, 58, .22);
    }
    .post-modal .modal-header {
        min-height: 78px;
        padding: 1.05rem 1.35rem;
        background: linear-gradient(135deg, rgba(79, 95, 231, .08), transparent 58%);
        border-bottom-color: var(--line);
    }
    .post-modal .modal-title { color: inherit; font-size: 1.08rem; font-weight: 750; letter-spacing: -.01em; }
    .post-modal-body { padding: 1.35rem; background: var(--editor-bg); }
    .post-modal-body > .row { --bs-gutter-x: 1.25rem; --bs-gutter-y: 1.25rem; }
    .post-modal .post-block {
        background: var(--editor-surface);
        border-color: var(--line);
        box-shadow: var(--editor-shadow);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .post-modal .post-block:hover { border-color: rgba(79, 95, 231, .35); box-shadow: 0 10px 30px rgba(31, 42, 78, .1); }
    .post-modal .post-block-head {
        min-height: 54px;
        padding: .75rem .95rem;
        background: var(--editor-head);
        border-bottom-color: var(--line);
        font-size: .86rem;
    }
    .post-modal .step { width: 30px; height: 30px; border-radius: 9px; box-shadow: 0 5px 12px rgba(79, 95, 231, .24); }
    .post-modal .post-block-body { padding: 1.05rem; }
    .post-modal .form-label { margin-bottom: .42rem; color: inherit; font-size: .78rem; letter-spacing: .005em; }
    .post-modal .form-control,
    .post-modal .form-select {
        min-height: 42px;
        border-radius: 8px;
        border-color: #dbe0ed;
        transition: border-color .16s ease, box-shadow .16s ease, background-color .16s ease;
    }
    .post-modal textarea.form-control { min-height: auto; padding: .7rem .8rem; line-height: 1.55; }
    .post-modal .form-control:focus,
    .post-modal .form-select:focus { border-color: #7380ec; box-shadow: 0 0 0 .2rem rgba(79, 95, 231, .12); }
    .post-modal .hint { margin-top: .38rem; color: var(--editor-muted); line-height: 1.4; }
    .post-modal .upload-box { background: rgba(79, 95, 231, .025); transition: border-color .18s ease, background .18s ease; }
    .post-modal .upload-box:hover { background: rgba(79, 95, 231, .06); border-color: var(--p); }
    .post-modal .seo-preview { background: linear-gradient(145deg, rgba(79, 95, 231, .05), transparent); border-radius: 9px; }
    .post-modal .placement { transition: color .16s ease, border-color .16s ease, background .16s ease; }
    .post-modal .placement:has(input:checked) { color: var(--p); background: rgba(79, 95, 231, .08); border-color: var(--p); }
    .post-modal .post-audit { margin-bottom: 0; background: var(--editor-surface); box-shadow: var(--editor-shadow); }
    .post-modal .modal-footer {
        min-height: 68px;
        padding: .8rem 1.35rem;
        background: var(--editor-surface);
        border-top-color: var(--line);
        box-shadow: 0 -8px 24px rgba(31, 42, 78, .05);
    }
    .post-modal .modal-footer .btn { min-width: 104px; min-height: 40px; border-radius: 8px; font-weight: 650; }
    @media (min-width: 1200px) {
        .post-modal .post-side-column { position: sticky; top: 0; align-self: flex-start; }
    }
    .dark-mode .post-modal {
        --editor-bg: #0c1230;
        --editor-surface: #11183d;
        --editor-head: #151d46;
        --editor-text: #edf0f5;
        --editor-muted: rgba(237, 240, 245, .52);
        --editor-shadow: 0 10px 26px rgba(0, 0, 0, .16);
        --line: rgba(255, 255, 255, .11);
    }
    .dark-mode .post-modal .modal-header { background: linear-gradient(135deg, rgba(79, 95, 231, .17), transparent 60%); }
    .dark-mode .post-modal .post-block:hover { border-color: rgba(123, 137, 244, .38); box-shadow: 0 12px 30px rgba(0, 0, 0, .2); }
    .dark-mode .post-modal .form-control,
    .dark-mode .post-modal .form-select { border-color: rgba(255, 255, 255, .12) !important; background-color: #0e1536 !important; }
    .dark-mode .post-modal .switch-row { color: #edf0f8; }
    .dark-mode .post-modal .news-toggle-track {
        background: #263052;
        border-color: #536087;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, .38);
    }
    .dark-mode .post-modal .news-toggle-track::after {
        background: #d9dff1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .45);
    }
    .dark-mode .post-modal .news-toggle-state { color: #929cba; }
    .dark-mode .post-modal .news-toggle-input:checked + .news-toggle-track {
        background: #6676f5;
        border-color: #8a96ff;
        box-shadow: 0 0 0 1px rgba(138, 150, 255, .16), 0 0 14px rgba(102, 118, 245, .3);
    }
    .dark-mode .post-modal .news-toggle-input:checked + .news-toggle-track::after { background: #fff; }
    .dark-mode .post-modal .news-toggle-input:checked ~ .news-toggle-state { color: #b8c0ff; }
    .dark-mode .post-modal .placement label {
        color: #dce2f4;
        background: #0e1536;
        border-color: rgba(255, 255, 255, .14);
    }
    .dark-mode .post-modal .placement input:checked + label {
        color: #c8ceff;
        background: rgba(101, 116, 244, .22);
        border-color: #7482f5;
        box-shadow: inset 0 0 0 1px rgba(130, 144, 255, .12);
    }
    .post-modal .placement input:focus-visible + label {
        outline: 2px solid #8290ff;
        outline-offset: 2px;
    }
    .dark-mode .post-modal .upload-box { background: rgba(255, 255, 255, .018); }
    .dark-mode .post-modal .upload-box:hover { background: rgba(79, 95, 231, .11); }
    @media (max-width: 1199px) {
        .post-modal .modal-dialog { max-width: calc(100% - 1.5rem); }
        .post-modal .post-side-column { position: static; }
    }
</style>

<div class="app-content main-content post-page">
    <div class="side-app">
        <div class="container-fluid main-container">

            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title mb-1">{{ $pageTitle }}</h4>
                    <p class="text-muted mb-0">{{ $pageSubtitle }}</p>
                </div>
                @if($canAdd)
                    <button type="button" class="btn btn-primary js-post-create">
                        <i class="fe fe-plus me-1"></i>Create Post</button
                    >
                @endif
            </div>

            <div class="row g-3 mb-4">
                @foreach($statCards as $card)
                    <div class="col-xl-3 col-sm-6">
                        <div class="card post-card mb-0">
                            <div class="card-body post-stat">
                                <i class="fe {{ $card[2] }}"></i
                                ><span class="text-muted"
                                    >{{ $card[0] }}<strong class="d-block fs-4 text-body">{{ $card[1] }}</strong></span
                                >
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form id="post-filter-form" action="{{ route($indexRoute) }}" class="card post-card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5">
                            <label class="form-label">Search post</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fe fe-search"></i></span
                                ><input
                                    name="search"
                                    value="{{ $search }}"
                                    class="form-control"
                                    placeholder="Search by headline"
                                />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label
                            ><select name="status" class="form-select">
                                <option value="">All statuses</option>
                                @foreach($statusOptions as $item)
                                    <option value="{{ $item }}" @if($status == $item) selected @endif>{{ ucfirst($item) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label
                            ><select name="category_id" class="form-select">
                                <option value="">All categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @if($categoryId == $category->id) selected @endif>{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1">
                            <button class="btn btn-outline-primary w-100"><i class="fe fe-filter"></i></button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card post-card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Post List</h5>
                    <span class="text-muted">{{ $posts->total() }} posts</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Post</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    @php
                                        $ownsPost = (int) $post->created_by === $admin->id || (int) $post->author_id === $admin->id;
                                        $reviewsPost = (int) $post->reviewed_by === $admin->id;
                                        $canEditPost = $canEdit && ($canManageAll || $ownsPost || $reviewsPost);
                                        $canDeletePost = $canDelete && ($canManageAll || $ownsPost);
                                        $canApprovePost = $canManageAll || $reviewsPost;
                                        $canChangeStatus = $canEditPost && ($canApprovePost || ($ownsPost && in_array($post->status, ['draft', 'review'], true)));
                                        $rowStatuses = $canApprovePost ? $statusOptions : ['draft', 'review'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($post->featured_image)
                                                    <img
                                                        class="post-thumb"
                                                        src="{{ asset('admin/postimages/'.$post->featured_image) }}"
                                                        alt=""
                                                    />
                                                @else
                                                    <span class="post-thumb"><i class="fe fe-image"></i></span>
                                                @endif
                                                <div>
                                                    <strong class="d-block">{{ Str::limit($post->title, 70) }}</strong
                                                    ><small class="text-muted">/{{ $post->slug }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $post->category?->category_name ?? '—' }}</td>
                                        <td>{{ $post->author?->name ?? '—' }}</td>
                                        <td>{{ ($post->published_at ?? $post->created_at)?->format('d M Y') }}</td>
                                        <td>
                                            @if($canChangeStatus)
                                                <select
                                                    class="form-select form-select-sm js-post-status"
                                                    data-url="{{ route('admin-post.status', $post) }}"
                                                >
                                                    @foreach($rowStatuses as $item)
                                                        <option value="{{ $item }}" @if($post->status == $item) selected @endif>{{ ucfirst($item) }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ ucfirst($post->status) }}
                                            @endif
                                        </td>                                        <td class="text-end">
                                            @if($canEditPost)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary js-post-edit"
                                                    data-url="{{ route('admin-post.show', $post) }}"
                                                    data-update-url="{{ route('admin-post.update', $post) }}"
                                                >
                                                    <i class="fe fe-edit-2"></i></button
                                                >
                                            @endif
                                            @if($canDeletePost)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger js-post-delete"
                                                    data-url="{{ route('admin-post.delete', $post) }}"
                                                >
                                                    <i class="fe fe-trash-2"></i></button
                                                >
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No posts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">{{ $posts->links() }}</div>
            </div>

            <div class="modal fade post-modal" id="post-form-modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form
                            id="post-form"
                            data-store-url="{{ route('admin-post.store') }}"
                            enctype="multipart/form-data"
                        >
                            @csrf
                            <div class="modal-header">
                                <div>
                                    <h5 id="post-form-title" class="modal-title">Create New Post</h5>
                                    <small class="text-muted">Write, classify and configure publishing.</small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="post-modal-body">
                                <div id="post-form-errors" class="post-validation d-none" role="alert" aria-live="polite"></div>

                                <div class="row g-3">
                                    <div class="col-xl-8 post-main-column">

                                        <div class="post-block mb-3">
                                            <div class="post-block-head">
                                                <span class="step">01</span>Story information
                                            </div>
                                            <div class="post-block-body">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Post type *</label
                                                        ><select name="post_type" class="form-select">
                                                            <option value="news">News</option>
                                                            <option value="article">Article</option>
                                                            <option value="opinion">Opinion</option>
                                                            <option value="video">Video</option>
                                                            <option value="photo_story">Photo story</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label">Special title</label
                                                        ><input name="special_title" class="form-control" />
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Headline *</label
                                                        ><input
                                                            id="post-title"
                                                            name="title"
                                                            maxlength="255"
                                                            class="form-control"
                                                            required
                                                        />
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Slug</label>
                                                        <div class="input-group">
                                                            <input
                                                                id="post-slug"
                                                                name="slug"
                                                                class="form-control"
                                                            /><button
                                                                type="button"
                                                                id="make-post-slug"
                                                                class="btn btn-outline-primary"
                                                            >
                                                                Generate
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Summary</label
                                                        ><textarea
                                                            id="post-summary"
                                                            name="summary"
                                                            rows="3"
                                                            class="form-control"
                                                        ></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="post-block mb-3">
                                            <div class="post-block-head"><span class="step">02</span>Description</div>
                                            <div class="post-block-body">
                                                <textarea id="post-description" name="description" class="d-none"></textarea>
<div class="post-rich-editor-wrap"><div id="post-rich-editor" class="post-rich-editor"></div></div><span class="hint"
                                                    >Words: <b data-word-count>0</b> · Reading time:
                                                    <b data-read-time>0 min</b></span
                                                >
                                            </div>
                                        </div>

                                        <div class="post-block mb-3">
                                            <div class="post-block-head"><span class="step">03</span>Media</div>
                                            <div class="post-block-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Featured image</label
                                                        ><label class="upload-box" for="post-featured-image"
                                                            ><span data-upload-text
                                                                ><i class="fe fe-image fs-2 d-block mb-2"></i>Choose
                                                                cover image</span
                                                            ><img data-image-preview class="d-none" alt="" /></label
                                                        ><input
                                                            id="post-featured-image"
                                                            name="featured_image"
                                                            type="file"
                                                            class="d-none"
                                                            accept="image/*"
                                                        />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Caption</label
                                                        ><textarea
                                                            name="image_caption"
                                                            rows="3"
                                                            class="form-control mb-3"
                                                        ></textarea
                                                        ><label class="form-label">Alt text</label
                                                        ><input name="image_alt" class="form-control" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Video type</label
                                                        ><select name="video_type" class="form-select">
                                                            <option value="">No video</option>
                                                            <option value="youtube">YouTube</option>
                                                            <option value="facebook">Facebook</option>
                                                            <option value="vimeo">Vimeo</option>
                                                            <option value="upload">Upload</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label">Video URL</label
                                                        ><input name="video_url" type="url" class="form-control" />
                                                    </div>
                                                    <div class="col-12">
                                                        <hr />
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <strong>Story gallery</strong
                                                            ><button
                                                                id="add-gallery"
                                                                type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                            >
                                                                + Add image
                                                            </button>
                                                        </div>
                                                        <div id="existing-gallery" class="d-grid gap-2 mb-2"></div>
                                                        <div id="gallery-list" class="d-grid gap-2"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="post-block">
                                            <div class="post-block-head"><span class="step">04</span>SEO</div>
                                            <div class="post-block-body">
                                                <div class="seo-preview mb-3">
                                                    <div data-seo-title>Your headline</div>
                                                    <div data-seo-url>/post-slug</div>
                                                    <small data-seo-description class="text-muted"
                                                        >Search preview description</small
                                                    >
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label">Meta title</label
                                                        ><input
                                                            id="meta-title"
                                                            name="meta_title"
                                                            class="form-control"
                                                        />
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Meta description</label
                                                        ><textarea
                                                            id="meta-description"
                                                            name="meta_description"
                                                            rows="3"
                                                            class="form-control"
                                                        ></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Keywords</label
                                                        ><input name="meta_keywords" class="form-control" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Robots</label
                                                        ><select name="meta_robots" class="form-select">
                                                            <option value="index,follow">index, follow</option>
                                                            <option value="noindex,follow">noindex, follow</option>
                                                            <option value="index,nofollow">index, nofollow</option>
                                                            <option value="noindex,nofollow">noindex, nofollow</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Canonical URL</label
                                                        ><input name="canonical_url" type="url" class="form-control" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Social image</label
                                                        ><input
                                                            name="meta_image"
                                                            type="file"
                                                            class="form-control"
                                                            accept="image/*"
                                                        />
                                                    </div>                                                    <div class="col-12">
                                                        <label class="form-label">Schema Markup</label>
                                                        <textarea
                                                            name="schema_markup"
                                                            rows="4"
                                                            class="form-control font-monospace"
                                                            placeholder='{"@@context":"https://schema.org","@@type":"NewsArticle"}'
                                                        ></textarea>
                                                        <span class="hint">Optional. Enter valid JSON only; no automatic formatting will be applied.</span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-xl-4 post-side-column">

                                        <div class="post-block mb-3">
                                            <div class="post-block-head"><span class="step">05</span>Publishing</div>
                                            <div class="post-block-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label
                                                    ><select name="status" class="form-select">
                                                        @foreach($statusOptions as $item)
                                                            <option value="{{ $item }}">{{ ucfirst($item) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Schedule</label
                                                    ><input
                                                        name="scheduled_at"
                                                        type="datetime-local"
                                                        class="form-control"
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Author / Reporter</label
                                                    ><select name="author_id" class="form-select">
                                                        {{-- A new story belongs to the logged-in reporter unless another byline is selected. --}}
                                                        <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $staffPosition($admin) }}, Logged in)</option>
                                                        @foreach($admins->where('id', '!=', $admin->id) as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $staffPosition($item) }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Reviewer / Send for review to</label>
                                                    {{-- reviewed_at remains empty until the selected editor approves the story. --}}
                                                    <select name="reviewed_by" class="form-select">
                                                        <option value="">Select editor</option>
                                                        @foreach($reviewers as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $staffPosition($item) }})</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="hint">Select the editor who will review this story. Required for Review status.</span>
                                                </div>

                                                <div>
                                                    <label class="form-label">Change note</label
                                                    ><textarea
                                                        name="change_note"
                                                        rows="2"
                                                        class="form-control"
                                                        placeholder="Optional note for revision history"
                                                    ></textarea>
                                                </div>
                                                <div class="switch-row">
                                                    <span>Allow comments</span>
                                                    <label class="news-toggle" for="allow-comments">
                                                        <input
                                                            id="allow-comments"
                                                            class="news-toggle-input"
                                                            type="checkbox"
                                                            name="allow_comments"
                                                            value="1"
                                                            aria-label="Allow comments"
                                                            checked
                                                        />
                                                        <span class="news-toggle-track" aria-hidden="true"></span>
                                                        <span class="news-toggle-state" aria-hidden="true"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="post-block mb-3">
                                            <div class="post-block-head">
                                                <span class="step">06</span>Classification
                                            </div>
                                            <div class="post-block-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Section</label
                                                    ><select id="post-section" name="section_id" class="form-select">
                                                        <option value="">Select section</option>
                                                        @foreach($sections as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label
                                                    ><select id="post-category" name="category_id" class="form-select">
                                                        <option value="">Select category</option>
                                                        @foreach($categories as $item)
                                                            <option
                                                                value="{{ $item->id }}"
                                                                data-section="{{ $item->section_id }}"
                                                            >
                                                                {{ $item->category_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">Tags</label
                                                    ><select
                                                        id="post-tags"
                                                        name="tag_ids[]"
                                                        class="form-select"
                                                        multiple
                                                        size="5"
                                                    >
                                                        @foreach($tags as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="post-block mb-3">
                                            <div class="post-block-head"><span class="step">07</span>Location</div>
                                            <div class="post-block-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Division</label
                                                    ><select id="post-division" name="division_id" class="form-select">
                                                        <option value="">All Bangladesh</option>
                                                        @foreach($divisions as $item)
                                                            <option value="{{ $item->id }}">
                                                                {{ $item->bn_name ?: $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">District</label
                                                    ><select id="post-district" name="district_id" class="form-select">
                                                        <option value="">Select district</option>
                                                        @foreach($districts as $item)
                                                            <option
                                                                value="{{ $item->id }}"
                                                                data-division="{{ $item->division_id }}"
                                                            >
                                                                {{ $item->bn_name ?: $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">Upazila</label
                                                    ><select id="post-upazila" name="upazila_id" class="form-select">
                                                        <option value="">Select upazila</option>
                                                        @foreach($upazilas as $item)
                                                            <option
                                                                value="{{ $item->id }}"
                                                                data-district="{{ $item->district_id }}"
                                                            >
                                                                {{ $item->bn_name ?: $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="post-block">
                                            <div class="post-block-head">
                                                <span class="step">08</span>Display priority
                                            </div>
                                            <div class="post-block-body">
                                                @foreach($prioritySwitches as $switch)
                                                    <div class="switch-row">
                                                        <span>{{ $switch[1] }}</span>
                                                        <label class="news-toggle" for="{{ str_replace('_', '-', $switch[0]) }}">
                                                            <input
                                                                id="{{ str_replace('_', '-', $switch[0]) }}"
                                                                class="news-toggle-input"
                                                                type="checkbox"
                                                                name="{{ $switch[0] }}"
                                                                value="1"
                                                                aria-label="{{ $switch[1] }}"
                                                            />
                                                            <span class="news-toggle-track" aria-hidden="true"></span>
                                                            <span class="news-toggle-state" aria-hidden="true"></span>
                                                        </label>
                                                    </div>
                                                @endforeach

                                                <label class="form-label mt-3">Featured positions</label>
                                                <div class="placement-grid">
                                                    @foreach($featuredPlaces as $value => $label)
                                                        <span class="placement"
                                                            ><input id="place-{{ $value }}" type="checkbox" name="featured_positions[]" value="{{ $value }}"
                                                            /><label for="place-{{ $value }}">{{ $label }}</label></span
                                                        >
                                                    @endforeach
                                                </div>

                                                <label class="form-label mt-3">News source</label
                                                ><input name="source" class="form-control" />
                                            </div>
                                        </div>

                                                <div id="post-audit" class="post-audit d-none mt-3">
                                                    <div class="post-audit-title"><i class="fe fe-clock"></i> Editorial history</div>
                                                    <div data-editorial-history></div>
                                                </div>


                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Cancel</button
                                ><button id="post-submit" class="btn btn-primary" type="submit">
                                    <i class="fe fe-save me-1"></i>Save Post
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
