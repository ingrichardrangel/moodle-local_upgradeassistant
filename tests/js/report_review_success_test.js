// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const feedback = {
    textContent: '',
    classList: {add() {}, remove() {}},
};
const button = {disabled: false};
const freshCard = {querySelector: () => null};
let replaced = false;
let scrolled = false;
let submit;
const card = {
    dataset: {uaFindingId: '42'},
    nextElementSibling: {isConnected: true, scrollIntoView() { scrolled = true; }},
    replaceWith(replacement) {
        assert.equal(replacement, freshCard);
        replaced = true;
    },
};
const report = {
    dataset: {reviewSaved: 'Review saved', reviewError: 'Review failed'},
    querySelector(selector) {
        return selector === '.ua-review-feedback' ? feedback : null;
    },
};

class HTMLFormElement {
    constructor() {
        this.dataset = {};
        this.action = {toString: () => '[object HTMLInputElement]'};
    }
    matches(selector) { return selector === '.ua-finding-review-form'; }
    getAttribute() { return '/local/upgradeassistant/index.php'; }
    setAttribute() {}
    removeAttribute() {}
    querySelector(selector) { return selector === 'button[type="submit"]' ? button : null; }
    closest(selector) { return selector === '.ua-reports-page' ? report : card; }
}
class FormData {
    append() {}
    get() { return '7'; }
}
class DOMParser {
    parseFromString() {
        return {querySelector(selector) {
            return selector === '[data-ua-finding-id="42"]' ? freshCard : null;
        }};
    }
}

const requests = [];
const context = {
    HTMLFormElement,
    FormData,
    DOMParser,
    URL,
    document: {
        addEventListener(name, callback) {
            assert.equal(name, 'submit');
            submit = callback;
        },
        importNode(node) { return node; },
    },
    window: {
        location: {href: 'https://example.org/local/upgradeassistant/index.php?view=reports'},
        fetch: true,
        DOMParser,
        scrollY: 200,
    },
    fetch: async (url, options) => {
        requests.push({url, options});
        return requests.length === 1
            ? {ok: true, json: async () => ({success: true})}
            : {ok: true, text: async () => '<html></html>'};
    },
};

const script = fs.readFileSync(path.join(__dirname, '../../js/report_review.js'), 'utf8');
vm.runInNewContext(script, context);
const form = new HTMLFormElement();
submit({target: form, preventDefault() {}}).then(() => {
    assert.equal(requests.length, 2);
    assert.equal(requests[0].options.method, 'POST');
    assert.equal(new URL(requests[1].url).searchParams.get('report'), '7');
    assert.equal(feedback.textContent, 'Review saved');
    assert.equal(form.dataset.saving, '0');
    assert.equal(button.disabled, false);
    assert.ok(replaced);
    assert.ok(scrolled);
    console.log('Documented review refreshes the finding in place after saving.');
}).catch(error => {
    console.error(error);
    process.exitCode = 1;
});
