<?php
script('localbase', 'api/api-client');
script('localbase', 'ui/ui');
script('adurlaub', 'main');
style('adurlaub', 'style');
?>
<main id="adurlaub-app" class="adu-app">
    <header class="adu-header">
        <h1>AD Urlaub</h1>
        <div class="adu-controls">
            <label>Team <select id="adu-team"></select></label>
            <label>Jahr <input id="adu-year" type="number" min="2000" max="2100" step="1"></label>
        </div>
    </header>
    <div id="adu-notice" role="status" aria-live="polite"></div>
    <section class="adu-section" aria-labelledby="adu-plan-title">
        <header class="adu-section-head">
            <h2 id="adu-plan-title">Urlaubsplan</h2>
            <p class="adu-legend"><span class="adu-request adu-request-planned">U?</span> geplant, Hinweis <span class="adu-request adu-request-approved">U</span> genehmigt, blockiert Kalenderzeiten</p>
        </header>
        <form id="adu-own-form" class="adu-vacation-form">
            <label>Von <input name="startDate" type="date" required></label>
            <label>Bis <input name="endDate" type="date" required></label>
            <label>Notiz <input name="note" type="text" maxlength="500"></label>
            <button type="submit">Eintragen</button>
        </form>
        <div id="adu-own-requests" class="adu-request-list" aria-label="Meine Urlaubsanträge"></div>
        <div class="adu-table-wrap adu-year-wrap">
            <table class="adu-table adu-year-table">
                <caption>Jahresurlaub nach Team und Mitarbeiter*in</caption>
                <thead id="adu-calendar-head"></thead>
                <tbody id="adu-calendar-body"><tr><td>Daten werden geladen.</td></tr></tbody>
            </table>
        </div>
    </section>
    <details id="adu-settings" class="adu-settings" hidden><summary>Genehmigungen durch direkte Kolleg*innen</summary><form id="adu-settings-form"><div id="adu-peer-settings"></div><button type="submit">Freigaben speichern</button></form></details>
    <dialog id="adu-dialog" class="adu-dialog" aria-labelledby="adu-dialog-title">
        <form id="adu-form">
            <header><h2 id="adu-dialog-title">Urlaub eintragen</h2><button type="button" id="adu-dialog-close" class="adu-icon-button" aria-label="Dialog schließen" title="Schließen">×</button></header>
            <input id="adu-id" type="hidden">
            <label>Mitarbeiter*in <select id="adu-employee" required></select></label>
            <label>Von <input id="adu-start-date" type="date" required></label>
            <label>Bis einschließlich <input id="adu-end-date" type="date" required></label>
            <label>Status <select id="adu-status"><option value="planned">Geplant (U?)</option><option value="approved">Genehmigt (U)</option></select></label>
            <label>Notiz <textarea id="adu-note" maxlength="500" rows="3"></textarea></label>
            <div id="adu-conflicts" class="adu-conflicts" role="alert" hidden></div>
            <footer><button type="button" id="adu-delete" class="error" hidden>Urlaub löschen</button><span class="adu-dialog-spacer"></span><button type="button" id="adu-dialog-cancel">Abbrechen</button><button type="submit" class="primary">Speichern</button></footer>
        </form>
    </dialog>
</main>
