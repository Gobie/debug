/*global describe, it, before, beforeEach, after, afterEach, expect */
'use strict';

var hljs = require('../../../../../node_modules/highlight.js/highlight.js');

describe('dumpers', function () {
    describe('booleans', function () {
        it('should be formatted as keywords', function () {
            var out;

            out = hljs.highlight('php', 'TRUE').value;
            expect(out).to.be('<span class="keyword">TRUE</span>');

            out = hljs.highlight('php', 'FALSE').value;
            expect(out).to.be('<span class="keyword">FALSE</span>');
        });
    });
});
