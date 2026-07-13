import { readFileSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
const source = readFileSync(new URL('../js/main.js', import.meta.url), 'utf8');
for (const contract of ['vacation.marker', 'approved', 'data-action', 'showModal', "client.request"]) if (!source.includes(contract)) throw new Error(`Frontendvertrag fehlt: ${contract}`);
execFileSync('node', ['--check', new URL('../js/main.js', import.meta.url).pathname], {stdio:'inherit'});
console.log('AD Urlaub JavaScript tests passed');
