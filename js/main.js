(function () {
    'use strict';
    const client = new window.LocalBase.api.ApiClient({ appId: 'adurlaub', errorMessage: (data, status) => data?.error || `HTTP ${status}` });
    const notice = new window.LocalBase.ui.Notice('adu-notice', { baseClass: 'adu-notice', typeClassPrefix: 'adu-notice--' });
    const ids = ['team','year','calendar-head','calendar-body','own-form','own-requests','conflicts','tab-calendar','tab-settings','calendar-view','settings-view','settings-form','peer-settings'];
    const elements = Object.fromEntries(ids.map(id => [id, document.getElementById(`adu-${id}`)]));
    const state = { teams: [], teamId: '', year: new Date().getFullYear(), currentUser: null, plan: null };

    function node(tag, text, className) { const result = document.createElement(tag); if (text !== undefined) result.textContent = text; if (className) result.className = className; return result; }
    function dateShort(date) { const [year,month,day] = String(date).split('-'); return year && month && day ? `${day}.${month}.` : String(date); }
    function monthHeader(day) { const names = ['','Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez']; const fragment = document.createDocumentFragment(); fragment.append(document.createTextNode(names[day.month] || String(day.month)), node('span', String(day.dayOfMonth))); return fragment; }
    function requestFor(uid, date) { return (state.plan?.requests || []).find(item => item.employeeUid === uid && item.startDate <= date && item.endDate >= date) || null; }
    function selectedTeam() { return state.teams.find(team => team.id === state.teamId) || null; }
    function showView(view) {
        const active = view === 'settings' && !elements['tab-settings'].hidden ? 'settings' : 'calendar';
        elements['calendar-view'].hidden = active !== 'calendar';
        elements['settings-view'].hidden = active !== 'settings';
        elements['tab-calendar'].setAttribute('aria-selected', String(active === 'calendar'));
        elements['tab-settings'].setAttribute('aria-selected', String(active === 'settings'));
    }

    function renderChrome() {
        const categories = [
            ['asn', 'Assistenzteams'],
            ['organization', 'Büros und Fachgruppen'],
        ];
        elements.team.replaceChildren(...categories.map(([category, label]) => {
            const group = document.createElement('optgroup'); group.label = label;
            group.append(...state.teams.filter(team => team.category === category).map(team => { const option = node('option', team.displayName); option.value = team.id; option.selected = team.id === state.teamId; return option; }));
            return group;
        }).filter(group => group.children.length));
        elements.year.value = String(state.year);
        const team = selectedTeam(); document.getElementById('adu-plan-title').textContent = team ? `${team.displayName} – Urlaub ${state.year}` : `Urlaub ${state.year}`;
    }

    function renderPlan() {
        const plan = state.plan; if (!plan) return;
        const currentUserInTeam = plan.assistants.some(employee => employee.uid === state.currentUser?.uid);
        elements['own-form'].hidden = !currentUserInTeam;
        elements['own-requests'].hidden = !currentUserInTeam;
        const head = document.createElement('tr'); const name = node('th','Mitarbeiter*in'); name.scope = 'col'; head.append(name);
        for (const day of plan.days) { const th = document.createElement('th'); th.scope = 'col'; if (day.weekday >= 6) th.classList.add('is-weekend'); th.append(monthHeader(day)); head.append(th); }
        elements['calendar-head'].replaceChildren(head);
        elements['calendar-body'].replaceChildren(...plan.assistants.map(employee => {
            const row = document.createElement('tr'); row.dataset.employeeUid = employee.uid; const th = node('th',employee.displayName); th.scope = 'row'; row.append(th);
            for (const day of plan.days) {
                const vacation = requestFor(employee.uid,day.date); const status = vacation?.status || ''; const cell = document.createElement('td'); cell.dataset.day = day.date; cell.className = ['adu-vac-cell',status && `adu-vac-${status}`,status && 'has-vacation',day.weekday >= 6 && 'is-weekend'].filter(Boolean).join(' ');
                const title = [employee.displayName,dateShort(day.date),status === 'approved' ? 'genehmigt' : status === 'planned' ? 'geplant' : ''].filter(Boolean).join(' – ');
                if (employee.canApprove) { const button = node('button', status === 'approved' ? 'U' : status === 'planned' ? 'U?' : ''); button.type = 'button'; button.dataset.action = 'set-status'; button.dataset.status = status === '' ? 'planned' : status === 'planned' ? 'approved' : 'planned'; button.title = title; button.setAttribute('aria-label',`${title} – Status ändern`); cell.append(button); }
                else { cell.title = title; if (status) cell.append(node('span',status === 'approved' ? 'U' : 'U?')); }
                row.append(cell);
            }
            return row;
        }));
        renderOwnRequests();
    }

    function renderOwnRequests() {
        const own = (state.plan?.requests || []).filter(item => item.employeeUid === state.currentUser?.uid);
        elements['own-requests'].replaceChildren(...own.map(request => { const badge = node('span',`${dateShort(request.startDate)}–${dateShort(request.endDate)} · ${request.status === 'approved' ? 'genehmigt' : 'geplant'}`,`adu-request adu-request-${request.status}`); if (request.status === 'planned') { const button = node('button','×'); button.type='button'; button.dataset.action='delete-own'; button.dataset.id=String(request.id); button.title='Urlaubsantrag löschen'; button.setAttribute('aria-label','Urlaubsantrag löschen'); badge.append(button); } return badge; }));
    }

    async function loadYear() { if (!state.teamId) return; try { state.plan=await client.request(`/api/teams/${client.encode(state.teamId)}/years/${client.encode(state.year)}`); renderChrome(); renderPlan(); notice.clear(); } catch (error) { notice.error(error); } }
    async function init() { try { const payload=await client.request('/api/teams'); state.teams=payload.teams || []; state.currentUser=payload.currentUser || null; const params=new URLSearchParams(window.location.search); const requested=params.get('team'); state.teamId=state.teams.some(team=>team.id===requested) ? requested : (state.teams.find(team=>team.employees?.some(employee=>employee.uid===state.currentUser?.uid))?.id || state.teams[0]?.id || ''); const year=Number(params.get('year')); if (year>=2000 && year<=2100) state.year=year; renderChrome(); await loadYear(); } catch (error) { notice.error(error); } loadSettings(); }
    async function save(payload) { await client.request('/api/vacations',{method:'POST',body:JSON.stringify(payload)}); }
    function showConflicts(error) { if (error.status!==409 || !error.data?.conflicts) return; elements.conflicts.hidden=false; elements.conflicts.replaceChildren(node('strong','Genehmigung nicht möglich:'),...error.data.conflicts.map(conflict=>node('div',`${conflict.type==='shift'?'Dienst':'Termin'} ${new Date(conflict.start).toLocaleString('de-DE')}–${new Date(conflict.end).toLocaleString('de-DE')}${conflict.label?` · ${conflict.label}`:''}`))); }
    async function loadSettings() { try { const settings=await client.request('/api/settings'); elements['tab-settings'].hidden=false; const labels=new Map((settings.peerOptions||[]).map(option=>[option.groupId,option.label])); elements['peer-settings'].replaceChildren(...Object.entries(settings.peerApproval).map(([group,enabled])=>{ const label=node('label'); const input=document.createElement('input'); input.type='checkbox'; input.name=group; input.checked=enabled; label.append(input,document.createTextNode(` ${labels.get(group)||group}`)); return label; })); } catch (_) { showView('calendar'); } }

    elements.team.addEventListener('change',async event=>{ state.teamId=event.target.value; await loadYear(); });
    elements.year.addEventListener('change',async event=>{ const year=Number(event.target.value); if (year>=2000&&year<=2100) { state.year=year; await loadYear(); } });
    elements['tab-calendar'].addEventListener('click',()=>showView('calendar'));
    elements['tab-settings'].addEventListener('click',()=>showView('settings'));
    elements['calendar-body'].addEventListener('click',async event=>{ const button=event.target instanceof Element ? event.target.closest('button[data-action="set-status"]'):null; if (!button) return; const employee=state.plan.assistants.find(item=>item.uid===button.closest('tr')?.dataset.employeeUid); if (!employee?.canApprove) return; elements.conflicts.hidden=true; elements.conflicts.replaceChildren(); try { await client.request(`/api/teams/${client.encode(state.teamId)}/years/${client.encode(state.year)}/status`,{method:'POST',body:JSON.stringify({employeeUid:employee.uid,date:button.closest('td').dataset.day,status:button.dataset.status})}); await loadYear(); notice.success('Urlaubsstatus gespeichert.'); } catch(error){ showConflicts(error); notice.error(error); } });
    elements['own-form'].addEventListener('submit',async event=>{ event.preventDefault(); const form=new FormData(event.currentTarget); try { await save({employeeUid:state.currentUser.uid,startDate:form.get('startDate'),endDate:form.get('endDate'),status:'planned',note:form.get('note')}); event.currentTarget.reset(); await loadYear(); notice.success('Urlaub eingetragen.'); } catch(error){ notice.error(error); } });
    elements['own-requests'].addEventListener('click',async event=>{ const button=event.target instanceof Element ? event.target.closest('button[data-action="delete-own"]'):null; if (!button) return; try { await client.request(`/api/vacations/${client.encode(button.dataset.id)}`,{method:'DELETE',body:'{}'}); await loadYear(); notice.success('Urlaub gelöscht.'); } catch(error){ notice.error(error); } });
    elements['settings-form'].addEventListener('submit',async event=>{ event.preventDefault(); const peerApproval=Object.fromEntries([...elements['peer-settings'].querySelectorAll('input')].map(input=>[input.name,input.checked])); try { await client.request('/api/settings',{method:'PUT',body:JSON.stringify({peerApproval})}); notice.success('Freigaben gespeichert.'); await loadYear(); } catch(error){ notice.error(error); } });
    init();
}());
