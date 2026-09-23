// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/** Keep the findings panel open when saving a documented review. */
(function() {
    'use strict';

    document.addEventListener('submit', async function(event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('.ua-finding-review-form')
                || !window.fetch || !window.DOMParser) {
            return;
        }
        event.preventDefault();
        if (form.dataset.saving === '1') {
            return;
        }
        form.dataset.saving = '1';
        form.setAttribute('aria-busy', 'true');
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
        }
        const article = form.closest('[data-ua-finding-id]');
        const report = form.closest('.ua-reports-page');
        const notice = report && report.querySelector('.ua-review-feedback');
        const inline = form.querySelector('.ua-review-inline-status');
        const next = article && article.nextElementSibling;
        const scroll = window.scrollY;
        let saved = false;
        let servermessage = '';
        if (notice) {
            notice.textContent = '';
        }
        if (inline) {
            inline.textContent = '';
        }

        try {
            const data = new FormData(form);
            data.append('ajaxreview', '1');
            // The hidden input named "action" shadows HTMLFormElement.action in browsers.
            const actionurl = new URL(form.getAttribute('action'), window.location.href);
            const response = await fetch(actionurl.toString(), {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
                headers: {'Accept': 'application/json'},
            });
            const result = await response.json();
            if (!response.ok || result.success !== true) {
                servermessage = result.message || '';
                throw new Error('Review was not saved');
            }
            saved = true;
            const reporturl = new URL(actionurl.toString());
            reporturl.searchParams.set('view', 'reports');
            reporturl.searchParams.set('report', data.get('reportid'));
            const refreshed = await fetch(reporturl.toString(), {credentials: 'same-origin', cache: 'no-store'});
            if (!refreshed.ok) {
                throw new Error('Report could not be refreshed');
            }
            const page = new DOMParser().parseFromString(await refreshed.text(), 'text/html');
            const selector = '[data-ua-finding-id="' + article.dataset.uaFindingId + '"]';
            const fresh = page.querySelector(selector);
            if (!fresh || fresh.querySelector('.ua-finding-review-form')) {
                throw new Error('Report could not be refreshed');
            }
            article.replaceWith(document.importNode(fresh, true));
            const oldscore = report.querySelector('.ua-report-heading .ua-risk-score');
            const newscore = page.querySelector('.ua-report-heading .ua-risk-score');
            if (oldscore && newscore) {
                oldscore.replaceWith(document.importNode(newscore, true));
            }
            if (notice) {
                notice.textContent = report.dataset.reviewSaved || '';
                notice.classList.remove('alert-danger');
                notice.classList.add('alert-success');
            }
            if (next && next.isConnected) {
                next.scrollIntoView({block: 'nearest'});
            } else {
                window.scrollTo(0, scroll);
            }
        } catch (error) {
            const message = saved ? report.dataset.reviewSavedRefresh
                : (servermessage || report.dataset.reviewError);
            if (inline) {
                inline.textContent = message || report.dataset.reviewError || '';
            }
            if (notice) {
                notice.textContent = message || report.dataset.reviewError || '';
                notice.classList.remove('alert-success');
                notice.classList.add(saved ? 'alert-success' : 'alert-danger');
            }
        } finally {
            form.dataset.saving = '0';
            form.removeAttribute('aria-busy');
            if (button) {
                button.disabled = false;
            }
        }
    });
}());
