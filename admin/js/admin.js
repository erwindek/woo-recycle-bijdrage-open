/* WRBO Admin JS */
jQuery(function ($) {

    // Live-update the OPEN label column when the select changes
    $(document).on('change', '.wrbo-open-code-select', function () {
        var $select = $(this);
        var $label  = $select.closest('tr').find('.wrbo-open-label');
        var text    = $select.find('option:selected').text();
        // Strip the "CODE – " prefix
        var parts = text.split(' – ');
        $label.text(parts.length > 1 ? parts.slice(1).join(' – ') : (text === '— Niet gekoppeld —' ? '' : text));
    });

    // CSV copy for tables
    $(document).on('click', '.wrbo-copy-csv', function () {
        var targetId = $(this).data('target');
        var $table   = $('#' + targetId);
        if (!$table.length) {
            $table = $('.' + targetId).first();
        }
        var rows = [];
        $table.find('tr').each(function () {
            var cells = [];
            $(this).find('th, td').each(function () {
                var text = $(this).text().trim().replace(/\s+/g, ' ');
                // Escape quotes and wrap if needed
                if (text.indexOf(';') !== -1 || text.indexOf('"') !== -1) {
                    text = '"' + text.replace(/"/g, '""') + '"';
                }
                cells.push(text);
            });
            rows.push(cells.join(';'));
        });
        var csv = rows.join('\n');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(csv).then(function () {
                wrboShowCopyNotice();
            });
        } else {
            // Fallback
            var $tmp = $('<textarea/>').val(csv).appendTo('body').select();
            document.execCommand('copy');
            $tmp.remove();
            wrboShowCopyNotice();
        }
    });

    function wrboShowCopyNotice() {
        var $notice = $('<div class="notice notice-success is-dismissible inline" style="margin:8px 0;"><p>Gekopieerd naar klembord.</p></div>');
        $('.wrbo-export-bar').before($notice);
        setTimeout(function () { $notice.fadeOut(400, function () { $(this).remove(); }); }, 2500);
    }

    // Live search / filter for detail table
    $(document).on('input', '#wrbo-detail-search', function () {
        var q = $(this).val().toLowerCase();
        $('#wrbo-detail-table tbody tr').each(function () {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    });
});
