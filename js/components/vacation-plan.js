(function () {
    'use strict';

    /**
     * Zweck: Rendert Teamauswahl, Jahresmatrix, eigene Anträge und Konfliktlisten ohne API-Zugriffe.
     * Zusammenspiel: VacationApp aktualisiert den Zustand und stößt danach die passenden Render-Methoden an.
     */
    class VacationPlan {
        constructor({ elements, state }) {
            this.elements = elements;
            this.state = state;
        }

        renderChrome() {
            const categories = [
                ['asn', 'Assistenzteams'],
                ['organization', 'Büros und Fachgruppen'],
            ];
            this.elements.team.replaceChildren(...categories.map(([category, label]) => {
                const group = document.createElement('optgroup');
                group.label = label;
                group.append(...this.state.teams.filter(team => team.category === category).map(team => {
                    const option = this.node('option', team.displayName);
                    option.value = team.id;
                    option.selected = team.id === this.state.teamId;
                    return option;
                }));
                return group;
            }).filter(group => group.children.length));
            this.elements.year.value = String(this.state.year);
            const team = this.selectedTeam();
            this.elements['plan-title'].textContent = team ? `${team.displayName} – Urlaub ${this.state.year}` : `Urlaub ${this.state.year}`;
        }

        renderPlan() {
            const plan = this.state.plan;
            if (!plan) return;
            const currentUserInTeam = plan.assistants.some(employee => employee.uid === this.state.currentUser?.uid);
            this.elements['own-form'].hidden = !currentUserInTeam;
            this.elements['own-requests'].hidden = !currentUserInTeam;
            this.elements['calendar-head'].replaceChildren(this.renderHeader(plan.days));
            this.elements['calendar-body'].replaceChildren(...plan.assistants.map(employee => this.renderEmployee(employee, plan.days)));
            this.renderOwnRequests();
        }

        renderOwnRequests() {
            const own = (this.state.plan?.requests || []).filter(item => item.employeeUid === this.state.currentUser?.uid);
            this.elements['own-requests'].replaceChildren(...own.map(request => {
                const badge = this.node(
                    'span',
                    `${this.dateShort(request.startDate)}–${this.dateShort(request.endDate)} · ${request.status === 'approved' ? 'genehmigt' : 'geplant'}`,
                    `adu-request adu-request-${request.status}`,
                );
                if (request.status === 'planned') {
                    const button = this.node('button', '×');
                    button.type = 'button';
                    button.dataset.action = 'delete-own';
                    button.dataset.id = String(request.id);
                    button.title = 'Urlaubsantrag löschen';
                    button.setAttribute('aria-label', 'Urlaubsantrag löschen');
                    badge.append(button);
                }
                return badge;
            }));
        }

        clearConflicts() {
            this.elements.conflicts.hidden = true;
            this.elements.conflicts.replaceChildren();
        }

        showConflicts(error) {
            if (error.status !== 409 || !error.data?.conflicts) return;
            this.elements.conflicts.hidden = false;
            this.elements.conflicts.replaceChildren(
                this.node('strong', 'Genehmigung nicht möglich:'),
                ...error.data.conflicts.map(conflict => this.node(
                    'div',
                    `${conflict.type === 'shift' ? 'Dienst' : 'Termin'} ${new Date(conflict.start).toLocaleString('de-DE')}–${new Date(conflict.end).toLocaleString('de-DE')}${conflict.label ? ` · ${conflict.label}` : ''}`,
                )),
            );
        }

        renderHeader(days) {
            const head = document.createElement('tr');
            const name = this.node('th', 'Mitarbeiter*in');
            name.scope = 'col';
            head.append(name);
            for (const day of days) {
                const th = document.createElement('th');
                th.scope = 'col';
                if (day.weekday >= 6) th.classList.add('is-weekend');
                th.append(this.monthHeader(day));
                head.append(th);
            }
            return head;
        }

        renderEmployee(employee, days) {
            const row = document.createElement('tr');
            row.dataset.employeeUid = employee.uid;
            const name = this.node('th', employee.displayName);
            name.scope = 'row';
            row.append(name);
            for (const day of days) row.append(this.renderDay(employee, day));
            return row;
        }

        renderDay(employee, day) {
            const vacation = this.requestFor(employee.uid, day.date);
            const status = vacation?.status || '';
            const cell = document.createElement('td');
            cell.dataset.day = day.date;
            cell.className = ['adu-vac-cell', status && `adu-vac-${status}`, status && 'has-vacation', day.weekday >= 6 && 'is-weekend'].filter(Boolean).join(' ');
            const title = [employee.displayName, this.dateShort(day.date), status === 'approved' ? 'genehmigt' : status === 'planned' ? 'geplant' : ''].filter(Boolean).join(' – ');
            if (employee.canApprove) {
                const button = this.node('button', status === 'approved' ? 'U' : status === 'planned' ? 'U?' : '');
                button.type = 'button';
                button.dataset.action = 'set-status';
                button.dataset.status = status === '' ? 'planned' : status === 'planned' ? 'approved' : 'planned';
                button.title = title;
                button.setAttribute('aria-label', `${title} – Status ändern`);
                cell.append(button);
            } else {
                cell.title = title;
                if (status) cell.append(this.node('span', status === 'approved' ? 'U' : 'U?'));
            }
            return cell;
        }

        requestFor(uid, date) {
            return (this.state.plan?.requests || []).find(item => item.employeeUid === uid && item.startDate <= date && item.endDate >= date) || null;
        }

        selectedTeam() {
            return this.state.teams.find(team => team.id === this.state.teamId) || null;
        }

        monthHeader(day) {
            const names = ['', 'Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
            const fragment = document.createDocumentFragment();
            fragment.append(document.createTextNode(names[day.month] || String(day.month)), this.node('span', String(day.dayOfMonth)));
            return fragment;
        }

        dateShort(date) {
            const [year, month, day] = String(date).split('-');
            return year && month && day ? `${day}.${month}.` : String(date);
        }

        node(tag, text, className) {
            const result = document.createElement(tag);
            if (text !== undefined) result.textContent = text;
            if (className) result.className = className;
            return result;
        }
    }

    window.AdUrlaub = window.AdUrlaub || {};
    window.AdUrlaub.components = window.AdUrlaub.components || {};
    window.AdUrlaub.components.VacationPlan = VacationPlan;
}());
