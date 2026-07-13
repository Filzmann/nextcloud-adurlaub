<?php
script('localbase', 'api/api-client');
script('localbase', 'ui/ui');
script('orgsuite', 'suite-navigation');
script('adurlaub', 'components/vacation-plan');
script('adurlaub', 'modules/vacation-app');
script('adurlaub', 'main');
style('adurlaub', 'style');
style('orgsuite', 'suite-navigation');
?>
<main id="adurlaub-app" class="adu-app">
    <div class="orgsuite-host" data-orgsuite data-suite="ad" data-current-app="adurlaub"></div>
    <header class="adu-header">
        <h1>AD Urlaub</h1>
        <div class="adu-controls">
            <label>Team <select id="adu-team"></select></label>
            <label>Jahr <input id="adu-year" type="number" min="2000" max="2100" step="1"></label>
        </div>
    </header>
    <div id="adu-notice" role="status" aria-live="polite"></div>
    <section id="adu-calendar-view" class="adu-section" aria-labelledby="adu-plan-title">
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
        <div id="adu-conflicts" class="adu-conflicts" role="alert" hidden></div>
        <div class="adu-table-wrap adu-year-wrap">
            <table class="adu-table adu-year-table">
                <caption>Jahresurlaub nach Team und Mitarbeiter*in</caption>
                <thead id="adu-calendar-head"></thead>
                <tbody id="adu-calendar-body"><tr><td>Daten werden geladen.</td></tr></tbody>
            </table>
        </div>
    </section>
</main>
