"use strict";

function newexportaction(e, dt, button, config) {
    const oldStart = dt.settings()[0]._iDisplayStart;
    dt.one('preXhr', (e, s, data) => {
        data.start = 0;
        data.length = -1;
        dt.one('preDraw', (e, settings) => {
            const extension = button[0].className.includes('buttons-excel') ? 'excel' : 'csv';
            const extensionName = extension + 'Html5';
            const extensionFlash = extension + 'Flash';
            const actionName = $.fn.dataTable.ext.buttons[extensionName].available(dt, config) ?
                extensionName : extensionFlash;
            $.fn.dataTable.ext.buttons[actionName].action.call(this, e, dt, button, config);
            dt.one('preXhr', (e, s, data) => {
                settings._iDisplayStart = oldStart;
                data.start = oldStart;
            });
            dt.ajax.reload(null, false);
            return false;
        });
    });
    dt.ajax.reload();
}
