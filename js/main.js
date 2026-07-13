(function () {
    'use strict';
    const client = new window.LocalBase.api.ApiClient({ appId: 'adurlaub', errorMessage: (data, status) => data?.error || `HTTP ${status}` });
    const notice = new window.LocalBase.ui.Notice('adu-notice', { baseClass: 'adu-notice', typeClassPrefix: 'adu-notice--' });
    const ids = ['team','year','calendar-head','calendar-body','own-form','own-requests','dialog','form','id','employee','start-date','end-date','status','note','delete','conflicts','settings','settings-form','peer-settings'];
    const elements = Object.fromEntries(ids.map(id => [id, document.getElementById(`adu-${id}`)]));
    const state = { teams: [], teamId: '', year: new Date().getFullYear(), currentUser: null, plan: null };

    function node(tag, text, className) { const result = document.createElement(tag); if (text !== undefined) result.textContent = text; if (className) result.className = className; return result; }
    function dateShort(date) { const [year,month,day] = String(date).split('-'); return year && month && day ? `${day}.${month}.` : String(date); }
    function monthHeader(day) { const names = ['','Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez']; const fragment = document.createDocumentFragment(); fragment.append(document.createTextNode(names[day.month] || String(day.month)), node('span', String(day.dayOfMonth))); return fragment; }
    function requestFor(uid, date) { return (state.plan?.requests || []).find(item => item.employeeUid === uid && item.startDate <= date && item.endDate >= date) || null; }
    function selectedTeam() { return state.teams.find(team => team.id === state.teamId) || null; }

    function renderChrome() {
        elements.team.replaceChildren(...state.teams.map(team => { const option = node('option', team.displayName); option.value = team.id; option.selected = team.id === state.teamId; return option; }));
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
                if (employee.canManage) { const button = node('button', status === 'approved' ? 'U' : status === 'planned' ? 'U?' : ''); button.type = 'button'; button.dataset.action = vacation ? 'edit' : 'add'; if (vacation) button.dataset.id = String(vacation.id); button.title = title; button.setAttribute('aria-label',title); cell.append(button); }
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

    function openDialog(employee, date, vacation = null) {
        elements.id.value = vacation?.id || ''; elements.employee.replaceChildren(...state.plan.assistants.filter(item => item.canManage).map(item => { const option = node('option',item.displayName); option.value=item.uid; return option; })); elements.employee.value=employee.uid;
        elements['start-date'].value=vacation?.startDate || date; elements['end-date'].value=vacation?.endDate || date; elements.status.value=vacation?.status || 'planned'; elements.note.value=vacation?.note || '';
        elements.status.querySelector('option[value="approved"]').disabled=!employee.canApprove; elements.delete.hidden=!vacation; elements.conflicts.hidden=true; elements.conflicts.replaceChildren(); elements.dialog.showModal();
    }
    function closeDialog() { elements.dialog.close(); }
    async function loadYear() { if (!state.teamId) return; try { state.plan=await client.request(`/api/teams/${client.encode(state.teamId)}/years/${client.encode(state.year)}`); renderChrome(); renderPlan(); notice.clear(); } catch (error) { notice.error(error); } }
    async function init() { try { const payload=await client.request('/api/teams'); state.teams=payload.teams || []; state.currentUser=payload.currentUser || null; const params=new URLSearchParams(window.location.search); const requested=params.get('team'); state.teamId=state.teams.some(team=>team.id===requested) ? requested : (state.teams.find(team=>team.employees?.some(employee=>employee.uid===state.currentUser?.uid))?.id || state.teams[0]?.id || ''); const year=Number(params.get('year')); if (year>=2000 && year<=2100) state.year=year; renderChrome(); await loadYear(); } catch (error) { notice.error(error); } loadSettings(); }
    async function save(payload,id='') { await client.request(id ? `/api/vacations/${client.encode(id)}` : '/api/vacations',{method:id ? 'PUT':'POST',body:JSON.stringify(payload)}); }
    function showConflicts(error) { if (error.status!==409 || !error.data?.conflicts) return; elements.conflicts.hidden=false; elements.conflicts.replaceChildren(node('strong','Genehmigung nicht möglich:'),...error.data.conflicts.map(conflict=>node('div',`${conflict.type==='shift'?'Dienst':'Termin'} ${new Date(conflict.start).toLocaleString('de-DE')}–${new Date(conflict.end).toLocaleString('de-DE')}${conflict.label?` · ${conflict.label}`:''}`))); }
    async function loadSettings() { try { const settings=await client.request('/api/settings'); elements.settings.hidden=false; elements['peer-settings'].replaceChildren(...Object.entries(settings.peerApproval).map(([group,enabled])=>{ const label=node('label'); const input=document.createElement('input'); input.type='checkbox'; input.name=group; input.checked=enabled; label.append(input,document.createTextNode(` ${group==='ad-ASN-*'?'ASN-Teamkolleg*innen':group}`)); return label; })); } catch (_) {} }

    elements.team.addEventListener('change',async event=>{ state.teamId=event.target.value; await loadYear(); });
    elements.year.addEventListener('change',async event=>{ const year=Number(event.target.value); if (year>=2000&&year<=2100) { state.year=year; await loadYear(); } });
    elements['calendar-body'].addEventListener('click',event=>{ const button=event.target instanceof Element ? event.target.closest('button[data-action]'):null; if (!button) return; const employee=state.plan.assistants.find(item=>item.uid===button.closest('tr')?.dataset.employeeUid); if (!employee?.canManage) return; openDialog(employee,button.closest('td').dataset.day,button.dataset.id ? state.plan.requests.find(item=>item.id===Number(button.dataset.id)):null); });
    elements['own-form'].addEventListener('submit',async event=>{ event.preventDefault(); const form=new FormData(event.currentTarget); try { await save({employeeUid:state.currentUser.uid,startDate:form.get('startDate'),endDate:form.get('endDate'),status:'planned',note:form.get('note')}); event.currentTarget.reset(); await loadYear(); notice.success('Urlaub eingetragen.'); } catch(error){ notice.error(error); } });
    elements['own-requests'].addEventListener('click',async event=>{ const button=event.target instanceof Element ? event.target.closest('button[data-action="delete-own"]'):null; if (!button) return; try { await client.request(`/api/vacations/${client.encode(button.dataset.id)}`,{method:'DELETE',body:'{}'}); await loadYear(); notice.success('Urlaub gelöscht.'); } catch(error){ notice.error(error); } });
    elements.form.addEventListener('submit',async event=>{ event.preventDefault(); const payload={employeeUid:elements.employee.value,startDate:elements['start-date'].value,endDate:elements['end-date'].value,status:elements.status.value,note:elements.note.value}; try { await save(payload,elements.id.value); closeDialog(); await loadYear(); notice.success('Urlaub gespeichert.'); } catch(error){ showConflicts(error); notice.error(error); } });
    elements.delete.addEventListener('click',async()=>{ if (!elements.id.value||!window.confirm('Urlaub wirklich löschen?')) return; try { await client.request(`/api/vacations/${client.encode(elements.id.value)}`,{method:'DELETE',body:'{}'}); closeDialog(); await loadYear(); notice.success('Urlaub gelöscht.'); } catch(error){ notice.error(error); } });
    elements['settings-form'].addEventListener('submit',async event=>{ event.preventDefault(); const peerApproval=Object.fromEntries([...elements['peer-settings'].querySelectorAll('input')].map(input=>[input.name,input.checked])); try { await client.request('/api/settings',{method:'PUT',body:JSON.stringify({peerApproval})}); notice.success('Freigaben gespeichert.'); await loadYear(); } catch(error){ notice.error(error); } });
    document.getElementById('adu-dialog-close').addEventListener('click',closeDialog); document.getElementById('adu-dialog-cancel').addEventListener('click',closeDialog);
    init();
}());
