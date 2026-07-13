(function () {
    'use strict';

    const client = new window.LocalBase.api.ApiClient({
        appId: 'adurlaub',
        errorMessage: (data, status) => data?.error || `HTTP ${status}`,
    });
    const notice = new window.LocalBase.ui.Notice('adu-notice', {
        baseClass: 'adu-notice',
        typeClassPrefix: 'adu-notice--',
    });

    new window.AdUrlaub.modules.VacationApp({ client, notice }).start();
}());
