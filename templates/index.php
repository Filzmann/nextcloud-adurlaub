<?php
script('localbase', 'api/api-client');
script('localbase', 'ui/ui');
script('adurlaub', 'main');
style('adurlaub', 'style');
?>
<main id="adurlaub-app" class="adu-app">
    <header class="adu-header">
        <div><h1>AD Urlaub</h1><p>Urlaubsplanung für alle Fachgruppen</p></div>
        <nav aria-label="Kalenderwoche" class="adu-navigation">
            <button type="button" id="adu-previous-week">Vorherige Woche</button>
            <output id="adu-week-label" aria-live="polite"></output>
            <label>KW <input id="adu-week-number" type="week"></label>
            <button type="button" id="adu-next-week">Nächste Woche</button>
        </nav>
    </header>
    <div id="adu-notice" role="status" aria-live="polite"></div>
    <p class="adu-legend"><span class="adu-marker adu-marker--planned">U?</span> geplant, Hinweis ohne Blockade <span class="adu-marker adu-marker--approved">U</span> genehmigt, blockiert Kalenderzeiten</p>
    <details id="adu-settings" class="adu-settings" hidden><summary>Genehmigungen durch direkte Kolleg*innen</summary><form id="adu-settings-form"><div id="adu-peer-settings"></div><button type="submit">Freigaben speichern</button></form></details>
    <div class="adu-table-wrap">
        <table class="adu-calendar">
            <caption>Urlaub je Mitarbeiter*in und Tag</caption>
            <thead id="adu-calendar-head"></thead>
            <tbody id="adu-calendar-body"><tr><td>Daten werden geladen.</td></tr></tbody>
        </table>
    </div>
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
