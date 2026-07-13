import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
const source = readFileSync(new URL('../js/main.js', import.meta.url), 'utf8');
for (const contract of ['adu-year-table', 'approved', 'data-action', 'set-status', "client.request", '/api/teams/', 'requestFor', "['organization', 'Büros und Fachgruppen']", "document.createElement('optgroup')"]) if (!source.includes(contract) && !readFileSync(new URL('../templates/index.php', import.meta.url), 'utf8').includes(contract)) throw new Error(`Frontendvertrag fehlt: ${contract}`);
for (const removed of ['/api/settings', 'adu-tab-settings', "showView('settings')"]) if (source.includes(removed)) throw new Error(`Organisationsweite Einstellung liegt noch im Urlaubs-Frontend: ${removed}`);
if (!source.includes('const formElement=event.currentTarget') || !source.includes('formElement.reset()') || source.includes('event.currentTarget.reset()')) throw new Error('Urlaubsformular verliert seinen Formularbezug nach dem asynchronen Speichern.');
execFileSync('node', ['--check', new URL('../js/main.js', import.meta.url).pathname], {stdio:'inherit'});
console.log('AD Urlaub JavaScript tests passed');
