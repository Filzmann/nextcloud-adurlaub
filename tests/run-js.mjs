import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
const source = readFileSync(new URL('../js/main.js', import.meta.url), 'utf8');
for (const contract of ['adu-year-table', 'approved', 'data-action', 'set-status', "client.request", '/api/teams/', 'requestFor']) if (!source.includes(contract) && !readFileSync(new URL('../templates/index.php', import.meta.url), 'utf8').includes(contract)) throw new Error(`Frontendvertrag fehlt: ${contract}`);
execFileSync('node', ['--check', new URL('../js/main.js', import.meta.url).pathname], {stdio:'inherit'});
console.log('AD Urlaub JavaScript tests passed');
