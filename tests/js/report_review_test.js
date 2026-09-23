// Run with: node tests/js/report_review_test.js
// A hidden input named "action" shadows HTMLFormElement.action in the browser.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

let submit;
let requestedUrl;
class HTMLFormElement {
    constructor() {
        this.action = {toString: () => '[object HTMLInputElement]'};
        this.dataset = {};
    }
    matches(selector) { return selector === '.ua-finding-review-form'; }
    getAttribute(name) {
        assert.equal(name, 'action');
        return '/local/upgradeassistant/index.php';
    }
    setAttribute() {}
    removeAttribute() {}
    querySelector() { return null; }
    closest(selector) {
        if (selector === '[data-ua-finding-id]') {
            return {dataset: {uaFindingId: '42'}, nextElementSibling: null};
        }
        return {dataset: {reviewError: 'Error'}, querySelector: () => null};
    }
}
class FormData {
    append() {}
    get() { return '1'; }
}
const context = {
    HTMLFormElement,
    FormData,
    URL,
    document: {addEventListener: (name, handler) => {
        assert.equal(name, 'submit');
        submit = handler;
    }},
    window: {
        location: {href: 'https://example.org/local/upgradeassistant/index.php?view=reports'},
        fetch: true,
        DOMParser: true,
        scrollY: 0,
    },
    fetch: async url => {
        requestedUrl = url;
        throw new Error('Stop after checking the request URL');
    },
};
const script = fs.readFileSync(path.join(__dirname, '../../js/report_review.js'), 'utf8');
vm.runInNewContext(script, context);
submit({target: new HTMLFormElement(), preventDefault() {}}).then(() => {
    assert.equal(requestedUrl, 'https://example.org/local/upgradeassistant/index.php');
    console.log('Documented review uses the form URL even when action is a named input.');
}).catch(error => {
    console.error(error);
    process.exitCode = 1;
});
