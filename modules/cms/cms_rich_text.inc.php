<?php
declare(strict_types=1);

/**
 * Éditeur riche (TinyMCE, licence LGPL) pour les textareas marquées .cms-rte.
 */
function cms_render_rich_text_editor_assets(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    function initCmsRichEditors() {
        if (typeof tinymce === 'undefined') return;
        var list = document.querySelectorAll('textarea.cms-rte');
        if (!list.length) return;
        tinymce.init({
            selector: 'textarea.cms-rte',
            height: 320,
            min_height: 200,
            menubar: false,
            statusbar: true,
            resize: true,
            plugins: 'lists link code',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link unlink | removeformat | code',
            block_formats: 'Paragraph=p;Heading 2=h2;Heading 3=h3',
            content_style: 'body{font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.55;margin:12px}',
            branding: false,
            promotion: false,
            entity_encoding: 'raw',
            convert_urls: false,
            relative_urls: false,
            language: 'fr_FR',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@23.10.9/langs6/fr_FR.js',
            setup: function (editor) {
                editor.on('change keyup', function () { editor.save(); });
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCmsRichEditors);
    } else {
        initCmsRichEditors();
    }
    document.querySelectorAll('form.cms-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (window.tinymce) tinymce.triggerSave();
        });
    });
})();
</script>
    <?php
}
